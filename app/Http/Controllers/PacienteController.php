<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Services\PacienteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PacienteController extends Controller
{
    public function __construct(
        protected PacienteService $pacienteService
    ) {}

    public function store(StorePacienteRequest $request): JsonResponse
    {
        $paciente = $this->pacienteService->createPaciente($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Paciente registrado exitosamente.',
            'data' => $paciente
        ], 201);
    }
}
