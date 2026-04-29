<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EscalaWeefim extends Model
{
    use SoftDeletes, \App\Traits\Auditable;

    protected $table = 'escalas_weefim';
    const UPDATED_AT = null;

    protected $fillable = [
        'paciente_id', 'profesional_id', 'fecha_evaluacion', 'subtotal_autocuidado',
        'subtotal_movilidad', 'subtotal_cognicion', 'puntaje_total', 'porcentaje_funcionalidad',
    ];

    protected $casts = [
        'fecha_evaluacion' => 'date',
        'porcentaje_funcionalidad' => 'decimal:2',
    ];

    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function profesional(): BelongsTo { return $this->belongsTo(User::class, 'profesional_id'); }
}
