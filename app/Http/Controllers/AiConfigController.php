<?php

namespace App\Http\Controllers;

use App\Helpers\AiHelper;
use App\Models\AiConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiConfigController extends Controller
{
    public function index()
    {
        $user      = auth()->user();
        $empresaId = $user->admin_tenant ? null : $user->empresa_id;
        $config    = AiConfig::forEmpresa($empresaId);
        $modelos   = AiHelper::modelosPorProveedor();

        return view('admin.ai.config', compact('config', 'modelos'));
    }

    public function save(Request $request): JsonResponse
    {
        $user      = auth()->user();
        $empresaId = $user->admin_tenant ? null : $user->empresa_id;

        $data = $request->validate([
            'proveedor'     => 'required|in:openai,anthropic,deepseek,glm',
            'modelo'        => 'required|string|max:100',
            'api_key'       => 'nullable|string|max:500',
            'system_prompt' => 'nullable|string|max:2000',
            'activo'        => 'boolean',
        ]);

        $config = AiConfig::where('empresa_id', $empresaId)->first();

        if ($config) {
            // Solo actualizar api_key si se envió una nueva
            if (empty($data['api_key'])) {
                unset($data['api_key']);
            }
            $config->update($data);
        } else {
            if (empty($data['api_key'])) {
                return response()->json(['message' => 'La API key es requerida para la primera configuración.'], 422);
            }
            $data['empresa_id'] = $empresaId;
            AiConfig::create($data);
        }

        return response()->json(['message' => 'Configuración guardada correctamente.']);
    }

    public function test(Request $request): JsonResponse
    {
        $user      = auth()->user();
        $empresaId = $user->admin_tenant ? null : $user->empresa_id;

        try {
            $respuesta = AiHelper::chat($empresaId, [
                ['role' => 'user', 'content' => 'Responde solo: "Conexión exitosa con ' . ucfirst($request->query('proveedor', 'IA')) . '"'],
            ]);
            return response()->json(['message' => $respuesta]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
