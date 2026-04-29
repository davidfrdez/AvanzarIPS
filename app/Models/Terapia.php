<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Terapia extends Model
{
    use SoftDeletes, \App\Traits\Auditable;

    protected $table = 'terapias';

    protected $fillable = [
        'paciente_id', 'profesional_id', 'objetivo_id', 'actividad_id',
        'especialidad_id', 'firma_electronica', 'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'firma_electronica' => 'encrypted',
    ];

    protected $hidden = [
        'firma_electronica',
    ];

    public function paciente(): BelongsTo { return $this->belongsTo(Paciente::class); }
    public function profesional(): BelongsTo { return $this->belongsTo(User::class, 'profesional_id'); }
    public function objetivo(): BelongsTo { return $this->belongsTo(Objetivo::class); }
    public function actividad(): BelongsTo { return $this->belongsTo(Actividad::class); }
    public function especialidad(): BelongsTo { return $this->belongsTo(Especialidad::class); }
    public function resultados(): HasMany { return $this->hasMany(ResultadoTerapia::class); }
}
