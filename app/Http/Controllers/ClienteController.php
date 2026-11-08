<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    /**
     * Muestra la lista de clientes con soporte para búsqueda.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $clientes = Cliente::query()
            ->when($search, function ($query, $search) {
                $query->where('numero_documento', 'ilike', "%{$search}%")
                    ->orWhere('nombres', 'ilike', "%{$search}%")
                    ->orWhere('apellidos', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            })
            ->withCount('cuentas', 'prestamos')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('clientes/Index', [
            'clientes' => $clientes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Muestra el formulario para registrar un nuevo cliente.
     */
    public function create(): Response
    {
        return Inertia::render('clientes/Create');
    }

    /**
     * Almacena un nuevo cliente y genera automáticamente su acceso a Banca Digital (RF-004, RF-005).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'tipo_documento' => ['required', 'in:ci,pasaporte,ruc'],
            'numero_documento' => ['required', 'string', 'max:50', 'unique:clientes,numero_documento'],
            'email' => ['required', 'email', 'max:150', 'unique:clientes,email'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'crear_usuario' => ['nullable', 'boolean'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $cliente = DB::transaction(function () use ($validated, $request) {
            $userId = null;
            $email = strtolower($validated['email']);
            $debeCrearUsuario = $request->boolean('crear_usuario', true);

            if ($debeCrearUsuario) {
                $usernameBase = ! empty($validated['username'])
                    ? Str::slug($validated['username'], '.')
                    : Str::slug($validated['nombres'].'.'.$validated['apellidos'], '.');

                $username = $usernameBase;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $usernameBase.$counter;
                    $counter++;
                }

                $rawPassword = ! empty($validated['password']) ? $validated['password'] : 'password123';

                $user = User::where('email', $email)->first();
                if (! $user) {
                    $user = User::create([
                        'name' => $validated['nombres'],
                        'apellidos' => $validated['apellidos'],
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make($rawPassword),
                        'rol' => 'cliente',
                        'estado' => 'activo',
                        'telefono' => $validated['telefono'] ?? null,
                    ]);
                }

                $userId = $user->id;
            }

            $clienteData = [
                'nombres' => $validated['nombres'],
                'apellidos' => $validated['apellidos'],
                'tipo_documento' => $validated['tipo_documento'],
                'numero_documento' => $validated['numero_documento'],
                'email' => $validated['email'],
                'telefono' => $validated['telefono'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'estado' => 'activo',
                'registrado_por' => $request->user()?->id,
                'user_id' => $userId,
            ];

            $cliente = Cliente::create($clienteData);

            Bitacora::registrar(
                accion: 'Registro de Cliente',
                detalles: "Se registró al cliente {$cliente->nombreCompleto()} con documento {$cliente->numero_documento}".($userId ? " y acceso web habilitado (Email: {$email})" : ''),
                tablaAfectada: 'clientes',
                registroId: $cliente->id
            );

            return $cliente;
        });

        $msg = 'Cliente registrado exitosamente.';
        if ($cliente->user_id) {
            $passInfo = ! empty($request->input('password')) ? $request->input('password') : 'password123';
            $msg .= " Se ha habilitado su acceso a Banca Digital con el correo {$cliente->email} (Contraseña: {$passInfo}).";
        }

        return redirect()->route('clientes.show', $cliente->id)
            ->with('success', $msg);
    }

    /**
     * Habilita o regenera el acceso a Banca Digital para un cliente existente.
     */
    public function habilitarAccesoWeb(Request $request, Cliente $cliente): RedirectResponse
    {
        $email = strtolower($cliente->email);
        $rawPassword = $request->input('password') ?: 'password123';

        $user = User::where('email', $email)->first();

        if (! $user) {
            $usernameBase = Str::slug($cliente->nombres.'.'.$cliente->apellidos, '.');
            $username = $usernameBase;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $usernameBase.$counter;
                $counter++;
            }

            $user = User::create([
                'name' => $cliente->nombres,
                'apellidos' => $cliente->apellidos,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($rawPassword),
                'rol' => 'cliente',
                'estado' => 'activo',
                'telefono' => $cliente->telefono,
            ]);
        } else {
            $user->update([
                'password' => Hash::make($rawPassword),
                'estado' => 'activo',
            ]);
        }

        $cliente->update(['user_id' => $user->id]);

        Bitacora::registrar(
            accion: 'Habilitar Acceso Web',
            detalles: "Se habilitó acceso digital para cliente {$cliente->nombreCompleto()} con usuario {$user->username}",
            tablaAfectada: 'clientes',
            registroId: $cliente->id
        );

        return back()->with('success', "Acceso a Banca Digital habilitado para {$user->email} con contraseña: {$rawPassword}");
    }

    /**
     * Muestra el expediente completo del cliente con sus cuentas y préstamos.
     */
    public function show(Cliente $cliente): Response
    {
        $cliente->load([
            'usuario',
            'cuentas.tarjeta',
            'prestamos' => function ($query) {
                $query->latest();
            },
            'huellas.registradoPor',
            'registradoPor',
        ]);

        return Inertia::render('clientes/Show', [
            'cliente' => $cliente,
        ]);
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit(Cliente $cliente): Response
    {
        return Inertia::render('clientes/Edit', [
            'cliente' => $cliente,
        ]);
    }

    /**
     * Actualiza la información del cliente.
     */
    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $validated = $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'tipo_documento' => ['required', 'in:ci,pasaporte,ruc'],
            'numero_documento' => ['required', 'string', 'max:50', "unique:clientes,numero_documento,{$cliente->id}"],
            'email' => ['required', 'email', 'max:150', "unique:clientes,email,{$cliente->id}"],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'estado' => ['required', 'in:activo,inactivo'],
        ]);

        $cliente->update($validated);

        Bitacora::registrar(
            accion: 'Actualización de Cliente',
            detalles: "Se actualizaron los datos del cliente {$cliente->nombreCompleto()}",
            tablaAfectada: 'clientes',
            registroId: $cliente->id
        );

        return redirect()->route('clientes.show', $cliente->id)
            ->with('success', 'Datos del cliente actualizados correctamente.');
    }
}
