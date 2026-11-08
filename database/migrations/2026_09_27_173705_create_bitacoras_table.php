<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bitácora de auditoría inmutable: todos los eventos del sistema.
     * RF-027, RNF-010, RNF-013
     */
    public function up(): void
    {
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_evento', [
                'autenticacion',
                'cierre_sesion',
                'bloqueo_usuario',
                'deposito',
                'retiro',
                'prestamo',
                'pago_prestamo',
                'apertura_cuenta',
                'cambio_estado_cuenta',
                'registro_cliente',
                'modificacion_usuario',
                'generacion_reporte',
                'otro',
            ]);
            $table->string('descripcion');
            $table->json('datos_adicionales')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            // No se permiten updates ni deletes en esta tabla (auditoría inmutable)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacoras');
    }
};
