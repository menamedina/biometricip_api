<?php

namespace App\Http\Controllers\Api;

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
        Log::info('API Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        Log::info('API Login user lookup', [
            'email' => $request->email,
            'user_found' => $user !== null,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_role' => $user?->role,
            'user_active' => $user?->is_active,
        ]);

        if (!$user) {
            Log::warning('API Login failed: user not found', ['email' => $request->email]);
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        $passwordOk = Hash::check($request->password, $user->password);
        Log::info('API Login password check', [
            'user_id' => $user->id,
            'password_ok' => $passwordOk,
        ]);

        if (!$passwordOk) {
            Log::warning('API Login failed: wrong password', [
                'user_id' => $user->id,
                'email' => $request->email,
            ]);
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (!$user->is_active) {
            Log::warning('API Login failed: inactive user', ['user_id' => $user->id]);
            return response()->json(['message' => 'Tu cuenta ha sido desactivada.'], 403);
        }

        $empleado = $user->empleado;
        Log::info('API Login empleado check', [
            'user_id' => $user->id,
            'has_empleado' => $empleado !== null,
            'empleado_id' => $empleado?->id,
            'empleado_activo' => $empleado?->is_active,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        Log::info('API Login success', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('empleado');

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'empleado' => $user->empleado,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente.']);
    }
}
