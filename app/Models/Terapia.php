<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Terapia extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'terapias';
    protected $fillable = ['paciente_id', 'profesional_id', 'objetivo_id', 'actividad_id', 'especialidad_id', 'firma_electronica', 'fecha_hora'];
    
    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    public function paciente() { return $this->belongsTo(Paciente::class); }
    public function profesional() { return $this->belongsTo(User::class, 'profesional_id'); }
    public function objetivo() { return $this->belongsTo(Objetivo::class); }
    public function actividad() { return $this->belongsTo(Actividad::class); }
    public function especialidad() { return $this->belongsTo(Especialidad::class); }
    public function resultados() { return $this->hasMany(ResultadoTerapia::class); }
}
