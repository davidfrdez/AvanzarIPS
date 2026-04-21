<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory;

    protected $table = 'pacientes';

    protected $fillable = [
        'cedula',
        'nombre',
        'eps',
    ];

    // Relación: Un paciente tiene muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }
}
