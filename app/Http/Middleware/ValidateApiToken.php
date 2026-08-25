<?php

namespace App\Http\Middleware;

use App\Helpers\TenantHelper;
use App\Models\Empresa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-Api-Token');

        if (!$token) {
            return response()->json(['message' => 'Token requerido (X-Api-Token).'], 401);
        }

        $empresa = Empresa::where('api_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$empresa) {
            return response()->json(['message' => 'Token inválido o empresa inactiva.'], 401);
        }

        if (!$empresa->api_token_vigencia || $empresa->api_token_vigencia->isPast()) {
            return response()->json(['message' => 'Token expirado.'], 401);
        }

        TenantHelper::switchTenant($empresa->id);

        $request->merge(['_empresa' => $empresa]);

        return $next($request);
    }
}
