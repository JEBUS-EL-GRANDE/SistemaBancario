<?php

namespace App\Models;

use App\Traits\BelongsToBanco;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Retiro extends Model
{
    use BelongsToBanco, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'banco_id',
        'referencia',
        'monto',
        'canal',
        'metodo_autenticacion',
        'estado',
        'motivo_rechazo',
        'saldo_previo',
        'saldo_posterior',
        'notificacion_enviada',
        'observacion',
        'cuenta_id',
        'cajero_id',
        'tarjeta_id',
    ];

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<User, $this> */
    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    /** @return BelongsTo<TarjetaDebito, $this> */
    public function tarjeta(): BelongsTo
    {
        return $this->belongsTo(TarjetaDebito::class, 'tarjeta_id');
    }

    /** @return MorphMany<Movimiento, $this> */
    public function movimientos(): MorphMany
    {
        return $this->morphMany(Movimiento::class, 'transaccion');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'saldo_previo' => 'decimal:2',
            'saldo_posterior' => 'decimal:2',
            'notificacion_enviada' => 'boolean',
        ];
    }
}
