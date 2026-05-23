<?php

namespace App\Http\Middleware;

use App\Helpers\TenantHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        if ($user->empresa_id !== null) {
            TenantHelper::switchTenant($user->empresa_id);
        }

        return $next($request);
    }
}
