<?php

namespace App\Traits;

use App\Models\AuditoriaCambio;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        // Rastrear Creación
        static::created(function ($model) {
            self::registrarAuditoria('CREAR', $model);
        });

        // Rastrear Actualización
        static::updated(function ($model) {
            self::registrarAuditoria('EDITAR', $model);
        });

        // Rastrear Eliminación (si tienes SoftDeletes, esto atrapa el borrado)
        static::deleted(function ($model) {
            self::registrarAuditoria('ELIMINAR', $model);
        });
    }

    protected static function registrarAuditoria($accion, $model)
    {
        // Solo auditar si hay un usuario logueado haciéndolo (Sanctum/Auth)
        if (Auth::check()) {
            
            // Si es edición, capturar solo los campos "Sucios" (lo que cambió). 
            // Si es nuevo o borrado, capturar todo el objeto.
            $detalles = $accion === 'EDITAR' ? $model->getDirty() : $model->getAttributes();

            AuditoriaCambio::create([
                'usuario_id'   => Auth::id(),
                'accion'       => $accion,
                'nombre_tabla' => $model->getTable(),
                'registro_id'  => $model->id,
                'detalles'     => json_encode($detalles)
            ]);
        }
    }
}
