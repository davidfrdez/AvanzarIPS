<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Respuesta extends Model
{
    protected $table = 'respuestas';
    public $timestamps = false;
    protected $fillable = ['actividad_id', 'texto_predeterminado'];

    public function actividad() { return $this->belongsTo(Actividad::class); }
}
