<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaCambio extends Model
{
    use HasFactory;

    // Le indicamos explícitamente el nombre de la tabla
    protected $table = 'auditoria_cambios';

    // Permitimos que estos campos se llenen desde el UserService
    protected $fillable = [
        'usuario_id',
        'accion',
        'nombre_tabla',
        'registro_id',
        'detalles',
    ];

    // Relación: Un log de auditoría pertenece a un usuario (el que hizo la acción)
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
