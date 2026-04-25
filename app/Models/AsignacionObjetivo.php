<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AsignacionObjetivo extends Model
{
    protected $table = 'asignaciones_objetivos';
    public $timestamps = false;
    protected $fillable = ['objetivo_id', 'rol_id', 'usuario_id'];

    public function objetivo() { return $this->belongsTo(Objetivo::class); }
    public function rol() { return $this->belongsTo(Rol::class); }
    public function usuario() { return $this->belongsTo(User::class, 'usuario_id'); }
}
