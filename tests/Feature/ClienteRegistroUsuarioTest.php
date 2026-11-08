<?php

use App\Models\Cliente;
use App\Models\User;

test('se puede registrar cliente y crear simultaneamente su usuario de acceso digital', function () {
    $admin = User::factory()->create([
        'apellidos' => 'Admin Test',
        'username' => 'admin_test',
        'rol' => 'administrador',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($admin)->post(route('clientes.store'), [
        'nombres' => 'Carlos',
        'apellidos' => 'Miranda',
        'tipo_documento' => 'ci',
        'numero_documento' => '99887766',
        'email' => 'carlos.miranda@test.com',
        'telefono' => '71122334',
        'direccion' => 'Calle Los Álamos 45',
        'crear_usuario' => true,
        'username' => 'carlos.miranda',
        'password' => 'secret123',
    ]);

    $cliente = Cliente::where('email', 'carlos.miranda@test.com')->first();
    expect($cliente)->not->toBeNull();

    $response->assertRedirect(route('clientes.show', $cliente->id));

    $user = User::where('email', 'carlos.miranda@test.com')->first();
    expect($user)->not->toBeNull()
        ->and($user->rol)->toBe('cliente')
        ->and($user->username)->toBe('carlos.miranda')
        ->and($cliente->user_id)->toBe($user->id);
});

test('se puede habilitar acceso web a un cliente que no tenia usuario previo', function () {
    $admin = User::factory()->create([
        'apellidos' => 'Admin Test 2',
        'username' => 'admin_test2',
        'rol' => 'administrador',
        'estado' => 'activo',
    ]);

    $cliente = Cliente::create([
        'nombres' => 'Pedro',
        'apellidos' => 'Gomez',
        'tipo_documento' => 'ci',
        'numero_documento' => '55443322',
        'email' => 'pedro.gomez@test.com',
        'estado' => 'activo',
        'registrado_por' => $admin->id,
        'user_id' => null,
    ]);

    $response = $this->actingAs($admin)->post(route('clientes.acceso-web', $cliente->id), [
        'password' => 'nuevaClave123',
    ]);

    $response->assertSessionHasNoErrors();

    $cliente->refresh();
    expect($cliente->user_id)->not->toBeNull();

    $user = User::find($cliente->user_id);
    expect($user)->not->toBeNull()
        ->and($user->email)->toBe('pedro.gomez@test.com')
        ->and($user->rol)->toBe('cliente');
});
