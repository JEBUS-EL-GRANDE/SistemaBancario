<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $username = explode('@', $request->email)[0].rand(100, 999);
        $user = User::create([
            'name' => $request->name,
            'apellidos' => $request->input('apellidos', 'Usuario'),
            'username' => $request->input('username', $username),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => 'cliente',
            'estado' => 'activo',
        ]);

        // Vincular automáticamente si ya existía un expediente de cliente con este email
        $cliente = Cliente::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();
        if ($cliente) {
            $cliente->update(['user_id' => $user->id]);
        }

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
