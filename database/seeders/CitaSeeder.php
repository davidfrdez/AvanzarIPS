<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cita;
use App\Models\User;
use App\Models\Paciente;
use Carbon\Carbon;

class CitaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buscamos un médico (ej: rol_id = 2) y un paciente que ya existan
        $medico = User::where('rol_id', 2)->first();
        $paciente = Paciente::first();

        // Evitamos errores si la base de datos está vacía
        if (!$medico || !$paciente) {
            $this->command->warn('No se pueden crear citas: Faltan médicos o pacientes en la BD.');
            return;
        }

        // 2. Creamos Citas de prueba usando el especialidad_id
        Cita::create([
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'especialidad_id' => 2, // CAMBIO: ID 2 = Fisioterapia
            // Programa la cita para pasado mañana a las 10:00 AM
            'programada_para' => Carbon::now()->addDays(2)->setHour(10)->setMinute(0),
        ]);

        Cita::create([
            'paciente_id' => $paciente->id,
            'medico_id' => $medico->id,
            'especialidad_id' => 3, // CAMBIO: ID 3 = Fonoaudiología
            // Programa la cita para dentro de 3 días a las 2:30 PM
            'programada_para' => Carbon::now()->addDays(3)->setHour(14)->setMinute(30),
        ]);

        $this->command->info('Citas de prueba creadas exitosamente.');
    }
}
