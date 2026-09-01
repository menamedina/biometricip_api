<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushHistorial;
use App\Models\PushHistorialDetalle;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Enviar notificación a usuarios específicos o a todos.
     */
    public function send(Request $request, FcmService $fcmService): JsonResponse
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'body'       => 'required|string|max:1000',
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'lider_id'   => 'nullable|integer|exists:users,id',
            'data'       => 'nullable|array',
        ]);

        $title = $request->title;
        $body  = $request->body;
        $data  = $request->data ?? [];

        $tipoDestinatario = 'all';
        $liderIdGuardar   = null;
        $userIdsGuardar   = null;
        $destinatarioIds  = [];

        if ($request->filled('lider_id')) {
            $tipoDestinatario = 'lider';
            $liderIdGuardar   = $request->lider_id;
            $destinatarioIds  = User::where('lider_id', $request->lider_id)
                ->where('is_active', true)->pluck('id')->toArray();
            $result = $fcmService->sendToUsers($destinatarioIds, $title, $body, $data);
        } elseif ($request->filled('user_ids')) {
            $tipoDestinatario = 'selected';
            $userIdsGuardar   = $request->user_ids;
            $destinatarioIds  = $request->user_ids;
            $result = $fcmService->sendToUsers($destinatarioIds, $title, $body, $data);
        } else {
            $result = $fcmService->sendToAll($title, $body, $data);
        }

        $authUser  = Auth::user();
        $historial = PushHistorial::create([
            'empresa_id'        => $authUser->empresa_id ?? null,
            'enviado_por'       => $authUser->id,
            'titulo'            => $title,
            'mensaje'           => $body,
            'tipo_destinatario' => $tipoDestinatario,
            'lider_id'          => $liderIdGuardar,
            'user_ids'          => $userIdsGuardar,
            'total_enviados'    => ($result['success'] ?? 0) + ($result['failure'] ?? 0),
            'total_exitosos'    => $result['success'] ?? 0,
            'total_fallidos'    => $result['failure'] ?? 0,
            'created_at'        => now(),
        ]);

        if (! empty($destinatarioIds)) {
            $now     = now();
            $detalle = array_map(fn ($uid) => [
                'historial_id' => $historial->id,
                'user_id'      => $uid,
                'exitoso'      => 0,
                'created_at'   => $now,
            ], $destinatarioIds);

            PushHistorialDetalle::insert($detalle);
        }

        return response()->json([
            'message' => 'Notificación enviada',
            'result'  => $result,
        ]);
    }
}
