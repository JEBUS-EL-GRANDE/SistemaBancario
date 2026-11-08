<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla cuotas de amortización francesa (cuota fija).
     * RF-023
     */
    public function up(): void
    {
        Schema::create('cuota_amortizacions', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_cuota');
            $table->date('fecha_vencimiento');
            $table->decimal('cuota_total', 15, 2);
            $table->decimal('capital', 15, 2);
            $table->decimal('interes', 15, 2);
            $table->decimal('saldo_restante', 15, 2);
            $table->enum('estado', ['pendiente', 'pagada', 'vencida', 'pagada_parcial'])->default('pendiente');
            $table->decimal('monto_pagado', 15, 2)->default(0.00);
            $table->date('fecha_pago')->nullable();
            $table->foreignId('prestamo_id')->constrained('prestamos')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuota_amortizacions');
    }
};
