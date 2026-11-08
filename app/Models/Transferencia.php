<?php

namespace App\Models;

use App\Traits\BelongsToBanco;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Transferencia extends Model
{
    use BelongsToBanco, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'banco_id',
        'referencia',
        'cuenta_origen_id',
        'cuenta_destino_id',
        'monto',
        'canal',
        'metodo_autenticacion',
        'motivo',
        'estado',
        'saldo_previo_origen',
        'saldo_posterior_origen',
        'saldo_previo_destino',
        'saldo_posterior_destino',
        'usuario_id',
    ];

    /** @return BelongsTo<Cuenta, $this> */
    public function cuentaOrigen(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_origen_id');
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuentaDestino(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_destino_id');
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
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
            'saldo_previo_origen' => 'decimal:2',
            'saldo_posterior_origen' => 'decimal:2',
            'saldo_previo_destino' => 'decimal:2',
            'saldo_posterior_destino' => 'decimal:2',
        ];
    }
}
