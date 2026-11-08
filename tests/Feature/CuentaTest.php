<?php

use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\TarjetaDebito;
use App\Models\User;

test('se puede aperturar cuenta con emision de tarjeta de debito y deposito inicial', function () {
    $user = User::factory()->create([
        'apellidos' => 'Cajero Test',
        'username' => 'cajero_test',
        'rol' => 'cajero',
        'estado' => 'activo',
    ]);

    $cliente = Cliente::create([
        'nombres' => 'Benjo',
        'apellidos' => 'Yu',
        'tipo_documento' => 'ci',
        'numero_documento' => '1614381',
        'email' => 'benja@gmail.com',
        'telefono' => '70011223',
        'direccion' => 'Av. Central 123',
        'fecha_nacimiento' => '1995-05-15',
        'estado' => 'activo',
        'registrado_por' => $user->id,
    ]);

    $response = $this->actingAs($user)->post(route('cuentas.store'), [
        'cliente_id' => $cliente->id,
        'tipo' => 'corriente',
        'moneda' => 'BOB',
        'limite_retiro_diario' => 5000,
        'deposito_inicial' => 10000,
        'emitir_tarjeta' => true,
        'pin_tarjeta' => '4321',
    ]);

    $cuenta = Cuenta::where('cliente_id', $cliente->id)->first();
    expect($cuenta)->not->toBeNull()
        ->and($cuenta->saldo)->toEqual('10000.00');

    $response->assertRedirect(route('cuentas.show', $cuenta->id));

    $tarjeta = TarjetaDebito::where('cuenta_id', $cuenta->id)->first();
    expect($tarjeta)->not->toBeNull()
        ->and($tarjeta->bin)->toBe('453288')
        ->and($tarjeta->estado)->toBe('activa');

    $this->assertDatabaseHas('movimientos', [
        'cuenta_id' => $cuenta->id,
        'tipo' => 'deposito',
        'naturaleza' => 'credito',
        'monto' => 10000.00,
    ]);
});
