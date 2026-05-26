<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Horario;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Las credenciales proporcionadas son incorrectas.'],
                ]);
            }

            if (!$user->is_active) {
                return response()->json(['message' => 'Tu cuenta ha sido desactivada.'], 403);
            }

            if ($user->empresa_id !== null) {
                TenantHelper::switchTenant($user->empresa_id);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user'  => $this->userData($user),
                'token' => $token,
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('API Login exception', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'email'   => $request->email,
            ]);
            return response()->json(['message' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userData($request->user()),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required'  => 'La nueva contraseña es obligatoria.',
            'new_password.min'       => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $user = $request->user();

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }

    private function userData(User $user): array
    {
        $departamentoNombre = null;
        $cargoNombre        = null;
        $empresaNombre      = null;

        $horarioData = null;

        if ($user->empresa_id !== null) {
            $empresaNombre = Empresa::find($user->empresa_id)?->nombre;
            if ($user->departamento_id) {
                $departamentoNombre = Departamento::find($user->departamento_id)?->nombre;
            }
            if ($user->cargo_id) {
                $cargoNombre = Cargo::find($user->cargo_id)?->nombre;
            }
            if ($user->horario_id) {
                $h = Horario::find($user->horario_id);
                if ($h) {
                    $horarioData = [
                        'id'                     => $h->id,
                        'nombre'                 => $h->nombre,
                        'hora_entrada'           => $h->hora_entrada,
                        'hora_salida'            => $h->hora_salida,
                        'tiene_almuerzo_marcado' => (bool) $h->tiene_almuerzo_marcado,
                        'duracion_almuerzo_min'  => $h->duracion_almuerzo_min,
                        'hora_almuerzo_inicio'   => $h->hora_almuerzo_inicio,
                        'hora_almuerzo_fin'      => $h->hora_almuerzo_fin,
                    ];
                }
            }
        }

        $sedeData = null;
        if ($user->sede_id) {
            $sede = Sede::find($user->sede_id);
            if ($sede) {
                $sedeData = [
                    'id'        => $sede->id,
                    'nombre'    => $sede->nombre,
                    'lat'       => $sede->lat,
                    'lng'       => $sede->lng,
                    'radio_mts' => $sede->radio_mts,
                ];
            }
        }

        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'role'            => $user->role,
            'empresa_id'      => $user->empresa_id,
            'empresa'         => $empresaNombre,
            'codigo_empleado' => $user->codigo_empleado,
            'departamento_id' => $user->departamento_id,
            'cargo_id'        => $user->cargo_id,
            'horario_id'      => $user->horario_id,
            'sede_id'         => $user->sede_id,
            'departamento'    => $departamentoNombre,
            'cargo'           => $cargoNombre,
            'horario'         => $horarioData,
            'sede'            => $sedeData,
            'telefono'        => $user->telefono,
            'foto_url'        => $user->foto_url,
            'is_active'       => $user->is_active,
        ];
    }
}
