<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuotaAmortizacion extends Model
{
    use HasFactory;

    protected $table = 'cuota_amortizacions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'numero_cuota',
        'fecha_vencimiento',
        'cuota_total',
        'capital',
        'interes',
        'saldo_restante',
        'estado',
        'monto_pagado',
        'fecha_pago',
        'prestamo_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'numero_cuota' => 'integer',
            'fecha_vencimiento' => 'date',
            'fecha_pago' => 'date',
            'cuota_total' => 'decimal:2',
            'capital' => 'decimal:2',
            'interes' => 'decimal:2',
            'saldo_restante' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Prestamo, $this> */
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    /** @return HasMany<PagoPrestamo, $this> */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoPrestamo::class, 'cuota_id');
    }
}
