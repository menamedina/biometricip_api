<?php

namespace App\Http\Controllers;

use App\Helpers\TenantHelper;
use App\Models\Capacitacion;
use App\Models\CapacitacionAsistente;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CapacitacionController extends Controller
{
    // ─── Admin: Vista principal ───────────────────────────────

    public function index(): View
    {
        abort_unless(Auth::user()->can('capacitaciones.ver'), 403, 'No tienes permiso para acceder a esta sección.');

        return view('admin.capacitaciones.index');
    }

    // ─── Admin: Listado JSON para DataTable ───────────────────

    public function list(): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.ver'), 403);

        try {
            $userNames = User::whereIn('id', Capacitacion::whereNotNull('creado_por')->pluck('creado_por')->unique())
                ->pluck('name', 'id');

            $capacitaciones = Capacitacion::withCount('asistentes')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($cap) use ($userNames) {
                    return [
                        'id'                  => $cap->id,
                        'encrypted_id'        => Crypt::encryptString((string) $cap->id),
                        'titulo'              => $cap->titulo,
                        'tipo_evento'         => $cap->tipo_evento,
                        'instructor_nombre'   => $cap->instructor_nombre,
                        'duracion_horas'      => $cap->duracion_horas,
                        'temas'               => $cap->temas ?? [],
                        'metodologia'         => $cap->metodologia,
                        'impacto_medible'     => $cap->impacto_medible,
                        'indicador_nombre'    => $cap->indicador_nombre,
                        'formula_indicador'   => $cap->formula_indicador,
                        'frecuencia_medicion' => $cap->frecuencia_medicion,
                        'observaciones'       => $cap->observaciones,
                        'fecha_capacitacion'  => $cap->fecha_capacitacion?->format('d/m/Y H:i'),
                        'fecha_expiracion'    => $cap->fecha_expiracion->format('d/m/Y H:i'),
                        'expira_en'           => $cap->expira_en,
                        'activo'              => $cap->activo,
                        'vigente'             => $cap->estaVigente(),
                'cerrada'             => $cap->cerrada,
                'estado_label'        => $cap->estadoLabel(),
                        'asistentes_count'    => $cap->asistentes_count,
                        'token'               => $cap->token,
                        'creado_por_nombre'   => $cap->creado_por ? ($userNames[$cap->creado_por] ?? '—') : '—',
                    ];
                });

            return response()->json($capacitaciones);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@list: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar las capacitaciones.'], 500);
        }
    }

    // ─── Admin: Crear capacitación ────────────────────────────

    public function store(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.crear'), 403);

        $data = $request->validate([
            'titulo'             => 'required|string|max:255',
            'tipo_evento'       => 'required|in:capacitacion,asistencia',
            'temas'              => 'nullable|string',
            'metodologia'        => 'nullable|string',
            'impacto_medible'    => 'required|boolean',
            'indicador_nombre'   => 'required_if:impacto_medible,1|nullable|string|max:255',
            'formula_indicador'  => 'required_if:impacto_medible,1|nullable|string',
            'frecuencia_medicion' => 'required_if:impacto_medible,1|nullable|string|max:100',
            'fechas_sesiones'    => 'nullable|string',
            'observaciones'      => 'required|string',
            'instructor_nombre'  => 'required|string|max:255',
            'fecha_capacitacion' => 'required|date',
            'duracion_horas'     => 'nullable|string|max:20',
            'expira_en'          => 'required|integer|min:0',
        ]);

        try {
            $temas = [];
            if (!empty($data['temas'])) {
                $decoded = json_decode($data['temas'], true);
                $temas   = is_array($decoded) ? $decoded : [];
            }

            // Todas las fechas (primera + adicionales)
            $fechas = [$data['fecha_capacitacion']];
            if (!empty($data['fechas_sesiones'])) {
                $decoded = json_decode($data['fechas_sesiones'], true);
                if (is_array($decoded)) {
                    $fechas = array_merge($fechas, $decoded);
                }
            }
            $fechas = array_unique($fechas);

            $base = [
                'titulo'            => $data['titulo'],
                'tipo_evento'      => $data['tipo_evento'],
                'temas'             => $temas,
                'metodologia'       => $data['metodologia'] ?? null,
                'impacto_medible'   => $data['impacto_medible'],
                'indicador_nombre'  => $data['indicador_nombre'] ?? null,
                'formula_indicador' => $data['formula_indicador'] ?? null,
                'frecuencia_medicion' => $data['frecuencia_medicion'] ?? null,
                'observaciones'     => $data['observaciones'],
                'instructor_nombre' => $data['instructor_nombre'],
                'duracion_horas'    => $data['duracion_horas'] ?? null,
                'expira_en'         => $data['expira_en'],
                'activo'            => true,
                'creado_por'        => Auth::id(),
                'empresa_id'        => Auth::user()->empresa_id,
            ];

            // Crear una capacitación por cada fecha
            // La fecha_expiracion se calcula a partir de la fecha de la sesión + expira_en minutos
            $primera = null;
            foreach ($fechas as $fecha) {
                $fechaBase       = \Carbon\Carbon::parse($fecha);
                $fechaExpiracion = $data['expira_en'] > 0
                    ? $fechaBase->addMinutes((int) $data['expira_en'])
                    : \Carbon\Carbon::parse($fecha)->addYears(100);

                $cap = Capacitacion::create(array_merge($base, [
                    'fecha_capacitacion' => $fecha,
                    'fecha_expiracion'   => $fechaExpiracion,
                ]));
                if (!$primera) $primera = $cap;
            }

            return response()->json([
                'success'      => true,
                'total'        => count($fechas),
                'capacitacion' => [
                    'id'           => $primera->id,
                    'encrypted_id' => Crypt::encryptString((string) $primera->id),
                    'token'        => $primera->token,
                    'link'         => route('capacitacion.registro', [$primera->empresa_id, $primera->token]),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@store: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear la capacitación.'], 500);
        }
    }

    // ─── Admin: Vista de detalle (página completa) ───────────

    public function showView(string $encryptedId): View
    {
        abort_unless(Auth::user()->can('capacitaciones.ver'), 403, 'No tienes permiso para acceder a esta sección.');

        try {
            $id  = (int) Crypt::decryptString($encryptedId);
            $cap = Capacitacion::with('asistentes')->findOrFail($id);
            $creadoPorNombre = $cap->creado_por ? (User::find($cap->creado_por)?->name ?? '—') : '—';
            $link = route('capacitacion.registro', [$cap->empresa_id, $cap->token]);

            return view('admin.capacitaciones.show', compact('cap', 'creadoPorNombre', 'link'));
        } catch (\Throwable $e) {
            Log::error('CapacitacionController@showView: ' . $e->getMessage());
            abort(404);
        }
    }

    // ─── Admin: Detalle JSON ──────────────────────────────────

    public function show(string $encryptedId): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.ver'), 403);

        try {
            $id  = (int) Crypt::decryptString($encryptedId);
            $cap = Capacitacion::with('asistentes')->findOrFail($id);

            return response()->json([
                'id'                 => $cap->id,
                'encrypted_id'       => Crypt::encryptString((string) $cap->id),
                'titulo'             => $cap->titulo,
                'tipo_evento'       => $cap->tipo_evento,
                'instructor_nombre'  => $cap->instructor_nombre,
                'duracion_horas'     => $cap->duracion_horas,
                'temas'              => $cap->temas ?? [],
                'metodologia'       => $cap->metodologia,
                'impacto_medible'   => $cap->impacto_medible,
                'indicador_nombre'  => $cap->indicador_nombre,
                'formula_indicador' => $cap->formula_indicador,
                'frecuencia_medicion' => $cap->frecuencia_medicion,
                'observaciones'      => $cap->observaciones,
                'fecha_capacitacion' => $cap->fecha_capacitacion?->format('d/m/Y H:i'),
                'fecha_expiracion'   => $cap->fecha_expiracion->format('d/m/Y H:i'),
                'expira_en'          => $cap->expira_en,
                'activo'             => $cap->activo,
                'vigente'            => $cap->estaVigente(),
                'cerrada'            => $cap->cerrada,
                'estado_label'       => $cap->estadoLabel(),
                'token'              => $cap->token,
                'link'               => route('capacitacion.registro', [$cap->empresa_id, $cap->token]),
                'creado_por_nombre'  => $cap->creado_por ? (User::find($cap->creado_por)?->name ?? '—') : '—',
                'asistentes'         => $cap->asistentes->map(fn($a) => [
                    'id'         => $a->id,
                    'nombre'     => $a->nombre,
                    'correo'     => $a->correo,
                    'telefono'   => $a->telefono,
                    'created_at' => $a->created_at?->format('d/m/Y H:i'),
                ])->values(),
            ]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@show: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar el detalle.'], 500);
        }
    }

    // ─── Admin: Desactivar ────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.eliminar'), 403);

        try {
            $cap = Capacitacion::findOrFail($id);
            $cap->update(['activo' => false]);

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@destroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al desactivar la capacitación.'], 500);
        }
    }

    // ─── Admin: Desactivar (desde vista show — redirige) ──────

    public function destroyWeb(string $encryptedId)
    {
        abort_unless(Auth::user()->can('capacitaciones.eliminar'), 403);

        try {
            $id  = (int) Crypt::decryptString($encryptedId);
            $cap = Capacitacion::findOrFail($id);
            $cap->update(['activo' => false]);

            return redirect()->route('admin.capacitaciones.show', $encryptedId)
                ->with('success', 'Capacitación desactivada correctamente.');
        } catch (\Throwable $e) {
            Log::error('CapacitacionController@destroyWeb: ' . $e->getMessage());
            return back()->with('error', 'Error al desactivar la capacitación.');
        }
    }

    // ─── Admin: Editar ────────────────────────────────────────

    public function update(Request $request, string $encryptedId): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        $data = $request->validate([
            'titulo'             => 'required|string|max:255',
            'tipo_evento'       => 'required|in:capacitacion,asistencia',
            'temas'              => 'nullable|string',
            'metodologia'        => 'nullable|string',
            'impacto_medible'    => 'required|boolean',
            'indicador_nombre'   => 'required_if:impacto_medible,1|nullable|string|max:255',
            'formula_indicador'  => 'required_if:impacto_medible,1|nullable|string',
            'frecuencia_medicion' => 'required_if:impacto_medible,1|nullable|string|max:100',
            'observaciones'      => 'required|string',
            'instructor_nombre'  => 'required|string|max:255',
            'fecha_capacitacion' => 'required|date',
            'duracion_horas'     => 'nullable|string|max:20',
            'cerrada'            => 'nullable|boolean',
            'extender_link'      => 'nullable|integer|min:1',
            'sin_expiracion'     => 'nullable|boolean',
        ]);

        try {
            $id    = (int) Crypt::decryptString($encryptedId);
            $temas = [];
            if (!empty($data['temas'])) {
                $decoded = json_decode($data['temas'], true);
                $temas   = is_array($decoded) ? $decoded : [];
            }

            $cap = Capacitacion::findOrFail($id);
            $cap->update([
                'titulo'             => $data['titulo'],
                'tipo_evento'       => $data['tipo_evento'],
                'temas'              => $temas,
                'metodologia'       => $data['metodologia'] ?? null,
                'impacto_medible'   => $data['impacto_medible'],
                'indicador_nombre'  => $data['indicador_nombre'] ?? null,
                'formula_indicador' => $data['formula_indicador'] ?? null,
                'frecuencia_medicion' => $data['frecuencia_medicion'] ?? null,
                'observaciones'      => $data['observaciones'],
                'instructor_nombre'  => $data['instructor_nombre'],
                'fecha_capacitacion' => $data['fecha_capacitacion'],
                'duracion_horas'     => $data['duracion_horas'] ?? null,
                'cerrada'            => $data['cerrada'] ?? false,
                'activo'             => true,
            ] + (!empty($data['extender_link']) ? [
                'expira_en'        => (int) $data['extender_link'],
                'fecha_expiracion' => now()->addMinutes((int) $data['extender_link']),
            ] : (!empty($data['sin_expiracion']) ? [
                'expira_en'        => 0,
                'fecha_expiracion' => now()->addYears(100),
            ] : [])));

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@update: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar la capacitación.'], 500);
        }
    }

    // ─── Admin: Regenerar token ───────────────────────────────

    public function regenerar(string $encryptedId): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        try {
            $id  = (int) Crypt::decryptString($encryptedId);
            $cap = Capacitacion::findOrFail($id);
            $cap->update(['token' => \Illuminate\Support\Str::random(64)]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('CapacitacionController@regenerar: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    // ─── Admin: Cerrar capacitación ───────────────────────────

    public function cerrar(string $encryptedId): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        try {
            $id  = (int) Crypt::decryptString($encryptedId);
            $cap = Capacitacion::findOrFail($id);
            $cap->update(['cerrada' => true]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('CapacitacionController@cerrar: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    // ─── Admin: Abrir capacitación ────────────────────────────

    public function abrir(string $encryptedId): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        try {
            $id  = (int) Crypt::decryptString($encryptedId);
            $cap = Capacitacion::findOrFail($id);
            $cap->update(['cerrada' => false]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('CapacitacionController@abrir: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    // ─── Admin: Buscar empleados para agregar participantes ───

    public function buscarEmpleados(Request $request): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        try {
            $empresaId = Auth::user()->empresa_id;
            $q         = $request->query('q', '');
            $deptId    = $request->query('departamento_id');

            $query = User::where('empresa_id', $empresaId)
                ->where('is_active', true)
                ->where('tipo', 'usuario');

            if ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('cedula', 'like', "%{$q}%");
                });
            }

            if ($deptId) {
                $query->where('departamento_id', (int) $deptId);
            }

            $empleados = $query->select('id', 'name', 'cedula', 'email', 'telefono', 'departamento_id')
                ->orderBy('name')
                ->get()
                ->map(function ($u) {
                    return [
                        'cedula'           => $u->cedula ?? '',
                        'nombre'           => $u->name,
                        'correo'           => $u->email ?? '',
                        'telefono'         => $u->telefono ?? '',
                        'departamento_id'  => $u->departamento_id,
                    ];
                });

            return response()->json(['success' => true, 'data' => $empleados]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@buscarEmpleados: ' . $e->getMessage());
            return response()->json(['success' => false, 'data' => []], 500);
        }
    }

    // ─── Admin: Listar departamentos (tenant) ─────────────────

    public function departamentos(): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        try {
            $departamentos = Departamento::where('is_active', true)
                ->orderBy('nombre')
                ->get(['id', 'nombre']);

            return response()->json(['success' => true, 'data' => $departamentos]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@departamentos: ' . $e->getMessage());
            return response()->json(['success' => false, 'data' => []], 500);
        }
    }

    // ─── Admin: Agregar múltiples participantes (bulk) ────────

    public function addParticipantes(Request $request, string $encryptedId): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        $data = $request->validate([
            'participantes'            => 'required|array|min:1',
            'participantes.*.cedula'   => 'required|string|max:20',
            'participantes.*.nombre'   => 'required|string|max:255',
            'participantes.*.correo'   => 'nullable|email|max:255',
            'participantes.*.telefono' => 'nullable|string|max:50',
        ]);

        try {
            $id      = (int) Crypt::decryptString($encryptedId);
            Capacitacion::findOrFail($id);

            $existentes = CapacitacionAsistente::where('capacitacion_id', $id)
                ->pluck('cedula')
                ->flip();

            $agregados  = 0;
            $duplicados = 0;

            foreach ($data['participantes'] as $p) {
                if (isset($existentes[$p['cedula']])) {
                    $duplicados++;
                    continue;
                }

                CapacitacionAsistente::create([
                    'capacitacion_id' => $id,
                    'cedula'          => $p['cedula'],
                    'nombre'          => $p['nombre'],
                    'correo'          => $p['correo'] ?? null,
                    'telefono'        => $p['telefono'] ?? null,
                    'estado'          => 'programado',
                    'created_at'      => now(),
                ]);

                $existentes[$p['cedula']] = true;
                $agregados++;
            }

            return response()->json([
                'success'    => true,
                'agregados'  => $agregados,
                'duplicados' => $duplicados,
            ]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@addParticipantes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al agregar participantes.'], 500);
        }
    }

    // ─── Admin: Agregar participante ──────────────────────────

    public function addParticipante(Request $request, string $encryptedId): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        $data = $request->validate([
            'cedula'   => 'required|string|max:20',
            'nombre'   => 'required|string|max:255',
            'correo'   => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:50',
        ]);

        try {
            $id  = (int) Crypt::decryptString($encryptedId);
            $cap = Capacitacion::findOrFail($id);

            $existe = CapacitacionAsistente::where('capacitacion_id', $id)
                ->where('cedula', $data['cedula'])
                ->exists();

            if ($existe) {
                return response()->json(['success' => false, 'message' => 'Esta cédula ya está registrada en la capacitación.'], 422);
            }

            $asistente = CapacitacionAsistente::create([
                'capacitacion_id' => $id,
                'cedula'          => $data['cedula'],
                'nombre'          => $data['nombre'],
                'correo'          => $data['correo'] ?? null,
                'telefono'        => $data['telefono'] ?? null,
                'estado'          => 'programado',
                'created_at'      => now(),
            ]);

            return response()->json(['success' => true, 'asistente' => [
                'id'                => $asistente->id,
                'cedula'            => $asistente->cedula,
                'nombre'            => $asistente->nombre,
                'correo'            => $asistente->correo,
                'telefono'          => $asistente->telefono,
                'estado'            => $asistente->estado,
                'fecha_confirmacion'=> null,
                'created_at'        => $asistente->created_at?->format('d/m/Y H:i'),
            ]]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@addParticipante: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al agregar participante.'], 500);
        }
    }

    // ─── Admin: Eliminar participante (solo programado) ───────

    public function removeParticipante(string $encryptedId, int $participanteId): JsonResponse
    {
        abort_unless(Auth::user()->can('capacitaciones.editar'), 403);

        try {
            $id         = (int) Crypt::decryptString($encryptedId);
            $asistente  = CapacitacionAsistente::where('capacitacion_id', $id)->findOrFail($participanteId);

            if ($asistente->esConfirmado()) {
                return response()->json(['success' => false, 'message' => 'No se puede eliminar un participante que ya confirmó.'], 422);
            }

            $asistente->delete();

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@removeParticipante: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar participante.'], 500);
        }
    }

    // ─── Público: Formulario de registro ─────────────────────

    public function registroPublico(int $empresaId, string $token, Request $request): View
    {
        TenantHelper::switchTenant($empresaId);
        $capacitacion = Capacitacion::where('token', $token)->firstOrFail();
        $expirado     = !$capacitacion->aceptaRegistros();
        $empresa      = \App\Models\Empresa::find($empresaId);

        // Si viene cédula en GET, buscar participante programado
        $cedula      = $request->query('cedula');
        $participante = null;
        if ($cedula && !$expirado) {
            $participante = CapacitacionAsistente::where('capacitacion_id', $capacitacion->id)
                ->where('cedula', $cedula)
                ->first();
        }

        return view('capacitaciones.registro-publico', compact('capacitacion', 'expirado', 'empresa', 'cedula', 'participante'));
    }

    // ─── Público: Guardar registro de asistente ───────────────

    public function guardarRegistro(Request $request, int $empresaId, string $token)
    {
        try {
            TenantHelper::switchTenant($empresaId);
            $capacitacion = Capacitacion::where('token', $token)->firstOrFail();

            if (!$capacitacion->aceptaRegistros()) {
                return back()->with('error', 'El registro para esta capacitación está cerrado.');
            }

            $data = $request->validate([
                'cedula'   => 'required|string|max:20',
                'nombre'   => 'required|string|max:255',
                'correo'   => 'nullable|email|max:255',
                'telefono' => 'nullable|string|max:50',
            ]);

            // Buscar si ya existe por cédula
            $asistente = CapacitacionAsistente::where('capacitacion_id', $capacitacion->id)
                ->where('cedula', $data['cedula'])
                ->first();

            if ($asistente) {
                if ($asistente->esConfirmado()) {
                    return back()->with('info', 'Ya confirmaste tu asistencia a esta capacitación.');
                }
                // Confirmar participante programado
                $asistente->update([
                    'estado'             => 'confirmado',
                    'fecha_confirmacion' => now(),
                    'ip_registro'        => $request->ip(),
                    'correo'             => $data['correo'] ?? $asistente->correo,
                    'telefono'           => $data['telefono'] ?? $asistente->telefono,
                ]);
            } else {
                // Externo: crear y confirmar directamente
                CapacitacionAsistente::create([
                    'capacitacion_id'    => $capacitacion->id,
                    'cedula'             => $data['cedula'],
                    'nombre'             => $data['nombre'],
                    'correo'             => $data['correo'] ?? null,
                    'telefono'           => $data['telefono'] ?? null,
                    'estado'             => 'confirmado',
                    'fecha_confirmacion' => now(),
                    'ip_registro'        => $request->ip(),
                    'created_at'         => now(),
                ]);
            }

            return back()->with('success', '¡Asistencia confirmada exitosamente!');

        } catch (\Throwable $e) {
            Log::error('CapacitacionController@guardarRegistro: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al procesar el registro. Intenta de nuevo.');
        }
    }
}
