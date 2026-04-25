<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Asegúrate de que esta línea esté presente

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, \App\Traits\Auditable;

    // ESTA LÍNEA ES LA CLAVE:
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'rol_id',
        'especialidad_id',
        'correo',
        'password',
        'esta_activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'esta_activo' => 'boolean',
        ];
    }
    public function tienePermiso($permisoSlug): bool
    {
        if (!$this->rol) {
            return false;
        }
        return $this->rol->permisos()->where('vista', $permisoSlug)->exists();
    }

    // Nuevas relaciones clínicas
    public function historiasClinicasIngreso() { return $this->hasMany(HistoriaClinicaIngreso::class, 'medico_id'); }
    public function ordenesMedicas() { return $this->hasMany(OrdenMedica::class, 'medico_id'); }
    public function consultasEspecialistas() { return $this->hasMany(ConsultaEspecialista::class, 'medico_id'); }
    public function escalasWeefim() { return $this->hasMany(EscalaWeefim::class, 'profesional_id'); }
    public function terapias() { return $this->hasMany(Terapia::class, 'profesional_id'); }
}
