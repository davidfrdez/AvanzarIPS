<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';

    protected $fillable = ['nombre', 'slug', 'descripcion'];

    public function roles()
    {
        // Un permiso pertenece a muchos roles
        return $this->belongsToMany(Rol::class, 'permiso_rol');
    }
    public function permisos()
    {
        // belongsToMany(ModeloRelacionado, tabla_pivote, foreign_key_local, foreign_key_relacionada)
        return $this->belongsToMany(Permiso::class, 'permiso_rol', 'rol_id', 'permiso_id');
    }
}
