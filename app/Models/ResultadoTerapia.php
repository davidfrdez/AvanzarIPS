<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ResultadoTerapia extends Model
{
    protected $table = 'resultados_terapias';
    public $timestamps = false;
    protected $fillable = ['terapia_id', 'respuesta_id', 'marcado', 'notas_libres'];
    
    protected $casts = [
        'marcado' => 'boolean',
    ];

    public function terapia() { return $this->belongsTo(Terapia::class); }
    public function respuesta() { return $this->belongsTo(Respuesta::class); }
}
