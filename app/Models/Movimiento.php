<?php

namespace App\Models;

use App\Traits\BelongsToBanco;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Movimiento extends Model
{
    use BelongsToBanco, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'banco_id',
        'referencia',
        'tipo',
        'naturaleza',
        'monto',
        'saldo_previo',
        'saldo_posterior',
        'canal',
        'descripcion',
        'transaccion_type',
        'transaccion_id',
        'cuenta_id',
        'usuario_id',
    ];

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /** Relación polimórfica al origen (Deposito, Retiro, PagoPrestamo, etc.) */
    public function transaccion(): MorphTo
    {
        return $this->morphTo();
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
        ];
    }
}
