<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Empresa;
use App\Models\Horario;
use App\Models\Sede;
use App\Models\User;
use App\Models\UserSede;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppAttendanceController extends Controller
{
    public function clock(Request $request): JsonResponse
    {
        // Validar token — acepta X-Api-Token header o Authorization: Bearer
        $token = $request->header('X-Api-Token')
              ?? $request->bearerToken()
              ?? $request->input('api_token');

        Log::info('=== EXT TOKEN DEBUG ===', [
            'x_api_token'   => $request->header('X-Api-Token'),
            'bearer'        => $request->bearerToken(),
            'all_headers'   => $request->headers->all(),
        ]);

        if (!$token) {
            return response()->json(['message' => 'Token requerido (X-Api-Token header, Authorization: Bearer, o api_token en body).'], 401);
        }

        $empresa = Empresa::where('agent_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$empresa) {
            return response()->json(['message' => 'Token inválido o empresa inactiva.'], 401);
        }

        if (!$empresa->agent_token_vigencia || $empresa->agent_token_vigencia->isPast()) {
            return response()->json(['message' => 'Token expirado.'], 401);
        }

        // Activar tenant de la empresa
        TenantHelper::switchTenant($empresa->id);

        $request->validate([
            'lat'      => 'required|numeric|between:-90,90',
            'lng'      => 'required|numeric|between:-180,180',
            'tipo'     => 'required|in:entrada,salida',
            'sede_id'  => 'nullable|integer',
            'user_id'  => 'nullable|integer',
            'cedula'   => 'nullable|string',
            'telefono' => 'nullable|string',
        ]);

        Log::info('=== EXT CLOCK REQUEST ===', [
            'empresa_id' => $empresa->id,
            'lat'        => $request->lat,
            'lng'        => $request->lng,
            'tipo'       => $request->tipo,
        ]);

        // Identificar empleado por user_id o cédula+teléfono
        if ($request->filled('user_id')) {
            $user = User::where('id', $request->user_id)
                        ->where('empresa_id', $empresa->id)
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'Empleado no encontrado en esta empresa.'], 404);
            }
        } elseif ($request->filled('cedula') && $request->filled('telefono')) {
            $user = User::where('cedula', $request->cedula)
                        ->where('telefono', $request->telefono)
                        ->where('empresa_id', $empresa->id)
                        ->first();

            if (!$user) {
                return response()->json(['message' => 'Cédula y teléfono no coinciden o el empleado no existe.'], 401);
            }
        } elseif ($request->filled('cedula')) {
            return response()->json(['message' => 'Se requiere también el teléfono para validar la identidad.'], 422);
        } else {
            return response()->json(['message' => 'Se requiere user_id o cedula+telefono.'], 422);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        // Resolver sede
        $sede = null;

        if ($request->filled('sede_id')) {
            $sede = Sede::where('id', $request->sede_id)->where('is_active', true)->first();

            if (!$sede) {
                return response()->json(['message' => 'La sede indicada no existe o está inactiva.'], 422);
            }
        } else {
            $sedes = Sede::where('is_active', true)->get();

            if ($sedes->isEmpty()) {
                return response()->json(['message' => 'No hay sedes activas registradas.'], 422);
            }

            $menorDistancia = PHP_INT_MAX;
            foreach ($sedes as $s) {
                $d = $this->calcularDistancia((float) $request->lat, (float) $request->lng, (float) $s->lat, (float) $s->lng);
                if ($d < $menorDistancia) {
                    $menorDistancia = $d;
                    $sede = $s;
                }
            }
        }

        // Validar que el usuario tenga asignada esta sede
        $tieneSede = UserSede::where('user_id', $user->id)
            ->where('empresa_id', $empresa->id)
            ->where('sede_id', $sede->id)
            ->exists();

        if (!$tieneSede) {
            return response()->json([
                'message' => 'No tienes permiso para registrar asistencia en esta sede.',
                'sede'    => $sede->nombre,
            ], 403);
        }

        $distancia = $this->calcularDistancia((float) $request->lat, (float) $request->lng, (float) $sede->lat, (float) $sede->lng);

        $geocercaValidada = $distancia <= $sede->radio_mts;

        if (!$geocercaValidada) {
            return response()->json([
                'message'             => 'Estás fuera del rango permitido de la sede.',
                'distancia_mts'       => round($distancia, 2),
                'radio_permitido_mts' => $sede->radio_mts,
                'sede'                => $sede->nombre,
            ], 422);
        }

        $horario = $user->horario_id ? Horario::find($user->horario_id) : null;

        $record = AttendanceRecord::create([
            'user_id'               => $user->id,
            'sede_id'               => $sede->id,
            'horario_id'            => $horario?->id,
            'tipo'                  => $request->tipo,
            'lat'                   => $request->lat,
            'lng'                   => $request->lng,
            'metodo'                => 'whatsapp',
            'qr_validado'           => false,
            'geocerca_validada'     => $geocercaValidada,
            'distancia_oficina_mts' => round($distancia, 2),
            'fecha_hora'            => Carbon::now(config('app.timezone')),
        ]);

        $record->load([
            'user:id,name,cedula,codigo_empleado,empresa_id',
            'sede:id,nombre,direccion',
        ]);

        return response()->json([
            'message' => 'Asistencia registrada correctamente.',
            'data'    => [
                'id'                    => $record->id,
                'tipo'                  => $record->tipo,
                'metodo'                => $record->metodo,
                'fecha_hora'            => $record->fecha_hora,
                'geocerca_validada'     => $record->geocerca_validada,
                'distancia_oficina_mts' => $record->distancia_oficina_mts,
                'empresa'               => ['id' => $empresa->id, 'nombre' => $empresa->nombre],
                'empleado'              => $record->user,
                'sede'                  => $record->sede,
            ],
        ], 201);
    }

    private function calcularDistancia(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
