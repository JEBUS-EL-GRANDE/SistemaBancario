<?php

namespace App\Models;

use App\Traits\BelongsToBanco;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prestamo extends Model
{
    use BelongsToBanco, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'banco_id',
        'codigo',
        'tipo',
        'monto_solicitado',
        'monto_aprobado',
        'tasa_interes',
        'plazo_meses',
        'destino_credito',
        'cuota_mensual',
        'saldo_pendiente',
        'estado',
        'motivo_rechazo',
        'ingreso_mensual',
        'egreso_mensual',
        'capacidad_pago',
        'cliente_id',
        'cuenta_desembolso_id',
        'ejecutivo_id',
        'fecha_solicitud',
        'fecha_aprobacion',
        'fecha_desembolso',
    ];

    /** @return BelongsTo<Cliente, $this> */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /** @return BelongsTo<Cuenta, $this> */
    public function cuentaDesembolso(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class, 'cuenta_desembolso_id');
    }

    /** @return BelongsTo<User, $this> */
    public function ejecutivo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ejecutivo_id');
    }

    /** @return HasMany<CuotaAmortizacion, $this> */
    public function cuotas(): HasMany
    {
        return $this->hasMany(CuotaAmortizacion::class)->orderBy('numero_cuota');
    }

    /** @return HasMany<PagoPrestamo, $this> */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoPrestamo::class)->latest();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monto_solicitado' => 'decimal:2',
            'monto_aprobado' => 'decimal:2',
            'tasa_interes' => 'decimal:4',
            'cuota_mensual' => 'decimal:2',
            'saldo_pendiente' => 'decimal:2',
            'ingreso_mensual' => 'decimal:2',
            'egreso_mensual' => 'decimal:2',
            'capacidad_pago' => 'decimal:2',
            'fecha_solicitud' => 'datetime',
            'fecha_aprobacion' => 'datetime',
            'fecha_desembolso' => 'datetime',
        ];
    }
}
