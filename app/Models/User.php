<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, \App\Traits\Auditable;

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
    public function tienePermiso(string $permisoSlug): bool
    {
        if (!$this->rol) {
            return false;
        }

        // Administrador tiene acceso implícito a todo (super-rol).
        if ($this->rol->nombre === 'Administrador') {
            return true;
        }

        return $this->rol->permisos()->where('vista', $permisoSlug)->exists();
    }

    /**
     * Override Laravel's authorization gate to use our RBAC.
     * Allows usage of `$user->can('pacientes.crear')` y middleware `can:`.
     *
     * @param  iterable|string  $abilities
     * @param  array|mixed  $arguments
     */
    public function can($abilities, $arguments = []): bool
    {
        $checks = is_array($abilities) ? $abilities : [$abilities];
        foreach ($checks as $ability) {
            if ($this->tienePermiso((string) $ability)) {
                return true;
            }
        }
        return parent::can($abilities, $arguments);
    }

    // Nuevas relaciones clínicas
    public function historiasClinicasIngreso() { return $this->hasMany(HistoriaClinicaIngreso::class, 'medico_id'); }
    public function ordenesMedicas() { return $this->hasMany(OrdenMedica::class, 'medico_id'); }
    public function consultasEspecialistas() { return $this->hasMany(ConsultaEspecialista::class, 'medico_id'); }
    public function escalasWeefim() { return $this->hasMany(EscalaWeefim::class, 'profesional_id'); }
    public function terapias() { return $this->hasMany(Terapia::class, 'profesional_id'); }
}
