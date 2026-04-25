<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Objetivo;
use App\Models\Actividad;
use App\Models\Respuesta;

class ObjetivosSeeder extends Seeder
{
    public function run(): void
    {
        $objetivo = Objetivo::create([
            'nombre' => 'Mejorar Movilidad Articular',
            'descripcion' => 'Aumentar el rango de movimiento en extremidades superiores.'
        ]);

        $actividad = Actividad::create([
            'objetivo_id' => $objetivo->id,
            'nombre' => 'Ejercicios de estiramiento pasivo'
        ]);

        Respuesta::create([
            'actividad_id' => $actividad->id,
            'texto_predeterminado' => 'El paciente logró completar la extensión sin dolor severo.'
        ]);
        Respuesta::create([
            'actividad_id' => $actividad->id,
            'texto_predeterminado' => 'Se observa limitación articular en los últimos 20 grados.'
        ]);
    }
}
