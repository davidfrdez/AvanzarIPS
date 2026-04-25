<?php

namespace App\Http\Controllers;

use App\Models\HistoriaClinicaIngreso;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HistoriaClinicaIngresoController extends Controller
{
    public function index(): JsonResponse
    {
        $historias = HistoriaClinicaIngreso::with(['paciente', 'medico'])->get();
        return response()->json(['status' => 'success', 'data' => $historias]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'motivo_consulta' => 'required|string',
            'enfermedad_actual' => 'required|string',
            'anamnesis' => 'required|string',
            'ant_personales' => 'nullable|string',
            'ant_familiares' => 'nullable|string',
            'ant_quirurgicos' => 'nullable|string',
            'ant_patologicos' => 'nullable|string',
            'ant_farmacologicos' => 'nullable|string',
            'ant_ginecolologicos' => 'nullable|string',
            'impresion_diagnostica' => 'required|string',
            'origen_enfermedad' => 'required|string',
            'plan_tratamiento' => 'required|string',
            'pronostico' => 'required|string'
        ]);

        $historia = HistoriaClinicaIngreso::create([
            ...$validated,
            'medico_id' => $request->user()->id
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Historia clínica de ingreso registrada correctamente.',
            'data' => $historia
        ], 201);
    }
}
