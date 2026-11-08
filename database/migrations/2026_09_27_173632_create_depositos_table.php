<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla depósitos: efectivo, cheque y transferencias a terceros.
     * RF-009 al RF-013
     */
    public function up(): void
    {
        Schema::create('depositos', function (Blueprint $table) {
            $table->id();
            $table->string('referencia', 30)->unique();
            $table->enum('tipo', ['efectivo', 'cheque', 'transferencia']);
            $table->decimal('monto', 15, 2);
            $table->enum('estado', ['completado', 'en_reserva', 'cancelado'])->default('completado');
            // Datos del cheque (solo cuando tipo = 'cheque')
            $table->string('banco_emisor')->nullable();
            $table->string('numero_cheque', 50)->nullable();
            $table->date('fecha_liberacion_cheque')->nullable();
            // Canal de operación
            $table->enum('canal', ['ventanilla', 'atm', 'sistema'])->default('ventanilla');
            $table->text('observacion')->nullable();
            $table->foreignId('cuenta_id')->constrained('cuentas');
            $table->foreignId('cajero_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depositos');
    }
};
