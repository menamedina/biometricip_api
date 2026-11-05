<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Horario;
use App\Models\ProgramacionTurno;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProgramacionTurnoController extends Controller
{
    /**
     * Calcula la fecha del próximo día de ejecución configurado en la empresa.
     * Si hoy es ese día, devuelve el de la semana siguiente.
     */
    private function proximoDiaEjecucion(int $empresaId): Carbon
    {
        $empresa  = Empresa::find($empresaId);
        $diaConfig = $empresa?->dia_ejecucion_turno ?? 2; // default: martes

        $hoy       = Carbon::today();
        $diasHasta = ($hoy->dayOfWeek === $diaConfig) ? 7 : ($diaConfig - $hoy->dayOfWeek + 7) % 7;

        return $hoy->copy()->addDays($diasHasta);
    }

    /**
     * Vista principal del módulo.
     */
    public function index(): View
    {
        abort_unless(Auth::user()->can('programacion_turno.ver'), 403);

        $semanaInicio = $this->proximoDiaEjecucion(Auth::user()->empresa_id);
        $semanaFin    = $semanaInicio->copy()->addDays(6);

        $horariosData = Horario::where('is_active', true)
            ->orderBy('nombre')
            ->with('dias')
            ->get()
            ->map(fn($h) => [
                'id'     => $h->id,
                'nombre' => $h->nombre,
                'dias'   => $h->dias->map(fn($d) => [
                    'dia'          => $d->dia_semana,
                    'hora_entrada' => $d->hora_entrada ? substr($d->hora_entrada, 0, 5) : null,
                    'hora_salida'  => $d->hora_salida  ? substr($d->hora_salida,  0, 5) : null,
                ])->values(),
            ])
            ->values();

        return view('admin.programacion_turno.index', compact('semanaInicio', 'semanaFin', 'horariosData'));
    }

    /**
     * Devuelve en JSON el equipo del líder con su horario actual
     * y el horario programado para la próxima semana (si existe).
     */
    public function data(): JsonResponse
    {
        abort_unless(Auth::user()->can('programacion_turno.ver'), 403);

        $lider        = Auth::user();
        $semanaInicio = $this->proximoDiaEjecucion($lider->empresa_id)->toDateString();

        // Equipo del líder en la misma empresa
        $equipo = User::where('lider_id', $lider->id)
            ->where('empresa_id', $lider->empresa_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'foto_url', 'cargo_id', 'departamento_id', 'horario_id']);

        if ($equipo->isEmpty()) {
            return response()->json(['equipo' => [], 'semana_inicio' => $semanaInicio]);
        }

        // Cargar nombres de cargo y departamento desde tenant
        $cargoIds = $equipo->pluck('cargo_id')->filter()->unique();
        $deptIds  = $equipo->pluck('departamento_id')->filter()->unique();
        $horIds   = $equipo->pluck('horario_id')->filter()->unique();

        $cargos   = Cargo::whereIn('id', $cargoIds)->pluck('nombre', 'id');
        $deptos   = Departamento::whereIn('id', $deptIds)->pluck('nombre', 'id');
        $horNombres = Horario::whereIn('id', $horIds)->pluck('nombre', 'id');

        // Programaciones existentes del líder para esta semana
        $programaciones = ProgramacionTurno::where('lider_id', $lider->id)
            ->where('semana_inicio', $semanaInicio)
            ->get()
            ->keyBy('user_id');

        $result = $equipo->map(function (User $u) use ($cargos, $deptos, $horNombres, $programaciones) {
            $prog = $programaciones->get($u->id);
            return [
                'id'                    => $u->id,
                'name'                  => $u->name,
                'foto_url'              => $u->foto_url,
                'cargo'                 => $cargos[$u->cargo_id] ?? '—',
                'departamento'          => $deptos[$u->departamento_id] ?? '—',
                'horario_id'            => $u->horario_id,
                'horario_nombre'        => $horNombres[$u->horario_id] ?? '—',
                'horario_programado_id' => $prog?->horario_id,
                'programacion_id'       => $prog?->id,
            ];
        });

        return response()->json([
            'equipo'       => $result,
            'semana_inicio' => $semanaInicio,
        ]);
    }

    /**
     * Guarda (upsert) la programación de turno del equipo del líder.
     * Recibe un array de {user_id, horario_id} para la semana_inicio.
     */
    public function store(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->can('programacion_turno.crear'), 403);

        $lider = Auth::user();

        $request->validate([
            'semana_inicio'          => 'required|date|date_format:Y-m-d',
            'programaciones'         => 'required|array|min:1',
            'programaciones.*.user_id'   => 'required|integer',
            'programaciones.*.horario_id' => 'required|integer',
        ]);

        $semanaInicio = $request->semana_inicio;

        // Validar que todos los user_id pertenecen al equipo del líder
        $equipoIds = User::where('lider_id', $lider->id)
            ->where('empresa_id', $lider->empresa_id)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $horarioIds = Horario::where('is_active', true)->pluck('id')->toArray();

        $guardados = 0;

        foreach ($request->programaciones as $item) {
            $userId   = (int) $item['user_id'];
            $horarioId = (int) $item['horario_id'];

            // Seguridad: solo empleados del equipo del líder y horarios válidos
            if (!in_array($userId, $equipoIds)) continue;
            if (!in_array($horarioId, $horarioIds)) continue;

            ProgramacionTurno::updateOrCreate(
                [
                    'lider_id'      => $lider->id,
                    'user_id'       => $userId,
                    'semana_inicio' => $semanaInicio,
                ],
                [
                    'horario_id' => $horarioId,
                ]
            );

            $guardados++;
        }

        return response()->json([
            'message'  => "Programación guardada ({$guardados} empleados).",
            'guardados' => $guardados,
        ]);
    }

    /**
     * Elimina una entrada individual de programación.
     * Solo puede eliminar entradas propias del líder autenticado.
     */
    public function destroy(int $id): JsonResponse
    {
        abort_unless(Auth::user()->can('programacion_turno.eliminar'), 403);

        $deleted = ProgramacionTurno::where('id', $id)
            ->where('lider_id', Auth::id())
            ->delete();

        if (!$deleted) {
            return response()->json(['message' => 'Programación no encontrada.'], 404);
        }

        return response()->json(['message' => 'Programación eliminada.']);
    }
}
