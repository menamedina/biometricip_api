<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->empresa_id !== null || $user->role !== 'admin') {
            return response()->json(['message' => 'Acceso restringido a superadmin.'], 403);
        }

        return $next($request);
    }
}
