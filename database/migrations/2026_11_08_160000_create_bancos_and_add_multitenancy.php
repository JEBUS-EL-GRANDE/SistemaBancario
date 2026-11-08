<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabla de Bancos (Inquilinos / Tenants)
        Schema::create('bancos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 10)->unique(); // BUN, BMSC, BISA
            $table->string('sigla', 20)->nullable();
            $table->string('logo')->nullable();
            $table->string('color_primario', 20)->default('#1E40AF');
            $table->string('nit', 30)->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
        });

        // 2. Asociar banco_id a usuarios
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });

        // 3. Asociar banco_id a clientes
        Schema::table('clientes', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });

        // 4. Asociar banco_id a cuentas
        Schema::table('cuentas', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });

        // 5. Asociar banco_id a transacciones y préstamos
        Schema::table('depositos', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });

        Schema::table('retiros', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });

        Schema::table('prestamos', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });

        Schema::table('transferencias', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });

        Schema::table('movimientos', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });

        Schema::table('bitacoras', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('id')->constrained('bancos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'bitacoras',
            'movimientos',
            'transferencias',
            'prestamos',
            'retiros',
            'depositos',
            'cuentas',
            'clientes',
            'users',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['banco_id']);
                $table->dropColumn('banco_id');
            });
        }

        Schema::dropIfExists('bancos');
    }
};

