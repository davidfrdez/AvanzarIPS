<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePacienteRequest;
use App\Http\Resources\PacienteResource;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Terapia;
use App\Services\Contracts\PacienteServiceInterface;
use Carbon\CarbonImmutable;
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
     * Dar de alta a un paciente (baja clínica).
     *
     * Marca `esta_activo = false`. El historial permanece consultable.
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
     * Reactivar un paciente dado de alta.
     *
     * Marca `esta_activo = true`. Requiere permiso `pacientes.gestionar`.
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

    /**
     * Balance de horas de un paciente en un mes.
     *
     * Devuelve citas programadas, terapias ejecutadas y saldo disponible.
     * Parámetro opcional `?mes=YYYY-MM` (default: mes actual).
     */
    public function balanceHoras(Request $request, Paciente $paciente): JsonResponse
    {
        $mesParam = $request->input('mes'); // "2026-05"

        if ($mesParam && ! preg_match('/^\d{4}-\d{2}$/', $mesParam)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El parámetro mes debe tener formato YYYY-MM (ej: 2026-05).',
            ], 422);
        }

        $fecha = $mesParam
            ? CarbonImmutable::createFromFormat('Y-m', $mesParam)->startOfMonth()
            : CarbonImmutable::now()->startOfMonth();

        $programadas = Cita::where('paciente_id', $paciente->id)
            ->whereYear('programada_para', $fecha->year)
            ->whereMonth('programada_para', $fecha->month)
            ->count();

        $ejecutadas = Terapia::where('paciente_id', $paciente->id)
            ->whereYear('fecha_hora', $fecha->year)
            ->whereMonth('fecha_hora', $fecha->month)
            ->count();

        $disponibles = max(0, $programadas - $ejecutadas);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'paciente_id'        => $paciente->id,
                'mes'                => $fecha->format('Y-m'),
                'horas_programadas'  => $programadas,
                'horas_ejecutadas'   => $ejecutadas,
                'horas_disponibles'  => $disponibles,
                'puede_registrar'    => $disponibles > 0,
            ],
        ]);
    }
}
