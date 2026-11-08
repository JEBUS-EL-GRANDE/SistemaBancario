<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PagoPrestamo extends Model
{
    use HasFactory;

    protected $table = 'pago_prestamos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'referencia',
        'monto',
        'tipo',
        'capital_abonado',
        'interes_abonado',
        'saldo_pendiente_previo',
        'saldo_pendiente_posterior',
        'prestamo_id',
        'cuota_id',
        'cajero_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'capital_abonado' => 'decimal:2',
            'interes_abonado' => 'decimal:2',
            'saldo_pendiente_previo' => 'decimal:2',
            'saldo_pendiente_posterior' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Prestamo, $this> */
    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(Prestamo::class);
    }

    /** @return BelongsTo<CuotaAmortizacion, $this> */
    public function cuota(): BelongsTo
    {
        return $this->belongsTo(CuotaAmortizacion::class, 'cuota_id');
    }

    /** @return BelongsTo<User, $this> */
    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    /** @return MorphMany<Movimiento, $this> */
    public function movimientos(): MorphMany
    {
        return $this->morphMany(Movimiento::class, 'transaccion');
    }
}
