<?php

use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\HuellaBiometrica;
use App\Models\User;

test('se puede enrolar una huella dactilar para un cliente', function () {
    $cajero = User::factory()->create(['rol' => 'cajero']);

    $cliente = Cliente::create([
        'nombres' => 'Roberto',
        'apellidos' => 'Suárez',
        'tipo_documento' => 'ci',
        'numero_documento' => '99887766',
        'email' => 'roberto.suarez@test.com',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($cajero)->postJson('/api/biometria/enrolar', [
        'cliente_id' => $cliente->id,
        'dedo' => 'indice_derecho',
        'calidad' => 98,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('huellas_biometricas', [
        'cliente_id' => $cliente->id,
        'dedo' => 'indice_derecho',
        'estado' => 'activo',
        'calidad' => 98,
    ]);
});

test('se puede verificar la huella dactilar de un cliente enrolado', function () {
    $cajero = User::factory()->create(['rol' => 'cajero']);

    $cliente = Cliente::create([
        'nombres' => 'Elena',
        'apellidos' => 'Torres',
        'tipo_documento' => 'ci',
        'numero_documento' => '44332211',
        'email' => 'elena.torres@test.com',
        'estado' => 'activo',
    ]);

    HuellaBiometrica::create([
        'cliente_id' => $cliente->id,
        'dedo' => 'pulgar_derecho',
        'credential_id' => 'cred_test_123',
        'biometric_hash' => hash('sha256', 'sample_minutiae'),
        'calidad' => 99,
        'estado' => 'activo',
        'registrado_por' => $cajero->id,
    ]);

    $response = $this->actingAs($cajero)->postJson('/api/biometria/verificar', [
        'cliente_id' => $cliente->id,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'dedo_validado' => 'Pulgar Derecho',
        ])
        ->assertJsonStructure([
            'token',
            'match_score',
        ]);
});

test('verificación biométrica falla si el cliente no tiene huellas registradas', function () {
    $cajero = User::factory()->create(['rol' => 'cajero']);

    $cliente = Cliente::create([
        'nombres' => 'Sin',
        'apellidos' => 'Huella',
        'tipo_documento' => 'ci',
        'numero_documento' => '11223344',
        'email' => 'sin.huella@test.com',
        'estado' => 'activo',
    ]);

    $response = $this->actingAs($cajero)->postJson('/api/biometria/verificar', [
        'cliente_id' => $cliente->id,
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'success' => false,
        ]);
});

test('un retiro con método biométrico requiere huella activa y token', function () {
    $cajero = User::factory()->create(['rol' => 'cajero']);

    $cliente = Cliente::create([
        'nombres' => 'Valeria',
        'apellidos' => 'Rios',
        'tipo_documento' => 'ci',
        'numero_documento' => '77665544',
        'email' => 'valeria.rios@test.com',
        'estado' => 'activo',
    ]);

    $cuenta = Cuenta::create([
        'numero_cuenta' => '1000000009',
        'tipo' => 'ahorro',
        'moneda' => 'BOB',
        'saldo' => 4000.00,
        'saldo_contable' => 4000.00,
        'limite_retiro_diario' => 10000.00,
        'estado' => 'activa',
        'cliente_id' => $cliente->id,
    ]);

    // Intento 1: Sin huella enrolada debe fallar
    $resFail = $this->actingAs($cajero)->post('/retiros', [
        'numero_cuenta' => $cuenta->numero_cuenta,
        'monto' => 500,
        'canal' => 'ventanilla',
        'metodo_autenticacion' => 'biometria',
        'biometric_token' => 'BIO-AUTH-SAMPLE',
    ]);
    $resFail->assertStatus(422);

    // Enrolar huella
    HuellaBiometrica::create([
        'cliente_id' => $cliente->id,
        'dedo' => 'indice_derecho',
        'credential_id' => 'cred_valeria_1',
        'biometric_hash' => hash('sha256', 'valeria_fingerprint'),
        'calidad' => 98,
        'estado' => 'activo',
    ]);

    // Intento 2: Con huella y token debe tener éxito
    $resSuccess = $this->actingAs($cajero)->post('/retiros', [
        'numero_cuenta' => $cuenta->numero_cuenta,
        'monto' => 500,
        'canal' => 'ventanilla',
        'metodo_autenticacion' => 'biometria',
        'biometric_token' => 'BIO-AUTH-1234567890',
    ]);

    $resSuccess->assertRedirect();
    $cuenta->refresh();
    expect((float) $cuenta->saldo)->toBe(3500.0);
});
