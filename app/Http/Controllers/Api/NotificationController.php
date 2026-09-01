<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        if ($request->filled('lider_id')) {
            $userIds = User::where('lider_id', $request->lider_id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
            $result = $fcmService->sendToUsers($userIds, $title, $body, $data);
        } elseif ($request->filled('user_ids')) {
            $result = $fcmService->sendToUsers($request->user_ids, $title, $body, $data);
        } else {
            $result = $fcmService->sendToAll($title, $body, $data);
        }

        return response()->json([
            'message' => 'Notificación enviada',
            'result' => $result,
        ]);
    }
}
