<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla movimientos: historial inmutable de todas las transacciones.
     * RF-027
     */
    public function up(): void
    {
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 30)->unique();
            $table->enum('tipo', ['deposito', 'retiro', 'pago_prestamo', 'desembolso', 'ajuste']);
            $table->enum('naturaleza', ['credito', 'debito']);
            $table->decimal('monto', 15, 2);
            $table->decimal('saldo_previo', 15, 2);
            $table->decimal('saldo_posterior', 15, 2);
            $table->enum('canal', ['ventanilla', 'atm', 'sistema'])->default('ventanilla');
            $table->string('descripcion')->nullable();
            // Referencia polimórfica al origen de la transacción
            $table->nullableMorphs('transaccion');
            $table->foreignId('cuenta_id')->constrained('cuentas');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
