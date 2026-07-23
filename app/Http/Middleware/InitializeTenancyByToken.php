<?php

namespace App\Http\Middleware;

use App\Helpers\TenantHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            Log::warning('Tenancy: request sin usuario autenticado', ['url' => $request->fullUrl()]);
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        Log::info('Tenancy: usuario autenticado', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'empresa_id' => $user->empresa_id,
            'role'       => $user->role,
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
        ]);

        // admin_tenant puede operar en cualquier empresa mediante el header X-Empresa-Id
        // Si no viene el header, usa su propio empresa_id como fallback
        if ($user->admin_tenant) {
            $empresaId = $request->header('X-Empresa-Id') ?? $user->empresa_id;
            if ($empresaId) {
                try {
                    TenantHelper::switchTenant((int) $empresaId);
                    Log::info('Tenancy: admin_tenant activó tenant', ['empresa_id' => $empresaId]);
                } catch (\Throwable $e) {
                    Log::error('Tenancy: fallo al activar tenant (admin_tenant)', [
                        'empresa_id' => $empresaId,
                        'error'      => $e->getMessage(),
                    ]);
                    return response()->json([
                        'message' => 'Tenant no configurado para empresa_id=' . $empresaId . ': ' . $e->getMessage(),
                    ], 503);
                }
            }
            return $next($request);
        }

        if ($user->empresa_id === null) {
            Log::error('Tenancy: empresa_id es null', ['user_id' => $user->id, 'email' => $user->email]);
            return response()->json(['message' => 'El usuario no tiene empresa asignada.'], 422);
        }

        try {
            TenantHelper::switchTenant($user->empresa_id);
            Log::info('Tenancy: tenant activado', ['empresa_id' => $user->empresa_id]);
        } catch (\Throwable $e) {
            Log::error('Tenancy: fallo al activar tenant', [
                'empresa_id' => $user->empresa_id,
                'error'      => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Tenant no configurado para empresa_id=' . $user->empresa_id . ': ' . $e->getMessage(),
            ], 503);
        }

        return $next($request);
    }
}
