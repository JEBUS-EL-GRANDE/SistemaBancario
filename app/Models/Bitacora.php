<?php

namespace App\Models;

use App\Traits\BelongsToBanco;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    use BelongsToBanco, HasFactory;

    /** Bitácora es inmutable: solo tiene created_at */
    public $timestamps = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'banco_id',
        'tipo_evento',
        'descripcion',
        'datos_adicionales',
        'ip_address',
        'user_agent',
        'user_id',
        'created_at',
    ];

    /** Registrar un evento en la bitácora de forma estática */
    public static function registrar(
        string $tipoEvento = 'otro',
        string $descripcion = '',
        ?int $userId = null,
        ?array $datosAdicionales = null,
        ?string $accion = null,
        ?string $detalles = null,
        ?string $tablaAfectada = null,
        ?int $registroId = null
    ): self {
        $evento = $accion ?? $tipoEvento;
        $eventosPermitidos = [
            'autenticacion', 'cierre_sesion', 'bloqueo_usuario',
            'deposito', 'retiro', 'prestamo', 'pago_prestamo',
            'apertura_cuenta', 'cambio_estado_cuenta', 'registro_cliente',
            'modificacion_usuario', 'generacion_reporte', 'otro',
        ];
        $slug = strtolower(str_replace(' ', '_', $evento));
        $tipoFinal = in_array($slug, $eventosPermitidos) ? $slug : 'otro';

        $desc = $detalles ?? $descripcion;
        $meta = $datosAdicionales ?? [];
        if ($tablaAfectada) {
            $meta['tabla'] = $tablaAfectada;
        }
        if ($registroId) {
            $meta['registro_id'] = $registroId;
        }
        if ($evento !== $tipoFinal) {
            $meta['evento_original'] = $evento;
        }

        return self::create([
            'tipo_evento' => $tipoFinal,
            'descripcion' => $desc ?: $evento,
            'user_id' => $userId ?? auth()->id(),
            'datos_adicionales' => ! empty($meta) ? $meta : null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'datos_adicionales' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
