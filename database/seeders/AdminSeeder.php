<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Crea los usuarios base del sistema bancario:
     * - 1 Administrador
     * - 1 Cajero de prueba
     * - 1 Ejecutivo de crédito de prueba
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrador',
                'apellidos' => 'Sistema',
                'username' => 'admin',
                'email' => 'admin@bancosistema.bo',
                'password' => Hash::make('Admin@1234'),
                'rol' => 'administrador',
                'estado' => 'activo',
            ]
        );

        User::updateOrCreate(
            ['username' => 'cajero01'],
            [
                'name' => 'Juan',
                'apellidos' => 'Pérez Mamani',
                'username' => 'cajero01',
                'email' => 'cajero01@bancosistema.bo',
                'password' => Hash::make('Cajero@1234'),
                'rol' => 'cajero',
                'estado' => 'activo',
            ]
        );

        User::updateOrCreate(
            ['username' => 'ejecutivo01'],
            [
                'name' => 'María',
                'apellidos' => 'López Vega',
                'username' => 'ejecutivo01',
                'email' => 'ejecutivo01@bancosistema.bo',
                'password' => Hash::make('Ejecutivo@1234'),
                'rol' => 'ejecutivo_credito',
                'estado' => 'activo',
            ]
        );

        $this->command->info('✅ Usuarios base creados:');
        $this->command->table(
            ['Rol', 'Username', 'Contraseña'],
            [
                ['administrador', 'admin', 'Admin@1234'],
                ['cajero', 'cajero01', 'Cajero@1234'],
                ['ejecutivo_credito', 'ejecutivo01', 'Ejecutivo@1234'],
            ]
        );
    }
}
