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
            'registros' => 'required|array|min:1',
        ]);

        $empresa = $request->_empresa;

        $device = DispositivoBiometrico::where('id', $request->device_id)
                    ->whereNotNull('sede_id')
                    ->first();

        if (!$device) {
            return response()->json(['message' => 'Dispositivo no encontrado.'], 404);
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
