<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta todos los seeders del sistema bancario.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            BancoSeeder::class,
        ]);
    }
}
