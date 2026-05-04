<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ResultadoTerapia;
use App\Models\Terapia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerapiaController extends Controller
{
    public function index(): JsonResponse
    {
        $terapias = Terapia::with(['paciente', 'profesional', 'objetivo', 'resultados'])->get();
        return response()->json(['status' => 'success', 'data' => $terapias]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null || !$user->tienePermiso('terapias.registrar')) {
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        $validated = $request->validate([
            'paciente_id' => 'required|integer|exists:pacientes,id',
            'objetivo_id' => 'required|integer|exists:objetivos,id',
            'actividad_id' => 'required|integer|exists:actividades,id',
            'especialidad_id' => 'required|integer|exists:especialidades,id',
            'firma_electronica' => 'required|string',
            'fecha_hora' => 'nullable|date',
            'resultados' => 'required|array',
            'resultados.*.respuesta_id' => 'required|integer|exists:respuestas,id',
            'resultados.*.marcado' => 'required|boolean',
            'resultados.*.notas_libres' => 'nullable|string',
        ]);

        // Se permiten varias terapias por paciente el mismo dia (F-GDG-020 admite
        // multiples sesiones diarias por especialidad), pero NO en la misma franja
        // horaria (hora en punto) para evitar registros duplicados accidentales.
        $existe = Terapia::where('paciente_id', $validated['paciente_id'])
            ->whereBetween('fecha_hora', [now()->startOfHour(), now()->endOfHour()])
            ->exists();

        if ($existe) {
            return response()->json([
                'status' => 'error',
                'message' => sprintf(
                    'Ya existe una terapia registrada para este paciente entre las %s y las %s.',
                    now()->startOfHour()->format('H:i'),
                    now()->endOfHour()->format('H:i')
                ),
            ], 422);
        }

        $terapia = DB::transaction(function () use ($validated, $user): Terapia {
            // Cast 'encrypted' del modelo se encarga de cifrar — no usar encrypt() manual.
            $terapia = Terapia::create([
                'paciente_id' => $validated['paciente_id'],
                'profesional_id' => $user->id,
                'objetivo_id' => $validated['objetivo_id'],
                'actividad_id' => $validated['actividad_id'],
                'especialidad_id' => $validated['especialidad_id'],
                'firma_electronica' => $validated['firma_electronica'],
                'fecha_hora' => $validated['fecha_hora'] ?? now(),
            ]);

            foreach ($validated['resultados'] as $resultado) {
                ResultadoTerapia::create([
                    'terapia_id' => $terapia->id,
                    'respuesta_id' => $resultado['respuesta_id'],
                    'marcado' => $resultado['marcado'],
                    'notas_libres' => $resultado['notas_libres'] ?? null,
                ]);
            }

            return $terapia;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Terapia registrada exitosamente',
            'data' => $terapia->load('resultados'),
        ], 201);
    }
}
