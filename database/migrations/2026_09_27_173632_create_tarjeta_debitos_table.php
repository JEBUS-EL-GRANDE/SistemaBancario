<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla tarjetas de débito vinculadas a cuentas.
     * RF-007
     */
    public function up(): void
    {
        Schema::create('tarjeta_debitos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_tarjeta', 16)->unique();
            $table->string('bin', 6)->default('453288');
            $table->string('pin_hash');
            $table->date('fecha_vencimiento');
            $table->string('cvv_hash', 255);
            $table->enum('estado', ['activa', 'bloqueada', 'cancelada'])->default('activa');
            $table->integer('intentos_fallidos')->default(0);
            $table->foreignId('cuenta_id')->constrained('cuentas')->cascadeOnDelete();
            $table->foreignId('emitida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjeta_debitos');
    }
};
