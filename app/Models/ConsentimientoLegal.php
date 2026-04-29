<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsentimientoLegal extends Model
{
    use SoftDeletes, \App\Traits\Auditable;

    protected $table = 'consentimientos_legales';
    const UPDATED_AT = null;

    protected $fillable = [
        'paciente_id', 'tipo_consentimiento', 'estado', 'firmado_por_representante',
        'nombre_firmante', 'documento_firmante', 'fecha_firma',
    ];

    protected $casts = [
        'firmado_por_representante' => 'boolean',
        'fecha_firma' => 'date',
        'nombre_firmante' => 'encrypted',
        'documento_firmante' => 'encrypted',
    ];

    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
}
