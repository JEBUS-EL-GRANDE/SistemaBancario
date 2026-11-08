<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Banco extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'codigo',
        'sigla',
        'logo',
        'color_primario',
        'nit',
        'direccion',
        'telefono',
        'estado',
    ];

    /** @return HasMany<User, $this> */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<Cliente, $this> */
    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    /** @return HasMany<Cuenta, $this> */
    public function cuentas(): HasMany
    {
        return $this->hasMany(Cuenta::class);
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

    /** @return HasMany<Prestamo, $this> */
    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamo::class);
    }

    /** @return HasMany<Transferencia, $this> */
    public function transferencias(): HasMany
    {
        return $this->hasMany(Transferencia::class);
    }

    public function isActivo(): bool
    {
        return $this->estado === 'activo';
    }
}

