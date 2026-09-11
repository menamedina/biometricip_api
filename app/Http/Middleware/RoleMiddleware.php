<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autenticado.'], 401);
            }
            abort(401);
        }

        // Compatibilidad: verificar rol legacy (string) o rol Spatie
        $tieneRol = in_array($user->role, $roles) || $user->hasAnyRole($roles);

        if (!$tieneRol) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tienes permiso para acceder a esta sección.'], 403);
            }
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
