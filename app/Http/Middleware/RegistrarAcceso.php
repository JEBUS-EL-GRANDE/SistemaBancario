<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RegistrarAcceso
{
    /**
     * Registra el último acceso y bloques de sesión en la bitácora.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            $user = Auth::user();
            $user->ultimo_acceso = now();
            $user->saveQuietly();
        }

        return $response;
    }
}
