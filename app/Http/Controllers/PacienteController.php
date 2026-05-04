<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Http\Resources\PacienteResource;
use App\Models\Paciente;
use App\Services\Contracts\PacienteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PacienteController extends Controller
{
    public function __construct(
        protected readonly PacienteServiceInterface $pacienteService
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min((int) $request->integer('per_page', 25), 100));

        // ?estado=activos (default) | inactivos | todos
        $estado = $request->input('estado', 'activos');
        if (! in_array($estado, ['activos', 'inactivos', 'todos'], true)) {
            $estado = 'activos';
        }

        return PacienteResource::collection(
            $this->pacienteService->paginate($perPage, $estado)
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

    /**
     * Da de alta (baja clínica) al paciente — esta_activo = false.
     * El historial clínico permanece intacto y consultable.
     * Requiere permiso `pacientes.gestionar`.
     */
    public function darAlta(Request $request, Paciente $paciente): PacienteResource
    {
        if (! $request->user()?->tienePermiso('pacientes.gestionar')) {
            abort(403, 'No tienes permiso para dar de alta pacientes.');
        }

        return new PacienteResource(
            $this->pacienteService->darAlta($paciente)
        );
    }

    /**
     * Reactiva un paciente previamente dado de alta — esta_activo = true.
     * Requiere permiso `pacientes.gestionar`.
     */
    public function reactivar(Request $request, Paciente $paciente): PacienteResource
    {
        if (! $request->user()?->tienePermiso('pacientes.gestionar')) {
            abort(403, 'No tienes permiso para reactivar pacientes.');
        }

        return new PacienteResource(
            $this->pacienteService->reactivar($paciente)
        );
    }
}
