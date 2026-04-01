<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    // Indicamos la tabla porque no sigue el plural estándar de Laravel
    protected $table = 'password_reset_codes';

    protected $fillable = [
        'correo',
        'code',
        'created_at',
    ];

    // Desactivamos updated_at ya que solo nos interesa cuándo se creó
    public $timestamps = false;

    // Aseguramos que created_at se trate como fecha
    protected $casts = [
        'created_at' => 'datetime',
    ];
}