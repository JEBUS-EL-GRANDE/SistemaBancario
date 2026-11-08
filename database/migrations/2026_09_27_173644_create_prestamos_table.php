<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla préstamos: personales, hipotecarios y comerciales.
     * RF-020 al RF-025
     */
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->enum('tipo', ['personal', 'hipotecario', 'comercial']);
            $table->decimal('monto_solicitado', 15, 2);
            $table->decimal('monto_aprobado', 15, 2)->nullable();
            $table->decimal('tasa_interes', 6, 4);
            $table->integer('plazo_meses');
            $table->string('destino_credito')->nullable();
            $table->decimal('cuota_mensual', 15, 2)->nullable();
            $table->decimal('saldo_pendiente', 15, 2)->default(0.00);
            $table->enum('estado', [
                'solicitado',
                'en_evaluacion',
                'aprobado',
                'rechazado',
                'desembolsado',
                'al_dia',
                'moroso',
                'cancelado',
            ])->default('solicitado');
            $table->text('motivo_rechazo')->nullable();
            // Evaluación crediticia
            $table->decimal('ingreso_mensual', 15, 2)->nullable();
            $table->decimal('egreso_mensual', 15, 2)->nullable();
            $table->decimal('capacidad_pago', 15, 2)->nullable();
            // Relaciones
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('cuenta_desembolso_id')->nullable()->constrained('cuentas')->nullOnDelete();
            $table->foreignId('ejecutivo_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fecha_solicitud')->useCurrent();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamp('fecha_desembolso')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
