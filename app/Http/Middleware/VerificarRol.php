<?php

namespace App\Http\Middleware;

use App\Models\Bitacora;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Middleware RBAC: restringe acceso según los roles permitidos.
     * Uso en rutas: ->middleware('rol:administrador,cajero')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->rol, $roles, true)) {
            Bitacora::registrar(
                'autenticacion',
                "Acceso denegado a [{$request->path()}] para rol [{$user?->rol}]",
                $user?->id,
            );

            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
