<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Actividad extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'actividades';
    public $timestamps = false;
    protected $fillable = ['objetivo_id', 'nombre'];

    public function objetivo(): BelongsTo { return $this->belongsTo(Objetivo::class); }
    public function respuestas(): HasMany { return $this->hasMany(Respuesta::class); }
}
