<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OrdenMedica extends Model
{
    protected $table = 'ordenes_medicas';
    protected $fillable = ['paciente_id', 'medico_id', 'descripcion', 'fecha_orden'];
    
    protected $casts = [
        'fecha_orden' => 'date',
    ];

    public function paciente() { return $this->belongsTo(Paciente::class); }
    public function medico() { return $this->belongsTo(User::class, 'medico_id'); }
}
