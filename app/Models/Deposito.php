<?php

namespace App\Models;

use App\Traits\BelongsToBanco;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Deposito extends Model
{
    use BelongsToBanco, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'banco_id',
        'referencia',
        'tipo',
        'monto',
        'estado',
        'banco_emisor',
        'numero_cheque',
        'fecha_liberacion_cheque',
        'canal',
        'observacion',
        'cuenta_id',
        'cajero_id',
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
            'fecha_liberacion_cheque' => 'date',
        ];
    }
}
