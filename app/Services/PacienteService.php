<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\AuditoriaCambio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PacienteService
{
    public function createPaciente(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            $paciente = Paciente::create([
                'tipo_documento' => $data['tipo_documento'],
                'cedula' => $data['cedula'],
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'fecha_nacimiento' => $data['fecha_nacimiento'],
                'sexo' => $data['sexo'],
                'direccion' => $data['direccion'],
                'barrio' => $data['barrio'],
                'telefono' => $data['telefono'],
                'correo' => $data['correo'] ?? null,
                'ocupacion' => $data['ocupacion'] ?? null,
                'eps' => $data['eps'],
                'regimen_salud' => $data['regimen_salud'] ?? null,
                'categoria_eps' => $data['categoria_eps'] ?? null,
                'nombre_responsable' => $data['nombre_responsable'] ?? null,
                'telefono_responsable' => $data['telefono_responsable'] ?? null,
                'parentesco_responsable' => $data['parentesco_responsable'] ?? null,
            ]);

            // Trazabilidad legal obligatoria
            AuditoriaCambio::create([
                'usuario_id' => Auth::id() ?? 1, // Fallback a 1 si se está usando Tinker/Semillas
                'accion' => 'CREAR',
                'nombre_tabla' => 'pacientes',
                'registro_id' => $paciente->id,
                'detalles' => 'Se registró un nuevo paciente con cédula: ' . $paciente->cedula,
            ]);

            return $paciente;
        });
    }
}
