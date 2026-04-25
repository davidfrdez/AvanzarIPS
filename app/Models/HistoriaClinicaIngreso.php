<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class HistoriaClinicaIngreso extends Model
{
    protected $table = 'historias_clinicas_ingreso';
    protected $fillable = [
        'paciente_id', 'medico_id', 'motivo_consulta', 'enfermedad_actual', 'anamnesis',
        'ant_personales', 'ant_familiares', 'ant_quirurgicos', 'ant_patologicos',
        'ant_farmacologicos', 'ant_ginecolologicos', 'impresion_diagnostica',
        'origen_enfermedad', 'plan_tratamiento', 'pronostico'
    ];
    public function paciente() { return $this->belongsTo(Paciente::class); }
    public function medico() { return $this->belongsTo(User::class, 'medico_id'); }
}
