<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    // Laravel por defecto busca la tabla 'rols', así que forzamos 'roles'
    protected $table = 'roles';

    protected $fillable = ['nombre'];
}
