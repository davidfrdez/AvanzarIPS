<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoTerapia extends Model
{
    use \App\Traits\Auditable;

    protected $table = 'resultados_terapias';

    public $timestamps = false;

    protected $fillable = ['terapia_id', 'respuesta_id', 'marcado', 'notas_libres'];

    protected $casts = [
        'marcado' => 'boolean',
    ];

    public function terapia(): BelongsTo { return $this->belongsTo(Terapia::class); }
    public function respuesta(): BelongsTo { return $this->belongsTo(Respuesta::class); }
}
