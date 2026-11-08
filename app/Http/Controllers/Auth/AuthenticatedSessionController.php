<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Bitacora;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Muestra la página de inicio de sesión bancaria.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Autentica al usuario y redirige según su rol.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // Verificar que el usuario esté activo
        if (! $user->isActivo()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Tu cuenta está inactiva o bloqueada. Contacta al administrador.',
            ]);
        }

        Bitacora::registrar(
            'autenticacion',
            "Inicio de sesión exitoso — rol: {$user->rol}",
            $user->id,
        );

        // Redirigir al dashboard general (adaptado por rol en el frontend)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Cierra la sesión del usuario actual.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            Bitacora::registrar(
                'cierre_sesion',
                'El usuario cerró sesión',
                $user->id,
            );
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
