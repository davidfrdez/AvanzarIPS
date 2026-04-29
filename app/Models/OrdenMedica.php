<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenMedica extends Model
{
    use SoftDeletes, \App\Traits\Auditable;

    protected $table = 'ordenes_medicas';

    protected $fillable = ['paciente_id', 'medico_id', 'descripcion', 'fecha_orden'];

    protected $casts = [
        'fecha_orden' => 'date',
    ];

    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function medico(): BelongsTo { return $this->belongsTo(User::class, 'medico_id'); }
}
