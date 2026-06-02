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

        if (!$user || !in_array($user->role, $roles, true)) {
            abort(403, 'No tienes permiso para acceder a esta seccion.');
        }

        // Académico con licencia médica activa: bloqueo total excepto logout
        if ($user->role === 'academico' && $user->bloqueado_por_licencia) {
            if (!$request->routeIs('logout')) {
                abort(403, 'Tu acceso está suspendido por licencia médica activa. Contacta al secretario de tu facultad.');
            }
        }

        return $next($request);
    }
}