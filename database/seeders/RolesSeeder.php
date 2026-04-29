<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['Administrador', 'Medico', 'Coordinador'];

        foreach ($roles as $nombre) {
            Rol::updateOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
        }
    }
}
