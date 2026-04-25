<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'pacientes';

    protected $fillable = [
        'tipo_documento',
        'cedula',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'barrio',
        'telefono',
        'correo',
        'ocupacion',
        'eps',
        'regimen_salud',
        'categoria_eps',
        'nombre_responsable',
        'telefono_responsable',
        'parentesco_responsable',
    ];

    // Relación: Un paciente tiene muchas citas
    public function citas()
    {
        return $this->hasMany(Cita::class);
    }

    // Nuevas relaciones clínicas
    public function historiasClinicasIngreso() { return $this->hasMany(HistoriaClinicaIngreso::class); }
    public function consentimientosLegales() { return $this->hasMany(ConsentimientoLegal::class); }
    public function ordenesMedicas() { return $this->hasMany(OrdenMedica::class); }
    public function consultasEspecialistas() { return $this->hasMany(ConsultaEspecialista::class); }
    public function escalasWeefim() { return $this->hasMany(EscalaWeefim::class); }
    public function terapias() { return $this->hasMany(Terapia::class); }
}
