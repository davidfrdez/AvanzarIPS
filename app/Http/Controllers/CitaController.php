<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreCitaRequest;
use App\Services\CitaService;

class CitaController extends Controller
{
    public function __construct(protected CitaService $citaService) {}

    public function store(StoreCitaRequest $request)
    {
        $cita = $this->citaService->createCita($request->validated());
        return response()->json(['status' => 'success', 'data' => $cita], 201);
    }
}
