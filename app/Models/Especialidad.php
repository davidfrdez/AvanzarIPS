<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Especialidad extends Model
{
    use HasFactory;

    // Indicamos explícitamente el nombre de la tabla
    protected $table = 'especialidades';

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'nombre',
    ];

    /**
     * RELACIONES ELOQUENT
     */

    // Relación: Una especialidad tiene muchos médicos (Usuarios)
    public function medicos()
    {
        return $this->hasMany(User::class, 'especialidad_id');
    }

    // Relación: Una especialidad tiene muchas citas programadas
    public function citas()
    {
        return $this->hasMany(Cita::class, 'especialidad_id');
    }

    // Relación: Una especialidad tiene muchas terapias realizadas (de tu esquema inicial)
    // public function terapias()
    // {
    //     return $this->hasMany(Terapia::class, 'especialidad_id');
    // }
}
