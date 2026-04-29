<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Objetivo extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $table = 'objetivos';

    protected $fillable = ['nombre', 'descripcion'];

    public function actividades(): HasMany
    {
        return $this->hasMany(Actividad::class);
    }
}
