<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCitaRequest;
use App\Models\Cita;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CitaController extends Controller
{
    public function __construct(protected CitaService $citaService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Cita::with(['paciente', 'medico', 'especialidad'])
            ->orderBy('programada_para', 'desc');

        if ($pacienteId = $request->query('paciente_id')) {
            $query->where('paciente_id', (int) $pacienteId);
        }

        if ($medicoId = $request->query('medico_id')) {
            $query->where('medico_id', (int) $medicoId);
        }

        if ($desde = $request->query('desde')) {
            $query->where('programada_para', '>=', $desde);
        }

        if ($hasta = $request->query('hasta')) {
            $query->where('programada_para', '<=', $hasta);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->get(),
        ]);
    }

    public function store(StoreCitaRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Evitar colision: mismo medico no puede tener dos citas a la misma hora.
        $colision = Cita::where('medico_id', $data['medico_id'])
            ->where('programada_para', $data['programada_para'])
            ->exists();

        if ($colision) {
            return response()->json([
                'status' => 'error',
                'message' => 'El profesional ya tiene una cita programada en ese horario.',
            ], 422);
        }

        $cita = $this->citaService->createCita($data);

        return response()->json([
            'status' => 'success',
            'data' => $cita->load(['paciente', 'medico', 'especialidad']),
        ], 201);
    }

    /**
     * Crear múltiples citas en lote para un paciente.
     *
     * Body: `{ paciente_id, citas: [{ medico_id, especialidad_id, programada_para }] }`.
     * Registra las válidas y reporta colisiones de horario sin abortar el lote.
     */
    public function storeBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'paciente_id' => ['required', 'integer', 'exists:pacientes,id'],
            'citas' => ['required', 'array', 'min:1'],
            'citas.*.medico_id' => ['required', 'integer', 'exists:usuarios,id'],
            'citas.*.especialidad_id' => ['required', 'integer', 'exists:especialidades,id'],
            'citas.*.programada_para' => ['required', 'date', 'after:now'],
        ]);

        $creadas = [];
        $errores = [];

        foreach ($validated['citas'] as $idx => $item) {
            $colision = Cita::where('medico_id', $item['medico_id'])
                ->where('programada_para', $item['programada_para'])
                ->exists();

            if ($colision) {
                $errores[] = ['index' => $idx, 'mensaje' => 'Colision de horario para el profesional.'];
                continue;
            }

            $creadas[] = $this->citaService->createCita([
                'paciente_id' => $validated['paciente_id'],
                'medico_id' => $item['medico_id'],
                'especialidad_id' => $item['especialidad_id'],
                'programada_para' => $item['programada_para'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'creadas' => count($creadas),
            'errores' => $errores,
            'data' => $creadas,
        ], 201);
    }
}
