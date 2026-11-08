<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'banco_id',
        'name',
        'apellidos',
        'username',
        'email',
        'password',
        'rol',
        'estado',
        'telefono',
        'ultimo_acceso',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isAdministrador(): bool
    {
        return $this->rol === 'administrador';
    }

    public function isCajero(): bool
    {
        return $this->rol === 'cajero';
    }

    public function isEjecutivoCredito(): bool
    {
        return $this->rol === 'ejecutivo_credito';
    }

    public function isCliente(): bool
    {
        return $this->rol === 'cliente';
    }

    public function isActivo(): bool
    {
        return $this->estado === 'activo';
    }

    /** @return HasMany<Bitacora, $this> */
    public function bitacoras(): HasMany
    {
        return $this->hasMany(Bitacora::class);
    }

    /** @return HasMany<Deposito, $this> */
    public function depositosRealizados(): HasMany
    {
        return $this->hasMany(Deposito::class, 'cajero_id');
    }

    /** @return HasMany<Retiro, $this> */
    public function retirosRealizados(): HasMany
    {
        return $this->hasMany(Retiro::class, 'cajero_id');
    }

    /** @return HasMany<Cliente, $this> */
    public function clientesRegistrados(): HasMany
    {
        return $this->hasMany(Cliente::class, 'registrado_por');
    }

    /** @return BelongsTo<Cliente, $this> */
    public function perfilCliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'id', 'user_id');
    }

    /** @return BelongsTo<Banco, $this> */
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'ultimo_acceso' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
