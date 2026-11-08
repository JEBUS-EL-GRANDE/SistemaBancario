<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\Cliente;
use App\Models\Cuenta;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BancoSeeder extends Seeder
{
    /**
     * Registra los bancos (inquilinos) e inicializa los datos de demostración multi-inquilino.
     */
    public function run(): void
    {
        // 1. Crear los 3 Bancos principales
        $bancoUnion = Banco::updateOrCreate(
            ['codigo' => 'BUN'],
            [
                'nombre' => 'Banco Unión',
                'sigla' => 'Banco Unión S.A.',
                'color_primario' => '#047857', // Verde esmeralda
                'nit' => '1020304050',
                'direccion' => 'Av. Camacho #1234, La Paz - Bolivia',
                'telefono' => '800-10-2872',
                'estado' => 'activo',
            ]
        );

        $bancoMercantil = Banco::updateOrCreate(
            ['codigo' => 'BMSC'],
            [
                'nombre' => 'Banco Mercantil Santa Cruz',
                'sigla' => 'BMSC',
                'color_primario' => '#1E3A8A', // Azul corporativo
                'nit' => '1020304061',
                'direccion' => 'Calle 21 de Calacoto #456, Santa Cruz - Bolivia',
                'telefono' => '800-10-5000',
                'estado' => 'activo',
            ]
        );

        $bancoBisa = Banco::updateOrCreate(
            ['codigo' => 'BISA'],
            [
                'nombre' => 'Banco BISA',
                'sigla' => 'BISA S.A.',
                'color_primario' => '#D97706', // Ámbar/Dorado
                'nit' => '1020304072',
                'direccion' => 'Av. 16 de Julio #789, Cochabamba - Bolivia',
                'telefono' => '800-10-4472',
                'estado' => 'activo',
            ]
        );

        // 2. Asociar todos los datos preexistentes a Banco Unión (ID 1)
        $tablas = [
            'users',
            'clientes',
            'cuentas',
            'depositos',
            'retiros',
            'prestamos',
            'transferencias',
            'movimientos',
            'bitacoras',
        ];

        foreach ($tablas as $tabla) {
            DB::table($tabla)->whereNull('banco_id')->update(['banco_id' => $bancoUnion->id]);
        }

        // 3. Crear Usuarios específicos para el segundo inquilino: Banco Mercantil Santa Cruz (BMSC)
        $adminBmsc = User::updateOrCreate(
            ['username' => 'admin_bmsc'],
            [
                'banco_id' => $bancoMercantil->id,
                'name' => 'Roberto',
                'apellidos' => 'Suárez Montero',
                'username' => 'admin_bmsc',
                'email' => 'admin_bmsc@bancosistema.bo',
                'password' => Hash::make('Admin@1234'),
                'rol' => 'administrador',
                'estado' => 'activo',
            ]
        );

        $cajeroBmsc = User::updateOrCreate(
            ['username' => 'cajero_bmsc'],
            [
                'banco_id' => $bancoMercantil->id,
                'name' => 'Carla',
                'apellidos' => 'Roca Aguilera',
                'username' => 'cajero_bmsc',
                'email' => 'cajero_bmsc@bancosistema.bo',
                'password' => Hash::make('Cajero@1234'),
                'rol' => 'cajero',
                'estado' => 'activo',
            ]
        );

        // 4. Crear un Cliente y Cuenta de demostración para el Banco Mercantil Santa Cruz
        $clienteBmsc = Cliente::sinFiltroBanco()->updateOrCreate(
            ['numero_documento' => '7894561-SC'],
            [
                'banco_id' => $bancoMercantil->id,
                'nombres' => 'Carlos Fernando',
                'apellidos' => 'Gutiérrez Mercado',
                'tipo_documento' => 'ci',
                'numero_documento' => '7894561-SC',
                'email' => 'carlos.gutierrez@email.com',
                'telefono' => '78541236',
                'direccion' => 'Barrio Equipetrol, Calle 5 #120, Santa Cruz',
                'fecha_nacimiento' => '1992-05-18',
                'estado' => 'activo',
                'registrado_por' => $cajeroBmsc->id,
            ]
        );

        Cuenta::sinFiltroBanco()->updateOrCreate(
            ['numero_cuenta' => '2000-0001-0001'],
            [
                'banco_id' => $bancoMercantil->id,
                'numero_cuenta' => '2000-0001-0001',
                'tipo' => 'ahorro',
                'moneda' => 'BOB',
                'saldo' => 15000.00,
                'saldo_contable' => 15000.00,
                'limite_retiro_diario' => 5000.00,
                'retiro_acumulado_hoy' => 0.00,
                'estado' => 'activa',
                'cliente_id' => $clienteBmsc->id,
                'aperturada_por' => $cajeroBmsc->id,
                'fecha_apertura' => now(),
            ]
        );

        $this->command->info('✅ Multi-inquilino configurado: Bancos creados y datos asociados.');
    }
}
