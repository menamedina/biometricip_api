<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\DispositivoBiometrico;
use App\Models\Horario;
use App\Models\SyncLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    /**
     * Retorna los dispositivos activos del tenant.
     *
     * GET /api/agent/devices
     * Header: X-Agent-Token: {token}
     */
    public function getDevices(Request $request): JsonResponse
    {
        $devices = DispositivoBiometrico::with('sede')
            ->where('is_active', true)
            ->orderBy('nombre')
            ->get()
            ->map(fn($d) => [
                'id'     => $d->id,
                'nombre' => $d->nombre,
                'ip'     => $d->ip,
                'puerto' => $d->puerto,
            ]);

        return response()->json(['devices' => $devices]);
    }

    /**
     * Recibe usuarios del dispositivo y crea los que no existen en la plataforma.
     *
     * POST /api/agent/users/sync
     * Header: X-Agent-Token: {token}
     * Body: { device_id: 1, serial: "...", usuarios: [...] }
     */
    public function syncUsers(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|integer',
            'serial'    => 'required|string',
            'usuarios'  => 'required|array|min:1',
        ]);

        $empresa = $request->_empresa;

        $device = DispositivoBiometrico::where('id', $request->device_id)
                    ->where('is_active', true)
                    ->first();

        if (!$device) {
            return response()->json(['message' => 'Dispositivo no encontrado.'], 404);
        }

        if ($device->numero_serie && $device->numero_serie !== $request->serial) {
            return response()->json(['message' => 'Serial del dispositivo no coincide.'], 403);
        }

        // Filtrar usuarios con cedula valida y construir mapa cedula => nombre
        $incoming = collect($request->usuarios)
            ->map(fn($u) => ['cedula' => trim($u['userid'] ?? ''), 'nombre' => trim($u['name'] ?? '')])
            ->filter(fn($u) => $u['cedula'] !== '')
            ->keyBy('cedula');

        $omitidos = count($request->usuarios) - $incoming->count();

        // Cedulas que ya existen en la BD — una sola query
        $existentes = User::whereIn('cedula', $incoming->keys())
                          ->where('empresa_id', $empresa->id)
                          ->pluck('cedula')
                          ->flip();

        $nuevos = $incoming->reject(fn($u) => $existentes->has($u['cedula']));
        $omitidos += $existentes->count();

        $now      = now();
        $password = Hash::make(Str::random(16));

        // Obtener el ultimo codigo EMP-XXXX para seguir la secuencia
        $ultimo = User::where('empresa_id', $empresa->id)
            ->where('codigo_empleado', 'regexp', '^EMP-[0-9]+$')
            ->orderByRaw('CAST(SUBSTRING(codigo_empleado, 5) AS UNSIGNED) DESC')
            ->value('codigo_empleado');

        $siguiente = $ultimo ? ((int) substr($ultimo, 4)) + 1 : 1;

        $inserts = $nuevos->map(function ($u) use ($empresa, $password, $now, &$siguiente) {
            $codigo = 'EMP-' . str_pad($siguiente++, 4, '0', STR_PAD_LEFT);
            return [
                'name'             => $u['nombre'] ?: "Empleado {$u['cedula']}",
                'cedula'           => $u['cedula'],
                'codigo_empleado'  => $codigo,
                'email'            => "{$u['cedula']}@{$empresa->id}.local",
                'password'         => $password,
                'role'             => 'empleado',
                'tipo'             => 'usuario',
                'is_active'        => 1,
                'empresa_id'       => $empresa->id,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        })->values()->toArray();

        if (!empty($inserts)) {
            User::insert($inserts);
        }

        $creados = count($inserts);

        return response()->json([
            'message'  => 'Sincronizacion de usuarios completada.',
            'creados'  => $creados,
            'omitidos' => $omitidos,
        ]);
    }

    /**
     * Recibe marcaciones desde el agente local y las guarda en el tenant.
     *
     * POST /api/agent/sync
     * Header: X-Agent-Token: {token}
     * Body: { device_id: 1, registros: [...] }
     */
    public function syncAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'device_id' => 'required|integer',
            'serial'    => 'required|string',
            'registros' => 'required|array|min:1',
        ]);

        $empresa = $request->_empresa;

        $device = DispositivoBiometrico::where('id', $request->device_id)
                    ->whereNotNull('sede_id')
                    ->where('is_active', true)
                    ->with('sede')
                    ->first();

        if (!$device) {
            return response()->json(['message' => 'Dispositivo no encontrado.'], 404);
        }

        if ($device->numero_serie && $device->numero_serie !== $request->serial) {
            return response()->json(['message' => 'Serial del dispositivo no coincide.'], 403);
        }

        $records = $request->registros;

        // Mapear cédulas a user_ids — filtrado por empresa
        $cedulas = collect($records)->pluck('id')->unique()->filter()->toArray();
        $userMap = User::whereIn('cedula', $cedulas)
                        ->where('empresa_id', $empresa->id)
                        ->pluck('id', 'cedula');

        $created = 0;
        $skipped = 0;
        $noUser  = 0;

        foreach ($records as $record) {
            $cedula    = $record['id'] ?? null;
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

            $type = (int) ($record['type'] ?? 0);
            $tipo = $type === 0 ? 'entrada' : 'salida';

            $exists = AttendanceRecord::where('user_id', $userId)
                ->where('dispositivo_id', $device->id)
                ->where('uid_dispositivo', $record['uid'] ?? null)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            $empleado = User::where('id', $userId)->where('empresa_id', $empresa->id)->first();
            $horario  = $empleado?->horario_id ? Horario::find($empleado->horario_id) : null;

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

        $device->update(['ultima_sync' => now()]);

        SyncLog::create([
            'dispositivo_id'   => $device->id,
            'registros_nuevos' => $created,
            'registros_total'  => count($records),
            'status'           => 'ok',
            'mensaje'          => "Agente local — Nuevos: {$created}, Omitidos: {$skipped}, Sin usuario: {$noUser}",
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
}
