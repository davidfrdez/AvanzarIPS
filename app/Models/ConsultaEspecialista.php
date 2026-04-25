<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ConsultaEspecialista extends Model
{
    protected $table = 'consultas_especialistas';
    const UPDATED_AT = null;
    
    protected $fillable = [
        'paciente_id', 'medico_id', 'especialidad_id', 'motivo_consulta',
        'examen_mental', 'diagnostico', 'concepto', 'escala_eeag',
        'firma_electronica', 'fecha_hora'
    ];
    
    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function paciente() { return $this->belongsTo(Paciente::class); }
    public function medico() { return $this->belongsTo(User::class, 'medico_id'); }
    public function especialidad() { return $this->belongsTo(Especialidad::class); }
}
