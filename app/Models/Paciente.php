<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use HasFactory, SoftDeletes, \App\Traits\Auditable;

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
        'esta_activo',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'esta_activo'      => 'boolean',
    ];

    public function citas(): HasMany { return $this->hasMany(Cita::class); }
    public function historiasClinicasIngreso(): HasMany { return $this->hasMany(HistoriaClinicaIngreso::class); }
    public function consentimientosLegales(): HasMany { return $this->hasMany(ConsentimientoLegal::class); }
    public function ordenesMedicas(): HasMany { return $this->hasMany(OrdenMedica::class); }
    public function consultasEspecialistas(): HasMany { return $this->hasMany(ConsultaEspecialista::class); }
    public function escalasWeefim(): HasMany { return $this->hasMany(EscalaWeefim::class); }
    public function terapias(): HasMany { return $this->hasMany(Terapia::class); }
}
