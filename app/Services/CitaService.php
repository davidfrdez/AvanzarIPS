<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\AuditoriaCambio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CitaService
{
    public function createCita(array $data)
    {
        return DB::transaction(function () use ($data) {
            $cita = Cita::create($data);

            AuditoriaCambio::create([
                'usuario_id'   => Auth::id(),
                'accion'       => 'CREAR',
                'nombre_tabla' => 'citas',
                'registro_id'  => $cita->id,
                'detalles'     => "Cita agendada para el paciente ID {$data['paciente_id']} con el médico ID {$data['medico_id']}",
            ]);

            return $cita;
        });
    }
}