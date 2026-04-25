<?php

namespace App\Http\Controllers;

use App\Models\ConsentimientoLegal;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ConsentimientoLegalController extends Controller
{
    public function index(): JsonResponse
    {
        $consentimientos = ConsentimientoLegal::with('paciente')->get();
        return response()->json(['status' => 'success', 'data' => $consentimientos]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'tipo_consentimiento' => 'required|string|max:255',
            'estado' => 'required|string|in:Firmado,Rechazado,Pendiente',
            'firmado_por_representante' => 'required|boolean',
            'nombre_firmante' => 'nullable|required_if:firmado_por_representante,true|string|max:255',
            'documento_firmante' => 'nullable|required_if:firmado_por_representante,true|string|max:255',
            'fecha_firma' => 'required|date',
        ]);

        $consentimiento = ConsentimientoLegal::create($validated);

        return response()->json([
            'status' => 'success', 
            'message' => 'Consentimiento legal guardado de forma exitosa.',
            'data' => $consentimiento
        ], 201);
    }
}
