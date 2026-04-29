<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Http\Resources\PacienteResource;
use App\Models\Paciente;
use App\Services\Contracts\PacienteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PacienteController extends Controller
{
    public function __construct(
        protected readonly PacienteServiceInterface $pacienteService
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        $perPage = (int) request()->integer('per_page', 25);
        $perPage = max(1, min($perPage, 100));

        return PacienteResource::collection(
            $this->pacienteService->paginate($perPage)
        );
    }

    public function show(Paciente $paciente): PacienteResource
    {
        return new PacienteResource(
            $paciente->load('historiasClinicasIngreso')
        );
    }

    public function store(StorePacienteRequest $request): JsonResponse
    {
        $paciente = $this->pacienteService->create(
            $request->toPacienteDTO(),
            $request->toIngresoDTO()
        );

        return (new PacienteResource($paciente))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Paciente $paciente): JsonResponse
    {
        $this->pacienteService->softDelete($paciente);

        return response()->json([
            'status' => 'success',
            'message' => 'Paciente eliminado (soft delete) correctamente.',
        ]);
    }
}
