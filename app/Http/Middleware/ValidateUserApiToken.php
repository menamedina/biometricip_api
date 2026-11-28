<?php

namespace App\Http\Middleware;

use App\Helpers\TenantHelper;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateUserApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');

        if (!str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Token de API requerido.'], 401);
        }

        $token = substr($header, 7);

        $user = User::where('api_token', $token)->where('is_active', true)->first();

        if (!$user) {
            return response()->json(['message' => 'Token inválido o usuario inactivo.'], 401);
        }

        TenantHelper::switchTenant($user->empresa_id);

        $request->merge(['_api_user' => $user]);

        return $next($request);
    }
}
