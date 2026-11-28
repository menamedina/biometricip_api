<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empleador;
use App\Models\Horario;
use App\Models\Sede;
use App\Models\User;
use App\Models\UserImagen;
use App\Models\UserSede;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ExternalApiController extends Controller
{
    /**
     * GET /api/external/attendance
     *
     * Parámetros opcionales:
     *   date_from  string  YYYY-MM-DD
     *   date_to    string  YYYY-MM-DD
     *   tipo       string  entrada|salida|salida_almuerzo|regreso_almuerzo
     *   sede_id    int
     *   user_id    int     (solo si scope lo permite)
     */
    public function attendance(Request $request): JsonResponse
    {
        /** @var User $apiUser */
        $apiUser = $request->_api_user;

        $scopeIds = $this->resolveScope($apiUser);

        $query = AttendanceRecord::with([
                'user:id,name,cedula,codigo_empleado,lider_id,departamento_id,cargo_id,horario_id',
                'sede:id,nombre',
                'photo:id,attendance_record_id,thumbnail_path',
            ])
            ->select(
                'id', 'user_id', 'sede_id', 'tipo', 'fecha_hora', 'metodo',
                'lat', 'lng', 'qr_validado', 'geocerca_validada',
                'distancia_oficina_mts', 'observacion'
            )
            ->whereIn('user_id', $scopeIds);

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('fecha_hora', [
                $request->date_from . ' 00:00:00',
                $request->date_to   . ' 23:59:59',
            ]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('fecha_hora', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('fecha_hora', '<=', $request->date_to);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('sede_id')) {
            $query->where('sede_id', $request->sede_id);
        }

        if ($request->filled('user_id') && in_array((int) $request->user_id, $scopeIds->all())) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->orderBy('fecha_hora', 'desc')->get();

        // Resolver nombres de catálogos tenant y líder
        $users       = $records->pluck('user')->filter();
        $liderIds    = $users->pluck('lider_id')->filter()->unique()->values()->all();
        $deptoIds    = $users->pluck('departamento_id')->filter()->unique()->values()->all();
        $cargoIds    = $users->pluck('cargo_id')->filter()->unique()->values()->all();
        $horarioIds  = $users->pluck('horario_id')->filter()->unique()->values()->all();

        $lideres       = User::on('mysql')->whereIn('id', $liderIds)->pluck('name', 'id');
        $departamentos = !empty($deptoIds) ? Departamento::whereIn('id', $deptoIds)->pluck('nombre', 'id') : collect();
        $cargos        = !empty($cargoIds) ? Cargo::whereIn('id', $cargoIds)->pluck('nombre', 'id') : collect();
        $horarios      = !empty($horarioIds) ? Horario::whereIn('id', $horarioIds)->pluck('nombre', 'id') : collect();

        $items = $records->map(function ($record) use ($lideres, $departamentos, $cargos, $horarios) {
            $hasFotoMarcacion = $record->photo !== null;

            return [
                'codigo_empleado'       => $record->user?->codigo_empleado,
                'cedula'                => $record->user?->cedula,
                'nombre'                => $record->user?->name,
                'lider'                 => $lideres->get($record->user?->lider_id),
                'departamento'          => $departamentos->get($record->user?->departamento_id),
                'cargo'                 => $cargos->get($record->user?->cargo_id),
                'horario'               => $horarios->get($record->user?->horario_id),
                'foto_marcacion_url'    => $hasFotoMarcacion && $record->photo->thumbnail_path
                                            ? url('api/external/attendance/photo?path=' . urlencode($record->photo->thumbnail_path))
                                            : null,
                'sede'                  => $record->sede?->nombre,
                'tipo'                  => $record->tipo,
                'fecha_hora'            => $record->fecha_hora ? \Carbon\Carbon::parse($record->fecha_hora)->format('Y-m-d H:i') : null,
                'metodo'                => $record->metodo,
                'lat'                   => $record->lat,
                'lng'                   => $record->lng,
                'maps_url'              => ($record->lat && $record->lng && !($record->lat == 0 && $record->lng == 0))
                                            ? "https://www.google.com/maps?q={$record->lat},{$record->lng}"
                                            : null,
                'qr_validado'           => $record->qr_validado,
                'distancia_oficina_mts' => $record->distancia_oficina_mts,
                'observacion'           => $record->observacion,
            ];
        });

        return response()->json($items->values());
    }

    /**
     * GET /api/external/employees
     *
     * Parámetros opcionales:
     *   search    string  busca por nombre, cédula o código
     */
    public function employees(Request $request): JsonResponse
    {
        /** @var User $apiUser */
        $apiUser = $request->_api_user;

        $scopeIds = $this->resolveScope($apiUser);

        $query = User::on('mysql')
            ->select('id', 'name', 'cedula', 'codigo_empleado', 'role',
                     'departamento_id', 'cargo_id', 'horario_id', 'is_active',
                     'empleador_id', 'lider_id', 'empresa_id',
                     'telefono', 'email', 'centro_costo', 'ruta',
                     'ver_todos', 'marcacion_global', 'foto_url')
            ->whereIn('id', $scopeIds)
            ->where('is_active', true);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('cedula', 'like', "%{$s}%")
                  ->orWhere('codigo_empleado', 'like', "%{$s}%");
            });
        }

        $employees = $query->orderBy('name')->get();

        // Resolver nombres de catálogos tenant
        $liderIds    = $employees->pluck('lider_id')->filter()->unique()->values()->all();
        $deptoIds    = $employees->pluck('departamento_id')->filter()->unique()->values()->all();
        $cargoIds    = $employees->pluck('cargo_id')->filter()->unique()->values()->all();
        $horarioIds  = $employees->pluck('horario_id')->filter()->unique()->values()->all();
        $empleadorIds = $employees->pluck('empleador_id')->filter()->unique()->values()->all();
        $empresaIds  = $employees->pluck('empresa_id')->filter()->unique()->values()->all();

        $lideres       = !empty($liderIds) ? User::on('mysql')->whereIn('id', $liderIds)->pluck('name', 'id') : collect();
        $departamentos = !empty($deptoIds) ? Departamento::whereIn('id', $deptoIds)->pluck('nombre', 'id') : collect();
        $cargos        = !empty($cargoIds) ? Cargo::whereIn('id', $cargoIds)->pluck('nombre', 'id') : collect();
        $horarios      = !empty($horarioIds) ? Horario::whereIn('id', $horarioIds)->pluck('nombre', 'id') : collect();
        $empleadores   = !empty($empleadorIds) ? Empleador::whereIn('id', $empleadorIds)->pluck('nombre', 'id') : collect();
        $empresas      = !empty($empresaIds) ? \App\Models\Empresa::whereIn('id', $empresaIds)->pluck('nombre', 'id') : collect();

        // Sedes por usuario (UserSede es mysql, Sede es tenant)
        $userIds  = $employees->pluck('id')->all();
        $userSedes = UserSede::whereIn('user_id', $userIds)->get()->groupBy('user_id');
        $sedeIds   = $userSedes->flatten()->pluck('sede_id')->filter()->unique()->values()->all();
        $sedes     = !empty($sedeIds) ? Sede::whereIn('id', $sedeIds)->pluck('nombre', 'id') : collect();

        $items = $employees->map(function ($emp) use ($lideres, $departamentos, $cargos, $horarios, $empleadores, $empresas, $userSedes, $sedes) {
            $sedeNombres = ($userSedes->get($emp->id) ?? collect())
                ->map(fn($us) => $sedes->get($us->sede_id))
                ->filter()
                ->implode(', ');

            return [
                'codigo_empleado'  => $emp->codigo_empleado,
                'cedula'           => $emp->cedula,
                'nombre'           => $emp->name,
                'foto_url'         => $emp->foto_url,
                'email'            => $emp->email,
                'telefono'         => $emp->telefono,
                'role'             => $emp->role,
                'lider'            => $lideres->get($emp->lider_id),
                'departamento'     => $departamentos->get($emp->departamento_id),
                'cargo'            => $cargos->get($emp->cargo_id),
                'horario'          => $horarios->get($emp->horario_id),
                'empleador'        => $empleadores->get($emp->empleador_id),
                'empresa'          => $empresas->get($emp->empresa_id),
                'sedes'            => $sedeNombres ?: null,
                'centro_costo'     => $emp->centro_costo,
                'ruta'             => $emp->ruta,
                'ver_todos'        => $emp->ver_todos,
                'marcacion_global' => $emp->marcacion_global,
                'is_active'        => $emp->is_active,
            ];
        });

        return response()->json($items->values());
    }

    /**
     * GET /api/external/employees/{id}/photo
     * Sirve la foto de perfil (thumbnail base64) del empleado.
     */
    public function employeePhoto(Request $request, int $id): Response|JsonResponse
    {
        $imagen = UserImagen::on('mysql')
            ->where('user_id', $id)
            ->whereNotNull('imagen_thumbnail')
            ->first(['imagen_thumbnail']);

        if (!$imagen || !$imagen->imagen_thumbnail) {
            return response()->json(['message' => 'Sin foto.'], 404);
        }

        $base64 = $imagen->imagen_thumbnail;

        if (str_contains($base64, ',')) {
            [, $base64] = explode(',', $base64, 2);
        }

        return response(base64_decode($base64), 200)
            ->header('Content-Type', 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * GET /api/external/attendance/photo?path={thumbnail_path}
     * Sirve la foto tomada en el momento de la marcación desde storage.
     */
    public function attendancePhoto(Request $request): Response|JsonResponse
    {
        $path = $request->query('path', '');

        if (!$path || !Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Foto no encontrada.'], 404);
        }

        $ext  = pathinfo($path, PATHINFO_EXTENSION);
        $mime = match ($ext) { 'png' => 'image/png', 'webp' => 'image/webp', default => 'image/jpeg' };

        return response(Storage::disk('local')->get($path), 200)
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * GET /api/external/summary
     *
     * Resumen diario de asistencia (Entrada 1, Salida 1, Entrada 2, Salida 2, Total).
     * Parámetros:
     *   date_from  string  YYYY-MM-DD (requerido)
     *   date_to    string  YYYY-MM-DD (requerido)
     *   user_id    int     (opcional, dentro del scope)
     */
    public function summary(Request $request): JsonResponse
    {
        /** @var User $apiUser */
        $apiUser = $request->_api_user;
        $scopeIds = $this->resolveScope($apiUser);

        $query = AttendanceRecord::with([
                'user:id,name,codigo_empleado,horario_id,ruta',
                'sede:id,nombre',
                'horario:id,nombre',
                'horario.dias',
            ])
            ->select('id', 'user_id', 'sede_id', 'horario_id', 'tipo', 'fecha_hora')
            ->whereIn('user_id', $scopeIds)
            ->orderBy('fecha_hora', 'asc');

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('fecha_hora', [
                $request->date_from . ' 00:00:00',
                $request->date_to   . ' 23:59:59',
            ]);
        } elseif ($request->filled('date_from')) {
            $query->whereDate('fecha_hora', '>=', $request->date_from);
        } elseif ($request->filled('date_to')) {
            $query->whereDate('fecha_hora', '<=', $request->date_to);
        }

        if ($request->filled('user_id') && in_array((int) $request->user_id, $scopeIds->all())) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->get();

        // Resolver horarios
        $horarioIds = $records->pluck('user.horario_id')->filter()->unique()->values()->all();
        $horarioNombres = !empty($horarioIds) ? Horario::whereIn('id', $horarioIds)->pluck('nombre', 'id') : collect();

        // Agrupar por usuario + fecha
        $grupos = [];
        foreach ($records as $r) {
            $fecha = substr($r->fecha_hora, 0, 10);
            $key   = "{$r->user_id}_{$fecha}";
            if (!isset($grupos[$key])) {
                $grupos[$key] = [
                    'user'    => $r->user,
                    'sede'    => $r->sede?->nombre,
                    'horario' => $r->horario,
                    'fecha'   => $fecha,
                    'marcas'  => [],
                ];
            }
            $grupos[$key]['marcas'][] = $r;
        }

        $items = [];
        foreach ($grupos as $g) {
            $marcas = collect($g['marcas'])->sortBy('fecha_hora')->values();

            // Calcular total minutos trabajados (pares entrada/salida)
            $totalMin = 0;
            for ($i = 0; $i < $marcas->count() - 1; $i += 2) {
                $entrada = $marcas->get($i);
                $salida  = $marcas->get($i + 1);
                if ($entrada && $salida) {
                    $totalMin += (int) round((\Carbon\Carbon::parse($salida->fecha_hora)->timestamp - \Carbon\Carbon::parse($entrada->fecha_hora)->timestamp) / 60);
                }
            }

            $totalMin  = max(0, $totalMin);
            $horas     = intdiv($totalMin, 60);
            $minutos   = $totalMin % 60;

            $formatHora = fn($r) => $r ? \Carbon\Carbon::parse($r->fecha_hora)->format('H:i') : null;

            $row = [
                'codigo_empleado' => $g['user']?->codigo_empleado,
                'nombre'          => $g['user']?->name,
                'horario'         => $horarioNombres->get($g['user']?->horario_id),
                'sede'            => $g['sede'],
                'fecha'           => $g['fecha'],
            ];

            // Hasta 4 pares (Entrada 1-4, Salida 1-4)
            for ($i = 0; $i < 4; $i++) {
                $n = $i + 1;
                $row["entrada_{$n}"] = $formatHora($marcas->get($i * 2));
                $row["salida_{$n}"]  = $formatHora($marcas->get($i * 2 + 1));
            }

            $row['total_horas'] = sprintf('%02d:%02d', $horas, $minutos);
            $items[] = $row;
        }

        return response()->json($items);
    }

    /**
     * GET /api/external/monthly
     *
     * Resumen mensual de horas trabajadas por día por empleado.
     * Parámetros:
     *   anio    int  (requerido) Ej: 2026
     *   mes     int  (requerido) Ej: 11
     *   user_id int  (opcional, dentro del scope)
     */
    public function monthly(Request $request): JsonResponse
    {
        /** @var User $apiUser */
        $apiUser = $request->_api_user;
        $scopeIds = $this->resolveScope($apiUser);

        $anio   = (int) ($request->anio ?? now()->year);
        $mes    = (int) ($request->mes ?? now()->month);
        $inicio = sprintf('%04d-%02d-01 00:00:00', $anio, $mes);
        $diasMes = (int) date('t', mktime(0, 0, 0, $mes, 1, $anio));
        $fin    = sprintf('%04d-%02d-%02d 23:59:59', $anio, $mes, $diasMes);

        $query = AttendanceRecord::with([
                'user:id,name,codigo_empleado',
                'horario:id,nombre',
                'horario.dias',
            ])
            ->select('id', 'user_id', 'horario_id', 'tipo', 'fecha_hora')
            ->whereBetween('fecha_hora', [$inicio, $fin])
            ->whereIn('user_id', $scopeIds)
            ->orderBy('fecha_hora', 'asc');

        if ($request->filled('user_id') && in_array((int) $request->user_id, $scopeIds->all())) {
            $query->where('user_id', $request->user_id);
        }

        $records = $query->get();

        // Agrupar por usuario
        $porUsuario = [];
        foreach ($records as $r) {
            $porUsuario[$r->user_id][] = $r;
        }

        $items = [];
        foreach ($porUsuario as $userId => $userRecords) {
            $user = $userRecords[0]->user;
            usort($userRecords, fn($a, $b) => strcmp($a->fecha_hora, $b->fecha_hora));

            // Emparejar entrada/salida en sesiones
            $sessions    = [];
            $openEntrada = null;
            foreach ($userRecords as $r) {
                if ($r->tipo === 'entrada') {
                    if ($openEntrada) $sessions[] = ['e' => $openEntrada, 's' => null];
                    $openEntrada = $r;
                } elseif ($r->tipo === 'salida') {
                    $sessions[] = ['e' => $openEntrada, 's' => $r];
                    $openEntrada = null;
                }
            }
            if ($openEntrada) $sessions[] = ['e' => $openEntrada, 's' => null];

            // Calcular minutos por día
            $minutosPorDia = [];
            foreach ($sessions as $s) {
                if (!$s['e'] || !$s['s']) continue;
                $fechaSesion = substr($s['e']->fecha_hora, 0, 10);
                $mins = (int) round((\Carbon\Carbon::parse($s['s']->fecha_hora)->timestamp - \Carbon\Carbon::parse($s['e']->fecha_hora)->timestamp) / 60);

                // Restar almuerzo del horario si aplica
                $isoDay  = (int) \Carbon\Carbon::parse($fechaSesion)->format('N');
                $horario = $s['e']->horario;
                $dia     = collect($horario?->dias ?? [])->firstWhere('dia_semana', $isoDay);
                if ($dia && $dia->duracion_almuerzo_min && $mins > $dia->duracion_almuerzo_min) {
                    $mins -= $dia->duracion_almuerzo_min;
                }

                $minutosPorDia[$fechaSesion] = ($minutosPorDia[$fechaSesion] ?? 0) + max(0, $mins);
            }

            // Construir fila con cada día del mes
            $row = [
                'codigo_empleado' => $user?->codigo_empleado,
                'nombre'          => $user?->name,
            ];

            $totalMes = 0;
            for ($d = 1; $d <= $diasMes; $d++) {
                $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $d);
                $mins  = $minutosPorDia[$fecha] ?? 0;
                $totalMes += $mins;
                $row["dia_$d"] = $mins > 0 ? sprintf('%02d:%02d', intdiv($mins, 60), $mins % 60) : null;
            }

            $row['total'] = sprintf('%02d:%02d', intdiv($totalMes, 60), $totalMes % 60);
            $items[] = $row;
        }

        return response()->json($items);
    }

    private function resolveScope(User $user): Collection
    {
        $scope = $user->scope_registros ?? ($user->ver_todos ? 'empresa' : 'personal');

        if ($scope === 'empresa') {
            return User::on('mysql')
                ->where('empresa_id', $user->empresa_id)
                ->pluck('id');
        }

        if ($scope === 'equipo') {
            return User::on('mysql')
                ->where('empresa_id', $user->empresa_id)
                ->where(fn($q) => $q->where('id', $user->id)->orWhere('lider_id', $user->id))
                ->pluck('id');
        }

        return collect([$user->id]);
    }
}
