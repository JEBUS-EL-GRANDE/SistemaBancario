<?php

use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\User;

test('usuarios no autenticados son redirigidos al login al intentar ver transferencias', function () {
    $response = $this->get('/transferencias');
    $response->assertRedirect('/login');
});

test('un cajero puede procesar una transferencia entre dos cuentas activas', function () {
    $cajero = User::factory()->create(['rol' => 'cajero']);

    $clienteOrigen = Cliente::create([
        'nombres' => 'Carlos',
        'apellidos' => 'Mendoza',
        'tipo_documento' => 'ci',
        'numero_documento' => '12345678',
        'email' => 'carlos.mendoza@test.com',
        'estado' => 'activo',
    ]);

    $clienteDestino = Cliente::create([
        'nombres' => 'Ana',
        'apellidos' => 'Gutiérrez',
        'tipo_documento' => 'ci',
        'numero_documento' => '87654321',
        'email' => 'ana.gutierrez@test.com',
        'estado' => 'activo',
    ]);

    $cuentaOrigen = Cuenta::create([
        'numero_cuenta' => '1000000001',
        'tipo' => 'ahorro',
        'moneda' => 'BOB',
        'saldo' => 5000.00,
        'saldo_contable' => 5000.00,
        'limite_retiro_diario' => 10000.00,
        'estado' => 'activa',
        'cliente_id' => $clienteOrigen->id,
    ]);

    $cuentaDestino = Cuenta::create([
        'numero_cuenta' => '1000000002',
        'tipo' => 'ahorro',
        'moneda' => 'BOB',
        'saldo' => 1000.00,
        'saldo_contable' => 1000.00,
        'limite_retiro_diario' => 10000.00,
        'estado' => 'activa',
        'cliente_id' => $clienteDestino->id,
    ]);

    $response = $this->actingAs($cajero)->post('/transferencias', [
        'cuenta_origen' => $cuentaOrigen->numero_cuenta,
        'cuenta_destino' => $cuentaDestino->numero_cuenta,
        'monto' => 1500.00,
        'canal' => 'ventanilla',
        'metodo_autenticacion' => 'identidad',
        'motivo' => 'Pago de consultoría',
    ]);

    $response->assertRedirect();

    $cuentaOrigen->refresh();
    $cuentaDestino->refresh();

    expect((float) $cuentaOrigen->saldo)->toBe(3500.0)
        ->and((float) $cuentaDestino->saldo)->toBe(2500.0);

    $this->assertDatabaseHas('transferencias', [
        'cuenta_origen_id' => $cuentaOrigen->id,
        'cuenta_destino_id' => $cuentaDestino->id,
        'monto' => 1500.00,
        'estado' => 'completado',
    ]);

    $this->assertDatabaseHas('movimientos', [
        'cuenta_id' => $cuentaOrigen->id,
        'tipo' => 'transferencia_debito',
        'monto' => 1500.00,
    ]);

    $this->assertDatabaseHas('movimientos', [
        'cuenta_id' => $cuentaDestino->id,
        'tipo' => 'transferencia_credito',
        'monto' => 1500.00,
    ]);
});

test('la transferencia es rechazada si el saldo es insuficiente', function () {
    $cajero = User::factory()->create(['rol' => 'cajero']);

    $cliente = Cliente::create([
        'nombres' => 'Mario',
        'apellidos' => 'Bros',
        'tipo_documento' => 'ci',
        'numero_documento' => '5555555',
        'email' => 'mario@test.com',
        'estado' => 'activo',
    ]);

    $cuentaOrigen = Cuenta::create([
        'numero_cuenta' => '1000000003',
        'tipo' => 'ahorro',
        'moneda' => 'BOB',
        'saldo' => 200.00,
        'saldo_contable' => 200.00,
        'limite_retiro_diario' => 5000.00,
        'estado' => 'activa',
        'cliente_id' => $cliente->id,
    ]);

    $cuentaDestino = Cuenta::create([
        'numero_cuenta' => '1000000004',
        'tipo' => 'ahorro',
        'moneda' => 'BOB',
        'saldo' => 100.00,
        'saldo_contable' => 100.00,
        'limite_retiro_diario' => 5000.00,
        'estado' => 'activa',
        'cliente_id' => $cliente->id,
    ]);

    $response = $this->actingAs($cajero)->post('/transferencias', [
        'cuenta_origen' => $cuentaOrigen->numero_cuenta,
        'cuenta_destino' => $cuentaDestino->numero_cuenta,
        'monto' => 500.00,
        'canal' => 'ventanilla',
        'metodo_autenticacion' => 'identidad',
    ]);

    $response->assertStatus(422);
});
