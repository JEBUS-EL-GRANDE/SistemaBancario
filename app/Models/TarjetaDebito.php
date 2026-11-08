<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TarjetaDebito extends Model
{
    use HasFactory;

    protected $table = 'tarjeta_debitos';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'numero_tarjeta',
        'bin',
        'pin_hash',
        'fecha_vencimiento',
        'cvv_hash',
        'estado',
        'intentos_fallidos',
        'cuenta_id',
        'emitida_por',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'pin_hash',
        'cvv_hash',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'intentos_fallidos' => 'integer',
        ];
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }

    /** @return BelongsTo<User, $this> */
    public function emitidaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'emitida_por');
    }
}
