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
        Schema::create('huellas_biometricas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('dedo', 50)->default('indice_derecho');
            $table->string('credential_id', 255)->nullable()->unique();
            $table->text('public_key')->nullable();
            $table->string('biometric_hash', 64);
            $table->unsignedTinyInteger('calidad')->default(95);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('huellas_biometricas');
    }
};
