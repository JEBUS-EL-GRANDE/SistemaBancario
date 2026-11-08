<?php

namespace App\Models;

use App\Traits\BelongsToBanco;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cuenta extends Model
{
    use BelongsToBanco, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'banco_id',
        'numero_cuenta',
        'tipo',
        'moneda',
        'saldo',
        'saldo_contable',
        'limite_retiro_diario',
        'retiro_acumulado_hoy',
        'fecha_reset_retiro',
        'estado',
        'cliente_id',
        'aperturada_por',
        'fecha_apertura',
    ];

    public function isActiva(): bool
    {
        return $this->estado === 'activa';
    }

    /** Verifica si el retiro acumulado debe resetearse al día de hoy */
    public function resetearRetiroDiarioSiNecesario(): void
    {
        if ($this->fecha_reset_retiro?->toDateString() !== now()->toDateString()) {
            $this->retiro_acumulado_hoy = 0;
            $this->fecha_reset_retiro = now()->toDateString();
            $this->save();
        }
    }

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** @return BelongsTo<User, $this> */
    public function aperturadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aperturada_por');
    }

    /** @return HasOne<TarjetaDebito, $this> */
    public function tarjeta(): HasOne
    {
        return $this->hasOne(TarjetaDebito::class);
    }

    /** @return HasMany<Deposito, $this> */
    public function depositos(): HasMany
    {
        return $this->hasMany(Deposito::class);
    }

    /** @return HasMany<Retiro, $this> */
    public function retiros(): HasMany
    {
        return $this->hasMany(Retiro::class);
    }

    /** @return HasMany<Movimiento, $this> */
    public function movimientos(): HasMany
    {
        return $this->hasMany(Movimiento::class)->latest();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'saldo' => 'decimal:2',
            'saldo_contable' => 'decimal:2',
            'limite_retiro_diario' => 'decimal:2',
            'retiro_acumulado_hoy' => 'decimal:2',
            'fecha_reset_retiro' => 'date',
            'fecha_apertura' => 'datetime',
        ];
    }
}
