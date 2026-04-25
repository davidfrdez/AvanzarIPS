<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ConsentimientoLegal extends Model
{
    protected $table = 'consentimientos_legales';
    const UPDATED_AT = null;
    
    protected $fillable = [
        'paciente_id', 'tipo_consentimiento', 'estado', 'firmado_por_representante',
        'nombre_firmante', 'documento_firmante', 'fecha_firma'
    ];
    
    protected $casts = [
        'firmado_por_representante' => 'boolean',
        'fecha_firma' => 'date',
    ];

    public function paciente() { return $this->belongsTo(Paciente::class); }
}
