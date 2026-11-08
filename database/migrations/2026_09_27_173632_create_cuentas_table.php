<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla cuentas bancarias: ahorro y corriente.
     * RF-006, RF-008
     */
    public function up(): void
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_cuenta', 20)->unique();
            $table->enum('tipo', ['ahorro', 'corriente']);
            $table->string('moneda', 3)->default('BOB');
            $table->decimal('saldo', 15, 2)->default(0.00);
            $table->decimal('saldo_contable', 15, 2)->default(0.00);
            $table->decimal('limite_retiro_diario', 15, 2)->default(5000.00);
            $table->decimal('retiro_acumulado_hoy', 15, 2)->default(0.00);
            $table->date('fecha_reset_retiro')->nullable();
            $table->enum('estado', ['activa', 'bloqueada', 'inactiva', 'cancelada'])->default('activa');
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('aperturada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas');
    }
};
