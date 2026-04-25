<?php

namespace App\Http\Controllers;

use App\Models\ConsultaEspecialista;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConsultaEspecialistaController extends Controller
{
    public function index(): JsonResponse
    {
        $consultas = ConsultaEspecialista::with(['paciente', 'medico', 'especialidad'])->get();
        return response()->json(['status' => 'success', 'data' => $consultas]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'especialidad_id' => 'required|integer|exists:especialidades,id',
            'motivo_consulta' => 'required|string',
            'examen_mental' => 'nullable|string',
            'diagnostico' => 'required|string',
            'concepto' => 'required|string',
            'escala_eeag' => 'nullable|string|max:255',
            'firma_electronica' => 'required|string',
        ]);

        $consulta = ConsultaEspecialista::create([
            ...$validated,
            'medico_id' => $request->user()->id,
            'firma_electronica' => encrypt($validated['firma_electronica']),
            'fecha_hora' => now()
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Consulta de especialista guardada y encriptada.',
            'data' => $consulta
        ], 201);
    }
}
