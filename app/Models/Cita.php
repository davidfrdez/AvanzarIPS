<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'paciente_id',
        'medico_id',
        'especialidad_id',
        'programada_para',
    ];

    // Convertimos automáticamente el campo a un objeto de fecha (Carbon)
    protected $casts = [
        'programada_para' => 'datetime',
    ];

    // Relación: Una cita pertenece a un paciente
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    // Relación: Una cita es atendida por un médico (Usuario)
    public function medico()
    {
        return $this->belongsTo(User::class, 'medico_id');
    }
    // NUEVA RELACIÓN: Una cita tiene una especialidad específica
    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }
}
