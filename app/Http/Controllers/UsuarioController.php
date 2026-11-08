<?php

namespace App\Http\Controllers;

use App\Models\Banco;
use App\Models\Bitacora;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    /**
     * Muestra el listado de usuarios del sistema bancario (RF-001, RF-002, RF-003).
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $rol = $request->input('rol');
        $estado = $request->input('estado');
        $user = $request->user();

        $usuarios = User::query()
            ->with('banco')
            ->when($user->banco_id !== null, fn ($q) => $q->where('banco_id', $user->banco_id))
            ->when($search, function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhere('apellidos', 'ilike', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($rol, fn ($q, $r) => $q->where('rol', $r))
            ->when($estado, fn ($q, $e) => $q->where('estado', $e))
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('usuarios/Index', [
            'usuarios' => $usuarios,
            'filters' => [
                'search' => $search,
                'rol' => $rol,
                'estado' => $estado,
            ],
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo usuario interno.
     */
    public function create(): Response
    {
        return Inertia::render('usuarios/Create', [
            'bancos' => Banco::where('estado', 'activo')->get(['id', 'nombre', 'codigo']),
        ]);
    }

    /**
     * Almacena un nuevo usuario en la base de datos (RF-001).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'banco_id' => ['nullable', 'exists:bancos,id'],
            'name' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'email' => ['required', 'string', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
            'rol' => ['required', 'in:administrador,cajero,ejecutivo_credito,cliente'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ]);

        $usuario = User::create([
            'banco_id' => $validated['banco_id'] ?? $request->user()->banco_id,
            'name' => $validated['name'],
            'apellidos' => $validated['apellidos'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'rol' => $validated['rol'],
            'estado' => 'activo',
            'telefono' => $validated['telefono'] ?? null,
        ]);

        Bitacora::registrar(
            accion: 'Creación de Usuario',
            detalles: "Usuario {$usuario->username} creado con rol {$usuario->rol}",
            tablaAfectada: 'users',
            registroId: $usuario->id
        );

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$usuario->username}' creado exitosamente.");
    }

    /**
     * Muestra el formulario para editar un usuario.
     */
    public function edit(User $usuario): Response
    {
        return Inertia::render('usuarios/Edit', [
            'usuario' => $usuario,
            'bancos' => Banco::where('estado', 'activo')->get(['id', 'nombre', 'codigo']),
        ]);
    }

    /**
     * Actualiza la información y rol del usuario.
     */
    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'banco_id' => ['nullable', 'exists:bancos,id'],
            'name' => ['required', 'string', 'max:100'],
            'apellidos' => ['nullable', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', "unique:users,username,{$usuario->id}", 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'email' => ['required', 'string', 'email', 'max:150', "unique:users,email,{$usuario->id}"],
            'rol' => ['required', 'in:administrador,cajero,ejecutivo_credito,cliente'],
            'estado' => ['required', 'in:activo,inactivo,bloqueado'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', Password::defaults()],
        ]);

        $data = [
            'name' => $validated['name'],
            'apellidos' => $validated['apellidos'] ?? null,
            'username' => $validated['username'],
            'email' => $validated['email'],
            'rol' => $validated['rol'],
            'estado' => $validated['estado'],
            'telefono' => $validated['telefono'] ?? null,
        ];

        if (array_key_exists('banco_id', $validated)) {
            $data['banco_id'] = $validated['banco_id'];
        }

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $usuario->update($data);

        Bitacora::registrar(
            accion: 'Actualización de Usuario',
            detalles: "Se actualizaron datos del usuario {$usuario->username} (Estado: {$usuario->estado}, Rol: {$usuario->rol})",
            tablaAfectada: 'users',
            registroId: $usuario->id
        );

        return redirect()->route('usuarios.index')
            ->with('success', "Usuario '{$usuario->username}' actualizado correctamente.");
    }

    /**
     * Cambia el estado de un usuario (RF-003: Activar, Inactivar, Bloquear).
     */
    public function cambiarEstado(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:activo,inactivo,bloqueado'],
        ]);

        if ($usuario->id === $request->user()?->id) {
            abort(422, 'No puedes cambiar el estado de tu propia cuenta.');
        }

        $estadoAnterior = $usuario->estado;
        $usuario->estado = $validated['estado'];
        $usuario->save();

        Bitacora::registrar(
            accion: 'Cambio de Estado de Usuario',
            detalles: "Usuario {$usuario->username} cambió de {$estadoAnterior} a {$usuario->estado}",
            tablaAfectada: 'users',
            registroId: $usuario->id
        );

        return back()->with('success', "Estado del usuario cambiado a '{$usuario->estado}'.");
    }
}
