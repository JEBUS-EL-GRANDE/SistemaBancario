<?php

namespace App\Traits;

use App\Models\Banco;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToBanco
{
    /**
     * Inicializa el trait para aplicar el Global Scope y la asignación automática.
     */
    protected static function bootBelongsToBanco(): void
    {
        // 1. Filtrado automático de consultas por el Banco del usuario autenticado
        static::addGlobalScope('banco', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();

                // Si el usuario pertenece a un banco específico, se aísla la información
                if ($user->banco_id !== null) {
                    $table = $builder->getModel()->getTable();
                    $builder->where("{$table}.banco_id", $user->banco_id);
                }
            }
        });

        // 2. Asignación automática del banco_id al crear cualquier registro
        static::creating(function ($model) {
            if (empty($model->banco_id) && Auth::check()) {
                $user = Auth::user();
                if ($user->banco_id !== null) {
                    $model->banco_id = $user->banco_id;
                }
            }
        });
    }

    /**
     * Relación con la entidad Banco (Inquilino / Tenant).
     *
     * @return BelongsTo<Banco, $this>
     */
    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class, 'banco_id');
    }

    /**
     * Permite consultar registros ignorando el filtro de banco (para auditoría global o super-admin).
     */
    public static function sinFiltroBanco(): Builder
    {
        return static::withoutGlobalScope('banco');
    }
}

