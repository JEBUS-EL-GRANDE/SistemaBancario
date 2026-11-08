<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ClienteUsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $cliente = Cliente::whereRaw('LOWER(email) = ?', ['benja@gmail.com'])->first();
        if ($cliente) {
            $user = User::firstOrCreate(
                ['email' => strtolower($cliente->email)],
                [
                    'name' => $cliente->nombres,
                    'apellidos' => $cliente->apellidos,
                    'username' => 'benjo.yu',
                    'password' => Hash::make('password123'),
                    'rol' => 'cliente',
                    'estado' => 'activo',
                ]
            );

            $cliente->update(['user_id' => $user->id]);
            echo "Usuario creado para {$cliente->email} con password: password123\n";
        }
    }
}
