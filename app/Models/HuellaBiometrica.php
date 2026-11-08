<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HuellaBiometrica extends Model
{
    use HasFactory;

    protected $table = 'huellas_biometricas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'cliente_id',
        'dedo',
        'credential_id',
        'public_key',
        'biometric_hash',
        'calidad',
        'estado',
        'registrado_por',
    ];

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** @return BelongsTo<User, $this> */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /**
     * Nombre legible del dedo enrolado.
     */
    public function getNombreDedoAttribute(): string
    {
        return match ($this->dedo) {
            'indice_derecho' => 'Índice Derecho',
            'pulgar_derecho' => 'Pulgar Derecho',
            'medio_derecho' => 'Medio Derecho',
            'indice_izquierdo' => 'Índice Izquierdo',
            'pulgar_izquierdo' => 'Pulgar Izquierdo',
            'medio_izquierdo' => 'Medio Izquierdo',
            default => ucfirst(str_replace('_', ' ', $this->dedo)),
        };
    }
}
