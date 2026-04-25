<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaCambio extends Model
{
    protected $table = 'auditoria_cambios';
    
    protected $fillable = [
        'usuario_id',
        'accion',
        'nombre_tabla',
        'registro_id',
        'detalles'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
