<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * AuditoriaCambio (Ley 2015 — append-only).
 *
 * Por exigencia legal de inmodificabilidad de la HCE, este modelo bloquea
 * cualquier intento de UPDATE o DELETE. Solo se puede insertar.
 */
class AuditoriaCambio extends Model
{
    protected $table = 'auditoria_cambios';

    protected $fillable = [
        'usuario_id',
        'accion',
        'nombre_tabla',
        'registro_id',
        'detalles',
        'ip',
        'user_agent',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new RuntimeException(
                'Ley 2015: los registros de auditoría son inmodificables y no pueden actualizarse.'
            );
        });

        static::deleting(function (): void {
            throw new RuntimeException(
                'Ley 2015: los registros de auditoría son inmodificables y no pueden eliminarse.'
            );
        });
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
