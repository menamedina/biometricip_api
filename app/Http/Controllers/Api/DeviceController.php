<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\DispositivoBiometrico;
use App\Models\Horario;
use App\Models\SyncLog;
use App\Models\User;
use App\Services\ZKTecoService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    /**
     * Listar todos los dispositivos.
     */
    public function index(): JsonResponse
    {
        $devices = DispositivoBiometrico::with('sede')
            ->orderBy('nombre')
            ->get();

        return response()->json($devices);
    }

    /**
     * Registrar un nuevo dispositivo.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'sede_id' => 'required|integer|exists:tbl_sedes,id',
            'nombre'  => 'required|string|max:255',
            'ip'      => 'required|ip',
            'puerto'  => 'nullable|integer|min:1|max:65535',
        ]);

        // Probar conexión antes de guardar
        $port = $request->puerto ?? 4370;
        $zk = new ZKTecoService($request->ip, $port);

        if (!$zk->connect()) {
            return response()->json([
                'message' => "No se pudo conectar al dispositivo en {$request->ip}:{$port}. Verifique la IP y que el dispositivo esté encendido.",
            ], 422);
        }

        $info = $zk->getDeviceInfo();
        $zk->disconnect();

        $device = DispositivoBiometrico::create([
            'sede_id'       => $request->sede_id,
            'nombre'        => $request->nombre,
            'ip'            => $request->ip,
            'puerto'        => $port,
            'numero_serie'  => $info['serial'] ?? null,
            'modelo'        => $info['nombre'] ?? null,
            'plataforma'    => $info['plataforma'] ?? null,
            'firmware'      => $info['firmware'] ?? null,
        ]);

        $device->load('sede');

        return response()->json([
            'message' => 'Dispositivo registrado correctamente.',
            'data'    => $device,
        ], 201);
    }

    /**
     * Ver un dispositivo.
     */
    public function show(int $id): JsonResponse
    {
        $device = DispositivoBiometrico::with('sede')->findOrFail($id);
        return response()->json($device);
    }

    /**
     * Actualizar un dispositivo.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $device = DispositivoBiometrico::findOrFail($id);

        $request->validate([
            'sede_id'   => 'nullable|integer|exists:tbl_sedes,id',
            'nombre'    => 'nullable|string|max:255',
            'ip'        => 'nullable|ip',
            'puerto'    => 'nullable|integer|min:1|max:65535',
            'is_active' => 'nullable|boolean',
        ]);

        $device->update($request->only(['sede_id', 'nombre', 'ip', 'puerto', 'is_active']));
        $device->load('sede');

        return response()->json([
            'message' => 'Dispositivo actualizado.',
            'data'    => $device,
        ]);
    }

    /**
     * Eliminar un dispositivo.
     */
    public function destroy(int $id): JsonResponse
    {
        $device = DispositivoBiometrico::findOrFail($id);
        $device->delete();

        return response()->json(['message' => 'Dispositivo eliminado.']);
    }

    /**
     * Probar conexión a un dispositivo registrado.
     */
    public function testConnection(int $id): JsonResponse
    {
        $device = DispositivoBiometrico::findOrFail($id);
        $zk = new ZKTecoService($device->ip, $device->puerto);

        if (!$zk->connect()) {
            return response()->json([
                'connected' => false,
                'message'   => "No se pudo conectar a {$device->ip}:{$device->puerto}.",
            ], 422);
        }

        $info = $zk->getDeviceInfo();
        $users = $zk->getUsers();
        $attendance = $zk->getAttendance();
        $zk->disconnect();

        // Actualizar info del dispositivo si cambió
        $device->update([
            'numero_serie' => $info['serial'] ?? $device->numero_serie,
            'modelo'       => $info['nombre'] ?? $device->modelo,
            'plataforma'   => $info['plataforma'] ?? $device->plataforma,
            'firmware'     => $info['firmware'] ?? $device->firmware,
        ]);

        return response()->json([
            'connected'           => true,
            'message'             => 'Conexión exitosa.',
            'info'                => $info,
            'usuarios_dispositivo' => count($users),
            'registros_asistencia' => count($attendance),
        ]);
    }

    /**
     * Probar conexión a una IP sin registrar el dispositivo.
     */
    public function ping(Request $request): JsonResponse
    {
        $request->validate([
            'ip'     => 'required|ip',
            'puerto' => 'nullable|integer|min:1|max:65535',
        ]);

        $port = $request->puerto ?? 4370;
        $zk = new ZKTecoService($request->ip, $port);

        if (!$zk->connect()) {
            return response()->json([
                'connected' => false,
                'message'   => "No se pudo conectar a {$request->ip}:{$port}.",
            ], 422);
        }

        $info = $zk->getDeviceInfo();
        $users = $zk->getUsers();
        $attendance = $zk->getAttendance();
        $zk->disconnect();

        return response()->json([
            'connected'           => true,
            'message'             => 'Conexión exitosa.',
            'info'                => $info,
            'usuarios_dispositivo' => count($users),
            'registros_asistencia' => count($attendance),
        ]);
    }

    /**
     * Listar usuarios registrados en el dispositivo.
     */
    public function deviceUsers(int $id): JsonResponse
    {
        $device = DispositivoBiometrico::findOrFail($id);
        $zk = new ZKTecoService($device->ip, $device->puerto);

        if (!$zk->connect()) {
            return response()->json(['message' => 'No se pudo conectar al dispositivo.'], 422);
        }

        $deviceUsers = $zk->getUsers();
        $zk->disconnect();

        // Cruzar con usuarios del sistema por cédula
        $cedulas = collect($deviceUsers)->pluck('userid')->filter()->toArray();
        $systemUsers = User::whereIn('cedula', $cedulas)->get()->keyBy('cedula');

        $result = collect($deviceUsers)->map(function ($du) use ($systemUsers) {
            $cedula = $du['userid'] ?? null;
            $matched = $cedula ? ($systemUsers[$cedula] ?? null) : null;

            return [
                'uid'              => $du['uid'] ?? null,
                'id_dispositivo'   => $cedula,
                'nombre_dispositivo' => $du['name'] ?? null,
                'vinculado'        => $matched !== null,
                'user_id'          => $matched?->id,
                'nombre_sistema'   => $matched?->name,
            ];
        });

        return response()->json([
            'total'      => count($deviceUsers),
            'vinculados' => $result->where('vinculado', true)->count(),
            'usuarios'   => $result->values(),
        ]);
    }

    /**
     * Sincronizar registros de asistencia desde el dispositivo.
     */
    public function syncAttendance(int $id): JsonResponse
    {
        $device = DispositivoBiometrico::with('sede')->findOrFail($id);
        $zk = new ZKTecoService($device->ip, $device->puerto);

        if (!$zk->connect()) {
            SyncLog::create([
                'dispositivo_id'  => $device->id,
                'status'          => 'error',
                'mensaje'         => "No se pudo conectar a {$device->ip}:{$device->puerto}",
                'created_at'      => now(),
            ]);

            return response()->json(['message' => 'No se pudo conectar al dispositivo.'], 422);
        }

        // Descargar todos los registros del dispositivo
        $records = $zk->getAttendance();
        $zk->disconnect();

        // Mapear cédulas a user_ids
        $cedulas = collect($records)->pluck('id')->unique()->filter()->toArray();
        $userMap = User::whereIn('cedula', $cedulas)->pluck('id', 'cedula');

        $created = 0;
        $skipped = 0;
        $noUser = 0;

        foreach ($records as $record) {
            $cedula = $record['id'] ?? null;
            $timestamp = $record['timestamp'] ?? null;

            if (!$cedula || !$timestamp) {
                $skipped++;
                continue;
            }

            $userId = $userMap[$cedula] ?? null;
            if (!$userId) {
                $noUser++;
                continue;
            }

            $fechaHora = Carbon::parse($timestamp);

            // Determinar tipo (entrada/salida) según type del dispositivo
            // type: 0 = check-in (entrada), 1 = check-out (salida)
            $type = (int) ($record['type'] ?? 0);
            $tipo = $type === 0 ? 'entrada' : 'salida';

            // Evitar duplicados: mismo usuario, dispositivo, y timestamp
            $exists = AttendanceRecord::where('user_id', $userId)
                ->where('dispositivo_id', $device->id)
                ->where('uid_dispositivo', $record['uid'] ?? null)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $empleado = User::find($userId);
            $horario = $empleado->horario_id ? Horario::find($empleado->horario_id) : null;

            AttendanceRecord::create([
                'user_id'               => $userId,
                'sede_id'               => $device->sede_id,
                'horario_id'            => $horario?->id,
                'tipo'                  => $tipo,
                'lat'                   => $device->sede?->lat ?? 0,
                'lng'                   => $device->sede?->lng ?? 0,
                'metodo'                => 'dispositivo',
                'qr_validado'           => false,
                'geocerca_validada'     => true,
                'distancia_oficina_mts' => 0,
                'fecha_hora'            => $fechaHora,
                'dispositivo_id'        => $device->id,
                'uid_dispositivo'       => $record['uid'] ?? null,
            ]);

            $created++;
        }

        // Actualizar última sincronización
        $device->update(['ultima_sync' => now()]);

        // Log de sincronización
        SyncLog::create([
            'dispositivo_id'   => $device->id,
            'registros_nuevos' => $created,
            'registros_total'  => count($records),
            'status'           => 'ok',
            'mensaje'          => "Nuevos: {$created}, Omitidos: {$skipped}, Sin usuario: {$noUser}",
            'created_at'       => now(),
        ]);

        return response()->json([
            'message'          => 'Sincronización completada.',
            'registros_nuevos' => $created,
            'registros_total'  => count($records),
            'omitidos'         => $skipped,
            'sin_usuario'      => $noUser,
        ]);
    }

    /**
     * Historial de sincronizaciones de un dispositivo.
     */
    public function syncHistory(int $id): JsonResponse
    {
        $device = DispositivoBiometrico::findOrFail($id);

        $logs = SyncLog::where('dispositivo_id', $device->id)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return response()->json($logs);
    }

    /**
     * Limpiar los registros de asistencia del dispositivo (no borra la BD).
     */
    public function clearDevice(int $id): JsonResponse
    {
        $device = DispositivoBiometrico::findOrFail($id);
        $zk = new ZKTecoService($device->ip, $device->puerto);

        if (!$zk->connect()) {
            return response()->json(['message' => 'No se pudo conectar al dispositivo.'], 422);
        }

        $cleared = $zk->clearAttendance();
        $zk->disconnect();

        if (!$cleared) {
            return response()->json(['message' => 'No se pudo limpiar el dispositivo.'], 422);
        }

        SyncLog::create([
            'dispositivo_id'   => $device->id,
            'registros_nuevos' => 0,
            'registros_total'  => 0,
            'status'           => 'ok',
            'mensaje'          => 'Registros del dispositivo limpiados manualmente.',
            'created_at'       => now(),
        ]);

        return response()->json(['message' => 'Registros del dispositivo limpiados correctamente.']);
    }
}
