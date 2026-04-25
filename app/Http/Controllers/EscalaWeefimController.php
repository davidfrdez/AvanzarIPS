<?php

namespace App\Http\Controllers;

use App\Models\EscalaWeefim;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EscalaWeefimController extends Controller
{
    public function index(): JsonResponse
    {
        $escalas = EscalaWeefim::with(['paciente', 'profesional'])->get();
        return response()->json(['status' => 'success', 'data' => $escalas]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'fecha_evaluacion' => 'required|date',
            'subtotal_autocuidado' => 'required|integer|min:0',
            'subtotal_movilidad' => 'required|integer|min:0',
            'subtotal_cognicion' => 'required|integer|min:0',
        ]);

        // Cálculo automático de las métricas según normativa
        $puntajeTotal = $validated['subtotal_autocuidado'] + $validated['subtotal_movilidad'] + $validated['subtotal_cognicion'];
        // Asumiendo un máximo teórico común de 126 para WEEFIM
        $porcentajeFuncionalidad = ($puntajeTotal / 126) * 100;

        $escala = EscalaWeefim::create([
            ...$validated,
            'puntaje_total' => $puntajeTotal,
            'porcentaje_funcionalidad' => round($porcentajeFuncionalidad, 2),
            'profesional_id' => $request->user()->id
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Escala WEEFIM calculada y guardada correctamente.',
            'data' => $escala
        ], 201);
    }
}
