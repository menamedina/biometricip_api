<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Festivo;
use App\Models\Permiso;
use App\Models\RecargoCalculado;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NovedadesNominaController extends Controller
{
    public function index(): View
    {
        abort_unless(Auth::user()->can('novedades_nomina.ver'), 403);

        return view('admin.novedades_nomina.index');
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->can('novedades_nomina.ver'), 403);

        $request->validate([
            'anio'    => 'required|integer|min:2000|max:2100',
            'mes'     => 'required|integer|min:1|max:12',
            'periodo' => 'required|in:mensual,q1,q2',
        ]);

        $anio    = (int) $request->anio;
        $mes     = (int) $request->mes;
        $periodo = $request->periodo;

        // ── Ventana del período ───────────────────────────────────────────────
        $ultimoDia = (int) date('t', mktime(0, 0, 0, $mes, 1, $anio));

        [$diaDesde, $diaHasta] = match ($periodo) {
            'q1'    => [1, 15],
            'q2'    => [16, $ultimoDia],
            default => [1, $ultimoDia],
        };

        $fechaDesde = Carbon::create($anio, $mes, $diaDesde)->startOfDay();
        $fechaHasta = Carbon::create($anio, $mes, $diaHasta)->endOfDay();

        $authUser = Auth::user();

        // ── Scope de visibilidad (mismo patrón que resumenMensualData) ────────
        $scopeIds = null;
        if ($authUser->cannot('empleados.ver')) {
            $scopeIds = [$authUser->id];
        } else {
            $equipoIds = $authUser->equipoIds();
            if ($authUser->ver_todos || count($equipoIds) > 1) {
                $scopeIds = $equipoIds;
            }
        }

        // ── Filtro adicional por user_id o departamento ───────────────────────
        $filtroUserId  = $request->filled('user_id')   ? (int) $request->user_id   : null;
        $filtroDeptId  = $request->filled('departamento_id') ? (int) $request->departamento_id : null;

        // ── Cargar empleados del scope ────────────────────────────────────────
        $empQuery = User::where('empresa_id', $authUser->empresa_id)
            ->where('is_active', true);

        if ($scopeIds !== null) {
            if ($filtroUserId && in_array($filtroUserId, $scopeIds)) {
                $empQuery->where('id', $filtroUserId);
            } else {
                $empQuery->whereIn('id', $scopeIds);
            }
        } elseif ($filtroUserId) {
            $empQuery->where('id', $filtroUserId);
        }

        $empleados = $empQuery->get(['id', 'name', 'cedula', 'codigo_empleado', 'departamento_id', 'cargo_id', 'horario_id']);

        if ($empleados->isEmpty()) {
            return response()->json(['data' => [], 'sin_recargos' => false]);
        }

        $empleadoIds = $empleados->pluck('id')->toArray();

        // Filtro cliente por departamento
        if ($filtroDeptId) {
            $empleados = $empleados->filter(fn($u) => $u->departamento_id == $filtroDeptId);
            if ($empleados->isEmpty()) {
                return response()->json(['data' => [], 'sin_recargos' => false]);
            }
            $empleadoIds = $empleados->pluck('id')->toArray();
        }

        // ── Departamentos y cargos ────────────────────────────────────────────
        $deptoMap = Departamento::whereIn('id', $empleados->pluck('departamento_id')->filter()->unique())
            ->pluck('nombre', 'id');

        // ── Festivos del período (lookup O(1)) ────────────────────────────────
        $festivosSet = Festivo::where('is_active', true)
            ->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
            ->pluck('fecha')
            ->map(fn($f) => $f instanceof \Carbon\Carbon ? $f->toDateString() : (string) $f)
            ->flip()
            ->all();

        // ── Días hábiles del período (global, igual para todos los empleados) ─
        $diasHabilesDelPeriodo = [];
        for ($d = $diaDesde; $d <= $diaHasta; $d++) {
            $fecha = Carbon::create($anio, $mes, $d)->toDateString();
            $dow   = Carbon::create($anio, $mes, $d)->dayOfWeek; // 0=Dom, 6=Sab
            if ($dow !== 0 && $dow !== 6 && !isset($festivosSet[$fecha])) {
                $diasHabilesDelPeriodo[] = $fecha;
            }
        }
        $totalDiasHabiles = count($diasHabilesDelPeriodo);
        $diasHabilesFlip  = array_flip($diasHabilesDelPeriodo);

        // ── Registros de asistencia del período ───────────────────────────────
        $registros = AttendanceRecord::whereIn('user_id', $empleadoIds)
            ->whereBetween('fecha_hora', [$fechaDesde, $fechaHasta])
            ->with(['horario:id,nombre', 'horario.dias'])
            ->orderBy('fecha_hora')
            ->get();

        // Agrupar por user_id → fecha → registros del día
        $regPorUsuarioDia = [];
        foreach ($registros as $r) {
            $fecha = substr($r->fecha_hora, 0, 10);
            $regPorUsuarioDia[$r->user_id][$fecha][] = $r;
        }

        // ── Permisos aprobados del período ────────────────────────────────────
        $permisosRaw = Permiso::whereIn('user_id', $empleadoIds)
            ->where('estado', 'aprobado')
            ->where(function ($q) use ($fechaDesde, $fechaHasta) {
                $q->where(function ($q2) use ($fechaDesde, $fechaHasta) {
                    $q2->whereNotNull('fecha_inicio')
                       ->whereDate('fecha_inicio', '<=', $fechaHasta)
                       ->whereDate('fecha_fin',    '>=', $fechaDesde);
                })->orWhere(function ($q2) use ($fechaDesde, $fechaHasta) {
                    $q2->whereNull('fecha_inicio')
                       ->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()]);
                });
            })
            ->with('tipoPermiso')
            ->get();

        // Indexar permisos por user_id → fecha
        $permisosIndex = [];
        foreach ($permisosRaw as $p) {
            if ($p->fecha_inicio && $p->fecha_fin) {
                $cursor = Carbon::parse($p->fecha_inicio)->startOfDay();
                $end    = Carbon::parse($p->fecha_fin)->startOfDay();
                while ($cursor->lte($end)) {
                    $key = "{$p->user_id}_{$cursor->toDateString()}";
                    $permisosIndex[$key][] = $p;
                    $cursor->addDay();
                }
            } elseif ($p->fecha) {
                $fechaStr = $p->fecha instanceof Carbon ? $p->fecha->toDateString() : (string) $p->fecha;
                $permisosIndex["{$p->user_id}_{$fechaStr}"][] = $p;
            }
        }

        // ── Recargos calculados del período ───────────────────────────────────
        $recargos = RecargoCalculado::whereIn('user_id', $empleadoIds)
            ->whereBetween('fecha', [$fechaDesde->toDateString(), $fechaHasta->toDateString()])
            ->with('concepto:id,codigo,es_extra,es_nocturno,es_festivo')
            ->get();

        // Agrupar por user_id → tipo
        $extrasPorUser    = [];
        $nocturnosPorUser = [];
        $festivosPorUser  = [];

        foreach ($recargos as $rc) {
            if (!$rc->concepto) continue;
            $uid = $rc->user_id;
            $min = (float) $rc->minutos;

            if ($rc->concepto->es_extra) {
                $extrasPorUser[$uid] = ($extrasPorUser[$uid] ?? 0) + $min;
            } else {
                if ($rc->concepto->es_nocturno) {
                    $nocturnosPorUser[$uid] = ($nocturnosPorUser[$uid] ?? 0) + $min;
                }
                if ($rc->concepto->es_festivo) {
                    $festivosPorUser[$uid] = ($festivosPorUser[$uid] ?? 0) + $min;
                }
            }
        }

        $hayRecargos = $recargos->isNotEmpty();

        // ── Construir resultado por empleado ──────────────────────────────────
        $toDate = fn($str) => new \DateTime(str_replace(' ', 'T', $str));

        $resultado = [];

        foreach ($empleados as $emp) {
            $uid        = $emp->id;
            $userRegs   = $regPorUsuarioDia[$uid] ?? [];
            $fechasConReg = array_keys($userRegs);

            // Días con registro en días hábiles
            $diasConRegistro = count(array_intersect($fechasConReg, $diasHabilesDelPeriodo));

            // Días con permiso completo en días hábiles
            $diasConPermiso = 0;
            $permisosTotalMin = 0;
            foreach ($diasHabilesDelPeriodo as $fechaHabil) {
                $key = "{$uid}_{$fechaHabil}";
                if (!isset($permisosIndex[$key])) continue;
                foreach ($permisosIndex[$key] as $p) {
                    if ($p->fecha_inicio && $p->fecha_fin) {
                        $pInicio = Carbon::parse($p->fecha_inicio);
                        $pFin    = Carbon::parse($p->fecha_fin);
                        $permisosTotalMin += (int) $pInicio->diffInMinutes($pFin);
                    } elseif ($p->horas_permiso) {
                        $permisosTotalMin += (int) ($p->horas_permiso * 60);
                    } else {
                        // Día completo
                        $diasConPermiso++;
                        $permisosTotalMin += 480;
                    }
                }
            }

            // Ausencias = días hábiles - días con registro - días de permiso completo
            $ausencias = max(0, $totalDiasHabiles - $diasConRegistro - $diasConPermiso);

            // Tardanzas
            $tardanzaMin = 0;
            foreach ($userRegs as $fecha => $regsDelDia) {
                // Solo días hábiles
                if (!isset($diasHabilesFlip[$fecha])) continue;

                // Primera entrada del día
                $entradas = array_filter($regsDelDia, fn($r) => $r->tipo === 'entrada');
                if (empty($entradas)) continue;

                usort($entradas, fn($a, $b) => strcmp($a->fecha_hora, $b->fecha_hora));
                $primeraEntrada = reset($entradas);

                // HorarioDia para el día de semana
                $dow    = (int) $toDate($primeraEntrada->fecha_hora)->format('N'); // 1=Lun…7=Dom
                $horario = $primeraEntrada->horario;
                if (!$horario) continue;

                $diaProg = collect($horario->dias ?? [])->firstWhere('dia_semana', $dow);
                if (!$diaProg || !$diaProg->hora_entrada) continue;

                // Hora programada en minutos del día
                [$hProg, $mProg] = explode(':', substr($diaProg->hora_entrada, 0, 5));
                $minProgramado = (int)$hProg * 60 + (int)$mProg;

                // Hora real en minutos del día
                $dtEntrada  = $toDate($primeraEntrada->fecha_hora);
                $minReal    = (int)$dtEntrada->format('H') * 60 + (int)$dtEntrada->format('i');

                $tolerancia = (int)($diaProg->retardo_min ?? 0);
                $diff       = $minReal - $minProgramado - $tolerancia;

                if ($diff > 0) {
                    $tardanzaMin += $diff;
                }
            }

            $resultado[] = [
                'user_id'              => $uid,
                'nombre'               => $emp->name,
                'cedula'               => $emp->cedula ?? '—',
                'codigo_empleado'      => $emp->codigo_empleado ?? '—',
                'departamento'         => $deptoMap[$emp->departamento_id] ?? '—',
                'dias_habiles'         => $totalDiasHabiles,
                'dias_con_registro'    => $diasConRegistro,
                'ausencias'            => $ausencias,
                'tardanza_min'         => $tardanzaMin,
                'extras_min'           => (int) round($extrasPorUser[$uid] ?? 0),
                'recargo_nocturno_min' => (int) round($nocturnosPorUser[$uid] ?? 0),
                'recargo_festivo_min'  => (int) round($festivosPorUser[$uid] ?? 0),
                'permisos_dias'        => $diasConPermiso,
                'permisos_min'         => $permisosTotalMin,
            ];
        }

        // Ordenar por nombre
        usort($resultado, fn($a, $b) => strcmp($a['nombre'], $b['nombre']));

        return response()->json([
            'data'         => $resultado,
            'sin_recargos' => !$hayRecargos,
        ]);
    }
}
