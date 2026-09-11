<?php

namespace App\Http\Controllers;

use App\Helpers\AiHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'messages'   => 'required|array|min:1|max:50',
            'messages.*.role'    => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:4000',
        ]);

        $user      = auth()->user();
        $empresaId = $user->admin_tenant ? null : $user->empresa_id;

        try {
            $respuesta = AiHelper::chat($empresaId, $request->messages);
            return response()->json(['reply' => $respuesta]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
