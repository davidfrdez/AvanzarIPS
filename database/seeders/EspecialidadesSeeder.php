<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EspecialidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $especialidades = [
            [
                'id' => 1, 
                'nombre' => 'Sin especificar'
            ],
            [
                'id' => 2, 
                'nombre' => 'Fisioterapia'
            ],
            [
                'id' => 3, 
                'nombre' => 'Fonoaudiología'
            ],
            [
                'id' => 4,
                'nombre' => 'Terapia Ocupacional'
            ],
            [
                'id' => 5,
                'nombre' => 'Psicologia'
            ],
            [
                'id' => 6,
                'nombre' => 'Visioterapia'
            ],
        ];

        foreach ($especialidades as $especialidad) {
            DB::table('especialidades')->updateOrInsert(
                ['id' => $especialidad['id']], // Condición de búsqueda
                [
                    'nombre' => $especialidad['nombre'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}