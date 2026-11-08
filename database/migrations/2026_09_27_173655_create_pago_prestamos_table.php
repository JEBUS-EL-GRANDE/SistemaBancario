<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla pagos de préstamos: normales y anticipados.
     * RF-025
     */
    public function up(): void
    {
        Schema::create('pago_prestamos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 30)->unique();
            $table->decimal('monto', 15, 2);
            $table->enum('tipo', ['cuota_mensual', 'anticipado', 'cancelacion_total'])->default('cuota_mensual');
            $table->decimal('capital_abonado', 15, 2)->default(0.00);
            $table->decimal('interes_abonado', 15, 2)->default(0.00);
            $table->decimal('saldo_pendiente_previo', 15, 2);
            $table->decimal('saldo_pendiente_posterior', 15, 2);
            $table->foreignId('prestamo_id')->constrained('prestamos');
            $table->foreignId('cuota_id')->nullable()->constrained('cuota_amortizacions')->nullOnDelete();
            $table->foreignId('cajero_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_prestamos');
    }
};
