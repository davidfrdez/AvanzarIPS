<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Asegúrate de que esta línea esté presente

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        // Un usuario pertenece a un solo rol
        return $this->belongsTo(Rol::class);
        return $this->belongsTo(Rol::class, 'rol_id');
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
        // Si el usuario no tiene rol, no tiene permisos
        if (!$this->rol) {
            return false;
        }

        // Busca en los permisos del rol si existe el slug solicitado
        return $this->rol->permisos()->where('vista', $permisoSlug)->exists();
    }
}
