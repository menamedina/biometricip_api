<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Registrar o actualizar token FCM del dispositivo.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|max:500',
            'device_type' => 'required|in:android,ios,web',
            'device_name' => 'nullable|string|max:255',
        ]);

        // Eliminar tokens anteriores del mismo usuario y tipo de dispositivo
        DeviceToken::where('user_id', $request->user()->id)
            ->where('device_type', $request->device_type)
            ->where('token', '!=', $request->token)
            ->delete();

        $deviceToken = DeviceToken::updateOrCreate(
            ['token' => $request->token],
            [
                'user_id' => $request->user()->id,
                'device_type' => $request->device_type,
                'device_name' => $request->device_name,
                'is_active' => true,
            ]
        );

        return response()->json([
            'message' => 'Token registrado correctamente',
            'device_token' => $deviceToken,
        ]);
    }

    /**
     * Eliminar token FCM (logout o desregistro).
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        DeviceToken::where('token', $request->token)
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'message' => 'Token eliminado correctamente',
        ]);
    }
}
