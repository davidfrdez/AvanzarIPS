<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\AccionAuditoria;
use App\Models\AuditoriaCambio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Trait Auditable — registra cambios en `auditoria_cambios` para cumplir Ley 2015.
 *
 * Registra eventos created/updated/deleted/restored del modelo. Captura IP
 * y User-Agent del request actual, además del usuario autenticado.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model): void {
            self::registrarAuditoria(AccionAuditoria::CREAR, $model);
        });

        static::updated(function (Model $model): void {
            self::registrarAuditoria(AccionAuditoria::EDITAR, $model);
        });

        static::deleted(function (Model $model): void {
            self::registrarAuditoria(AccionAuditoria::ELIMINAR, $model);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model): void {
                self::registrarAuditoria(AccionAuditoria::RESTAURAR, $model);
            });
        }
    }

    protected static function registrarAuditoria(AccionAuditoria $accion, Model $model): void
    {
        $detalles = $accion === AccionAuditoria::EDITAR
            ? $model->getDirty()
            : $model->getAttributes();

        // No registrar si no hay nada en el dirty array (false-positive de updated())
        if ($accion === AccionAuditoria::EDITAR && empty($detalles)) {
            return;
        }

        $request = request();

        AuditoriaCambio::create([
            'usuario_id'   => Auth::id(),
            'accion'       => $accion->value,
            'nombre_tabla' => $model->getTable(),
            'registro_id'  => $model->getKey(),
            'detalles'     => json_encode($detalles, JSON_UNESCAPED_UNICODE),
            'ip'           => $request?->ip(),
            'user_agent'   => substr((string) ($request?->userAgent() ?? ''), 0, 500),
        ]);
    }
}
