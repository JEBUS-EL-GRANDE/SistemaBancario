<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla retiros: ventanilla y ATM con validaciones ACID.
     * RF-014 al RF-019
     */
    public function up(): void
    {
        Schema::create('retiros', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 30)->unique();
            $table->decimal('monto', 15, 2);
            $table->enum('canal', ['ventanilla', 'atm'])->default('ventanilla');
            $table->enum('metodo_autenticacion', ['identidad', 'tarjeta_pin', 'biometria', '2fa'])->default('identidad');
            $table->enum('estado', ['completado', 'rechazado', 'pendiente'])->default('completado');
            $table->string('motivo_rechazo')->nullable();
            $table->decimal('saldo_previo', 15, 2);
            $table->decimal('saldo_posterior', 15, 2);
            $table->boolean('notificacion_enviada')->default(false);
            $table->text('observacion')->nullable();
            $table->foreignId('cuenta_id')->constrained('cuentas');
            $table->foreignId('cajero_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tarjeta_id')->nullable()->constrained('tarjeta_debitos')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retiros');
    }
};
