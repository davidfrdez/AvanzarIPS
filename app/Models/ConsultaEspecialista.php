<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultaEspecialista extends Model
{
    use SoftDeletes, \App\Traits\Auditable;

    protected $table = 'consultas_especialistas';
    const UPDATED_AT = null;

    protected $fillable = [
        'paciente_id', 'medico_id', 'especialidad_id', 'motivo_consulta',
        'examen_mental', 'diagnostico', 'concepto', 'escala_eeag',
        'firma_electronica', 'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'firma_electronica' => 'encrypted',
    ];

    protected $hidden = [
        'firma_electronica',
    ];

    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function medico(): BelongsTo { return $this->belongsTo(User::class, 'medico_id'); }
    public function especialidad(): BelongsTo { return $this->belongsTo(Especialidad::class); }
}
