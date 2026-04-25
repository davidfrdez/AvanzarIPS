<?php

namespace App\Http\Controllers;

use App\Models\OrdenMedica;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrdenMedicaController extends Controller
{
    public function index(): JsonResponse
    {
        $ordenes = OrdenMedica::with(['paciente', 'medico'])->get();
        return response()->json(['status' => 'success', 'data' => $ordenes]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'descripcion' => 'required|string',
            'fecha_orden' => 'required|date'
        ]);

        $orden = OrdenMedica::create([
            ...$validated,
            'medico_id' => $request->user()->id
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Orden médica generada correctamente.',
            'data' => $orden
        ], 201);
    }
}
