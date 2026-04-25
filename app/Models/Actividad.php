<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividades';
    public $timestamps = false;
    protected $fillable = ['objetivo_id', 'nombre'];

    public function objetivo() { return $this->belongsTo(Objetivo::class); }
    public function respuestas() { return $this->hasMany(Respuesta::class); }
}
