<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\Bitacora;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\CuotaAmortizacion;
use App\Models\Deposito;
use App\Models\Movimiento;
use App\Models\PagoPrestamo;
use App\Models\Prestamo;
use App\Models\Retiro;
use App\Models\Transferencia;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatosDemoSeeder extends Seeder
{
    /**
     * Puebla el sistema con mínimo 6 registros completos por módulo tanto para Banco Mercantil como Banco Unión.
     */
    public function run(): void
    {
        $bancoMercantil = Banco::where('codigo', 'BMSC')->first() ?? Banco::find(2);
        $bancoUnion = Banco::where('codigo', 'BUN')->first() ?? Banco::find(1);

        if (! $bancoMercantil || ! $bancoUnion) {
            $this->command->error('Primero debes ejecutar BancoSeeder.');
            return;
        }

        $this->poblarBanco($bancoMercantil, '2000');
        $this->poblarBanco($bancoUnion, '1000');

        $this->command->info('✅ Datos demo generados exitosamente (Mínimo 6 registros por módulo).');
    }

    private function poblarBanco(Banco $banco, string $prefijoCuenta): void
    {
        // Obtener o crear cajero del banco
        $cajero = User::where('banco_id', $banco->id)->where('rol', 'cajero')->first()
            ?? User::where('banco_id', $banco->id)->first()
            ?? User::first();

        $ejecutivo = User::where('banco_id', $banco->id)->where('rol', 'ejecutivo_credito')->first()
            ?? $cajero;

        // 1. Clientes (mínimo 6 por banco)
        $clientesData = [
            [
                'nombres' => 'Alejandro',
                'apellidos' => 'Montaño Ribera',
                'ci' => "5412890-{$banco->codigo}",
                'email' => "alejandro.m.{$banco->codigo}@bancosistema.bo",
                'telefono' => '71023456',
                'direccion' => 'Av. San Martín #450, Barrio Equipetrol',
                'nacimiento' => '1988-04-12',
            ],
            [
                'nombres' => 'Valeria Sofía',
                'apellidos' => 'Camacho Justiniano',
                'ci' => "6891234-{$banco->codigo}",
                'email' => "valeria.c.{$banco->codigo}@bancosistema.bo",
                'telefono' => '72198765',
                'direccion' => 'Calle René Moreno #180, Casco Viejo',
                'nacimiento' => '1994-09-23',
            ],
            [
                'nombres' => 'Marcelo Andrés',
                'apellidos' => 'Torrico Saucedo',
                'ci' => "4785210-{$banco->codigo}",
                'email' => "marcelo.t.{$banco->codigo}@bancosistema.bo",
                'telefono' => '73145678',
                'direccion' => 'Av. Monseñor Rivero #320',
                'nacimiento' => '1982-11-05',
            ],
            [
                'nombres' => 'Mariana Lucía',
                'apellidos' => 'Vaca Díez',
                'ci' => "7512984-{$banco->codigo}",
                'email' => "mariana.v.{$banco->codigo}@bancosistema.bo",
                'telefono' => '70011223',
                'direccion' => 'Barrio Las Palmas, Calle 4 #88',
                'nacimiento' => '1996-02-14',
            ],
            [
                'nombres' => 'Jorge Eduardo',
                'apellidos' => 'Paz Antelo',
                'ci' => "3987125-{$banco->codigo}",
                'email' => "jorge.p.{$banco->codigo}@bancosistema.bo",
                'telefono' => '75033445',
                'direccion' => 'Av. Santos Dumont #610',
                'nacimiento' => '1979-07-30',
            ],
            [
                'nombres' => 'Gabriela Elena',
                'apellidos' => 'Mendoza Cuéllar',
                'ci' => "8234910-{$banco->codigo}",
                'email' => "gabriela.m.{$banco->codigo}@bancosistema.bo",
                'telefono' => '78955667',
                'direccion' => 'Villa 1ro de Mayo, Calle 3 #15',
                'nacimiento' => '1998-12-08',
            ],
        ];

        $cuentasCreadas = [];

        foreach ($clientesData as $idx => $cd) {
            $cliente = Cliente::sinFiltroBanco()->updateOrCreate(
                ['numero_documento' => $cd['ci']],
                [
                    'banco_id' => $banco->id,
                    'nombres' => $cd['nombres'],
                    'apellidos' => $cd['apellidos'],
                    'tipo_documento' => 'ci',
                    'numero_documento' => $cd['ci'],
                    'email' => $cd['email'],
                    'telefono' => $cd['telefono'],
                    'direccion' => $cd['direccion'],
                    'fecha_nacimiento' => $cd['nacimiento'],
                    'estado' => 'activo',
                    'registrado_por' => $cajero->id,
                ]
            );

            // 2. Cuentas Bancarias (1 cuenta por cliente, saldos entre 8,000 y 65,000 Bs)
            $saldoInicial = [18500.00, 34200.00, 9500.00, 48000.00, 12800.00, 62000.00][$idx];
            $tipoCuenta = $idx % 2 === 0 ? 'ahorro' : 'corriente';
            $numCuenta = sprintf('%s-%04d-%04d', $prefijoCuenta, $banco->id, $idx + 10);

            $cuenta = Cuenta::sinFiltroBanco()->updateOrCreate(
                ['numero_cuenta' => $numCuenta],
                [
                    'banco_id' => $banco->id,
                    'numero_cuenta' => $numCuenta,
                    'tipo' => $tipoCuenta,
                    'moneda' => 'BOB',
                    'saldo' => $saldoInicial,
                    'saldo_contable' => $saldoInicial,
                    'limite_retiro_diario' => 5000.00,
                    'retiro_acumulado_hoy' => 0.00,
                    'estado' => 'activa',
                    'cliente_id' => $cliente->id,
                    'aperturada_por' => $cajero->id,
                    'fecha_apertura' => now()->subMonths(6 - $idx),
                ]
            );

            $cuentasCreadas[] = $cuenta;
        }

        // 3. Depósitos (mínimo 6 depósitos por banco)
        $depositosConfig = [
            ['monto' => 3500.00, 'tipo' => 'efectivo', 'obs' => 'Depósito en efectivo por ventanilla'],
            ['monto' => 7200.00, 'tipo' => 'cheque', 'obs' => 'Depósito con cheque Banco Central', 'banco_emisor' => 'Banco Central de Bolivia', 'cheque' => 'CHQ-98102'],
            ['monto' => 12000.00, 'tipo' => 'transferencia', 'obs' => 'Abono por pago de servicios profesionales'],
            ['monto' => 2500.00, 'tipo' => 'efectivo', 'obs' => 'Depósito para ahorro mensual'],
            ['monto' => 5000.00, 'tipo' => 'efectivo', 'obs' => 'Abono de capital en ventanilla'],
            ['monto' => 15000.00, 'tipo' => 'cheque', 'obs' => 'Depósito de cheque gerencial', 'banco_emisor' => 'Banco Mercantil', 'cheque' => 'CHQ-55410'],
        ];

        foreach ($depositosConfig as $k => $dc) {
            $cta = $cuentasCreadas[$k % count($cuentasCreadas)];
            $ref = sprintf('DEP-%s-%04d', $banco->codigo, 1000 + $k + ($banco->id * 50));

            $dep = Deposito::sinFiltroBanco()->updateOrCreate(
                ['referencia' => $ref],
                [
                    'banco_id' => $banco->id,
                    'referencia' => $ref,
                    'tipo' => $dc['tipo'],
                    'monto' => $dc['monto'],
                    'estado' => 'completado',
                    'banco_emisor' => $dc['banco_emisor'] ?? null,
                    'numero_cheque' => $dc['cheque'] ?? null,
                    'fecha_liberacion_cheque' => ($dc['tipo'] === 'cheque') ? now() : null,
                    'canal' => 'ventanilla',
                    'observacion' => $dc['obs'],
                    'cuenta_id' => $cta->id,
                    'cajero_id' => $cajero->id,
                    'created_at' => now()->subDays(15 - ($k * 2)),
                ]
            );

            // Registrar movimiento contable
            Movimiento::sinFiltroBanco()->updateOrCreate(
                ['referencia' => "MOV-{$ref}"],
                [
                    'banco_id' => $banco->id,
                    'referencia' => "MOV-{$ref}",
                    'tipo' => 'deposito',
                    'naturaleza' => 'credito',
                    'monto' => $dc['monto'],
                    'saldo_previo' => $cta->saldo - $dc['monto'],
                    'saldo_posterior' => $cta->saldo,
                    'canal' => 'ventanilla',
                    'descripcion' => "Depósito acreditado: {$dc['obs']}",
                    'transaccion_type' => Deposito::class,
                    'transaccion_id' => $dep->id,
                    'cuenta_id' => $cta->id,
                    'usuario_id' => $cajero->id,
                    'created_at' => $dep->created_at,
                ]
            );
        }

        // 4. Retiros (mínimo 6 retiros por banco)
        $retirosConfig = [
            ['monto' => 800.00, 'canal' => 'ventanilla', 'metodo' => 'biometria', 'obs' => 'Retiro autorizado con huella dactilar'],
            ['monto' => 1500.00, 'canal' => 'ventanilla', 'metodo' => 'biometria', 'obs' => 'Retiro de fondos para gastos personales'],
            ['monto' => 500.00, 'canal' => 'atm', 'metodo' => 'tarjeta_pin', 'obs' => 'Dispensación en cajero automático ATM'],
            ['monto' => 2000.00, 'canal' => 'ventanilla', 'metodo' => 'biometria', 'obs' => 'Retiro verificado por escáner dactilar WebAuthn'],
            ['monto' => 1200.00, 'canal' => 'ventanilla', 'metodo' => 'identidad', 'obs' => 'Retiro con documento de identidad y firma'],
            ['monto' => 3000.00, 'canal' => 'ventanilla', 'metodo' => 'biometria', 'obs' => 'Retiro programado con comprobante emitido'],
        ];

        foreach ($retirosConfig as $k => $rc) {
            $cta = $cuentasCreadas[($k + 1) % count($cuentasCreadas)];
            $ref = sprintf('RET-%s-%04d', $banco->codigo, 2000 + $k + ($banco->id * 50));

            $ret = Retiro::sinFiltroBanco()->updateOrCreate(
                ['referencia' => $ref],
                [
                    'banco_id' => $banco->id,
                    'referencia' => $ref,
                    'monto' => $rc['monto'],
                    'canal' => $rc['canal'],
                    'metodo_autenticacion' => $rc['metodo'],
                    'estado' => 'completado',
                    'saldo_previo' => $cta->saldo + $rc['monto'],
                    'saldo_posterior' => $cta->saldo,
                    'notificacion_enviada' => true,
                    'observacion' => $rc['obs'],
                    'cuenta_id' => $cta->id,
                    'cajero_id' => $cajero->id,
                    'created_at' => now()->subDays(12 - ($k * 2)),
                ]
            );

            Movimiento::sinFiltroBanco()->updateOrCreate(
                ['referencia' => "MOV-{$ref}"],
                [
                    'banco_id' => $banco->id,
                    'referencia' => "MOV-{$ref}",
                    'tipo' => 'retiro',
                    'naturaleza' => 'debito',
                    'monto' => $rc['monto'],
                    'saldo_previo' => $cta->saldo + $rc['monto'],
                    'saldo_posterior' => $cta->saldo,
                    'canal' => $rc['canal'],
                    'descripcion' => "Retiro debitado: {$rc['obs']}",
                    'transaccion_type' => Retiro::class,
                    'transaccion_id' => $ret->id,
                    'cuenta_id' => $cta->id,
                    'usuario_id' => $cajero->id,
                    'created_at' => $ret->created_at,
                ]
            );
        }

        // 5. Transferencias (mínimo 5 transferencias entre cuentas)
        $transferenciasConfig = [
            ['monto' => 1500.00, 'motivo' => 'Pago por compra de mercadería'],
            ['monto' => 3200.00, 'motivo' => 'Transferencia familiar mensual'],
            ['monto' => 750.00, 'motivo' => 'Reembolso de gastos de viaje'],
            ['monto' => 5000.00, 'motivo' => 'Abono de cuota de asociación'],
            ['monto' => 2100.00, 'motivo' => 'Pago de honorarios profesionales'],
        ];

        foreach ($transferenciasConfig as $k => $tc) {
            $origen = $cuentasCreadas[$k % count($cuentasCreadas)];
            $destino = $cuentasCreadas[($k + 1) % count($cuentasCreadas)];
            $ref = sprintf('TRF-%s-%04d', $banco->codigo, 3000 + $k + ($banco->id * 50));

            $trf = Transferencia::sinFiltroBanco()->updateOrCreate(
                ['referencia' => $ref],
                [
                    'banco_id' => $banco->id,
                    'referencia' => $ref,
                    'cuenta_origen_id' => $origen->id,
                    'cuenta_destino_id' => $destino->id,
                    'monto' => $tc['monto'],
                    'canal' => 'ventanilla',
                    'metodo_autenticacion' => 'identidad',
                    'motivo' => $tc['motivo'],
                    'estado' => 'completado',
                    'saldo_previo_origen' => $origen->saldo + $tc['monto'],
                    'saldo_posterior_origen' => $origen->saldo,
                    'saldo_previo_destino' => $destino->saldo - $tc['monto'],
                    'saldo_posterior_destino' => $destino->saldo,
                    'usuario_id' => $cajero->id,
                    'created_at' => now()->subDays(10 - ($k * 2)),
                ]
            );
        }

        // 6. Préstamos y Amortizaciones (mínimo 5 préstamos por banco)
        $prestamosConfig = [
            ['tipo' => 'personal', 'monto' => 25000.00, 'plazo' => 24, 'tasa' => 0.12, 'destino' => 'Remodelación de vivienda', 'estado' => 'desembolsado'],
            ['tipo' => 'comercial', 'monto' => 70000.00, 'plazo' => 36, 'tasa' => 0.095, 'destino' => 'Capital de trabajo para comercio', 'estado' => 'al_dia'],
            ['tipo' => 'personal', 'monto' => 15000.00, 'plazo' => 12, 'tasa' => 0.14, 'destino' => 'Consolidación de deudas personales', 'estado' => 'al_dia'],
            ['tipo' => 'hipotecario', 'monto' => 180000.00, 'plazo' => 120, 'tasa' => 0.075, 'destino' => 'Compra de departamento en condominio', 'estado' => 'desembolsado'],
            ['tipo' => 'personal', 'monto' => 30000.00, 'plazo' => 18, 'tasa' => 0.11, 'destino' => 'Adquisición de equipamiento médico', 'estado' => 'solicitado'],
        ];

        foreach ($prestamosConfig as $k => $pc) {
            $cta = $cuentasCreadas[$k % count($cuentasCreadas)];
            $cliente = $cta->cliente ?? Cliente::sinFiltroBanco()->find($cta->cliente_id);
            $cod = sprintf('CRE-%s-%04d', $banco->codigo, 4000 + $k + ($banco->id * 50));

            // Calcular cuota francesa: P * [ i / (1 - (1+i)^-n) ]
            $i = $pc['tasa'] / 12;
            $n = $pc['plazo'];
            $cuota = round($pc['monto'] * ($i / (1 - pow(1 + $i, -$n))), 2);

            $prestamo = Prestamo::sinFiltroBanco()->updateOrCreate(
                ['codigo' => $cod],
                [
                    'banco_id' => $banco->id,
                    'codigo' => $cod,
                    'tipo' => $pc['tipo'],
                    'monto_solicitado' => $pc['monto'],
                    'monto_aprobado' => $pc['monto'],
                    'tasa_interes' => $pc['tasa'],
                    'plazo_meses' => $pc['plazo'],
                    'destino_credito' => $pc['destino'],
                    'cuota_mensual' => $cuota,
                    'saldo_pendiente' => $pc['monto'] * 0.85,
                    'estado' => $pc['estado'],
                    'ingreso_mensual' => 8500.00,
                    'egreso_mensual' => 3200.00,
                    'capacidad_pago' => 5300.00,
                    'cliente_id' => $cliente->id,
                    'cuenta_desembolso_id' => $cta->id,
                    'ejecutivo_id' => $ejecutivo->id,
                    'fecha_solicitud' => now()->subMonths(4),
                    'fecha_aprobacion' => now()->subMonths(4),
                    'fecha_desembolso' => ($pc['estado'] !== 'solicitado') ? now()->subMonths(4) : null,
                ]
            );

            // Generar cuotas de amortización del préstamo si está desembolsado o al día
            if (in_array($pc['estado'], ['desembolsado', 'al_dia'])) {
                $saldoRestante = $pc['monto'];
                for ($numCuota = 1; $numCuota <= min(6, $pc['plazo']); $numCuota++) {
                    $interesCuota = round($saldoRestante * $i, 2);
                    $capitalCuota = round($cuota - $interesCuota, 2);
                    $saldoRestante = max(0, round($saldoRestante - $capitalCuota, 2));

                    $estadoCuota = ($numCuota <= 3) ? 'pagada' : 'pendiente';

                    $cuotaModel = CuotaAmortizacion::updateOrCreate(
                        ['prestamo_id' => $prestamo->id, 'numero_cuota' => $numCuota],
                        [
                            'cuota_total' => $cuota,
                            'capital' => $capitalCuota,
                            'interes' => $interesCuota,
                            'saldo_restante' => $saldoRestante,
                            'monto_pagado' => ($estadoCuota === 'pagada') ? $cuota : 0.00,
                            'fecha_vencimiento' => now()->subMonths(4)->addMonths($numCuota)->toDateString(),
                            'fecha_pago' => ($estadoCuota === 'pagada') ? now()->subMonths(4)->addMonths($numCuota)->subDays(2) : null,
                            'estado' => $estadoCuota,
                        ]
                    );

                    // Si está pagada, registrar el PagoPrestamo correspondiente (mínimo 5 pagos en total)
                    if ($estadoCuota === 'pagada') {
                        $refPago = sprintf('PAG-%s-%04d-%02d', $banco->codigo, $prestamo->id, $numCuota);
                        PagoPrestamo::updateOrCreate(
                            ['referencia' => $refPago],
                            [
                                'referencia' => $refPago,
                                'monto' => $cuota,
                                'tipo' => 'cuota_mensual',
                                'capital_abonado' => $capitalCuota,
                                'interes_abonado' => $interesCuota,
                                'saldo_pendiente_previo' => $saldoRestante + $capitalCuota,
                                'saldo_pendiente_posterior' => $saldoRestante,
                                'prestamo_id' => $prestamo->id,
                                'cuota_id' => $cuotaModel->id,
                                'cajero_id' => $cajero->id,
                                'created_at' => now()->subMonths(4)->addMonths($numCuota)->subDays(2),
                            ]
                        );
                    }
                }
            }
        }

        // 7. Bitácora de Auditoría Forense
        Bitacora::sinFiltroBanco()->create([
            'banco_id' => $banco->id,
            'tipo_evento' => 'autenticacion',
            'descripcion' => "Inicio de sesión exitoso en {$banco->nombre}",
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'user_id' => $cajero->id,
            'created_at' => now()->subHours(2),
        ]);
    }
}
