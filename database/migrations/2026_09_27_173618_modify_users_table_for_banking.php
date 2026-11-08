<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extiende la tabla users de Laravel con campos bancarios:
     * rol RBAC, estado, apellidos y username.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellidos')->after('name');
            $table->string('username')->unique()->after('apellidos');
            $table->enum('rol', ['administrador', 'cajero', 'ejecutivo_credito', 'cliente'])
                ->default('cajero')
                ->after('username');
            $table->enum('estado', ['activo', 'inactivo', 'bloqueado'])
                ->default('activo')
                ->after('rol');
            $table->string('telefono', 20)->nullable()->after('estado');
            $table->timestamp('ultimo_acceso')->nullable()->after('telefono');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['apellidos', 'username', 'rol', 'estado', 'telefono', 'ultimo_acceso']);
        });
    }
};
