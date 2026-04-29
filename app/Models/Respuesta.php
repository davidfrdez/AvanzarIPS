<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Respuesta extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'respuestas';
    public $timestamps = false;
    protected $fillable = ['actividad_id', 'texto_predeterminado'];

    public function actividad(): BelongsTo { return $this->belongsTo(Actividad::class); }
}
