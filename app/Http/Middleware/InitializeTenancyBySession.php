<?php

namespace App\Http\Middleware;

use App\Helpers\TenantHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyBySession
{
    public function handle(Request $request, Closure $next): Response
    {
        $empresaId = session('empresa_id');

        if ($empresaId !== null) {
            TenantHelper::switchTenant((int) $empresaId);
        }

        return $next($request);
    }
}
