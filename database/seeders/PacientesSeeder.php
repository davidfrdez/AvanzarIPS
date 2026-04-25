<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Paciente;

class PacientesSeeder extends Seeder
{
    public function run(): void
    {
        Paciente::create([
            'tipo_documento' => 'CC',
            'cedula' => '1234567890',
            'nombres' => 'Juan Carlos',
            'apellidos' => 'Pérez',
            'fecha_nacimiento' => '1990-01-01',
            'sexo' => 'M',
            'direccion' => 'Calle Falsa 123',
            'barrio' => 'El Prado',
            'telefono' => '3001234567',
            'correo' => 'juan.perez@example.com',
            'ocupacion' => 'Ingeniero',
            'eps' => 'Sura',
            'regimen_salud' => 'Contributivo',
            'categoria_eps' => 'A',
            'nombre_responsable' => 'María Pérez',
            'telefono_responsable' => '3007654321',
            'parentesco_responsable' => 'Madre',
        ]);
    }
}
