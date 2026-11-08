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
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 30)->unique();
            $table->foreignId('cuenta_origen_id')->constrained('cuentas');
            $table->foreignId('cuenta_destino_id')->constrained('cuentas');
            $table->decimal('monto', 15, 2);
            $table->enum('canal', ['ventanilla', 'banca_web', 'atm'])->default('ventanilla');
            $table->enum('metodo_autenticacion', ['identidad', 'tarjeta_pin', 'biometria', '2fa'])->default('identidad');
            $table->string('motivo', 255)->nullable();
            $table->enum('estado', ['completado', 'fallido', 'revertido'])->default('completado');
            $table->decimal('saldo_previo_origen', 15, 2);
            $table->decimal('saldo_posterior_origen', 15, 2);
            $table->decimal('saldo_previo_destino', 15, 2);
            $table->decimal('saldo_posterior_destino', 15, 2);
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};
