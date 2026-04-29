<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoriaClinicaIngreso extends Model
{
    use SoftDeletes, \App\Traits\Auditable;

    protected $table = 'historias_clinicas_ingreso';

    protected $fillable = [
        'paciente_id', 'medico_id', 'motivo_consulta', 'enfermedad_actual', 'anamnesis',
        'ant_personales', 'ant_familiares', 'ant_quirurgicos', 'ant_patologicos',
        'ant_farmacologicos', 'ant_ginecolologicos', 'impresion_diagnostica',
        'origen_enfermedad', 'plan_tratamiento', 'pronostico',
    ];

    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function medico(): BelongsTo { return $this->belongsTo(User::class, 'medico_id'); }
}
