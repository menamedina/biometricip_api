<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TenantHelper;
use App\Http\Controllers\Controller;
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

    private function userData(User $user): array
    {
        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'role'            => $user->role,
            'empresa_id'      => $user->empresa_id,
            'codigo_empleado' => $user->codigo_empleado,
            'departamento'    => $user->departamento,
            'cargo'           => $user->cargo,
            'telefono'        => $user->telefono,
            'foto_url'        => $user->foto_url,
        ];
    }
}
