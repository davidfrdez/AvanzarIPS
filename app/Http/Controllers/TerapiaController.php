<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\ResultadoTerapia;
use App\Models\Terapia;
use Carbon\CarbonImmutable;
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
            'paciente_id'              => 'required|integer|exists:pacientes,id',
            'objetivo_id'              => 'required|integer|exists:objetivos,id',
            'actividad_id'             => 'required|integer|exists:actividades,id',
            'especialidad_id'          => 'required|integer|exists:especialidades,id',
            'firma_electronica'        => 'required|string',
            'fecha_hora'               => 'nullable|date',
            'resultados'               => 'required|array',
            'resultados.*.respuesta_id' => 'required|integer|exists:respuestas,id',
            'resultados.*.marcado'     => 'required|boolean',
            'resultados.*.notas_libres' => 'nullable|string',
        ]);

        // Fecha efectiva de la terapia (la enviada por el cliente o ahora mismo).
        $fechaHora = isset($validated['fecha_hora'])
            ? CarbonImmutable::parse($validated['fecha_hora'])
            : CarbonImmutable::now();

        // ── Registro retroactivo ──────────────────────────────────────────────
        // Si fecha_hora es anterior a hoy se considera retroactivo y requiere
        // permiso especial (solo admin / supervisor pueden corregir olvidos).
        if ($fechaHora->startOfDay()->lt(CarbonImmutable::today())) {
            if (! $user->tienePermiso('terapias.retroactivo')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No tienes permiso para registrar terapias con fecha anterior a hoy. '
                        . 'Solicita al administrador que realice el registro retroactivo.',
                ], 403);
            }
        }

        // ── Cupo mensual ─────────────────────────────────────────────────────
        // Un paciente no puede tener más terapias que citas programadas en el mes.
        // Andrés carga las citas del mes por adelantado; ese conteo es el cupo.
        $citasMes = Cita::where('paciente_id', $validated['paciente_id'])
            ->whereYear('programada_para', $fechaHora->year)
            ->whereMonth('programada_para', $fechaHora->month)
            ->count();

        $terapiasMes = Terapia::where('paciente_id', $validated['paciente_id'])
            ->whereYear('fecha_hora', $fechaHora->year)
            ->whereMonth('fecha_hora', $fechaHora->month)
            ->count();

        if ($terapiasMes >= $citasMes) {
            return response()->json([
                'status'  => 'error',
                'message' => sprintf(
                    'El paciente ya alcanzó el cupo mensual de %d sesión(es) para %s. '
                    . 'Programa más citas antes de registrar nuevas terapias.',
                    $citasMes,
                    $fechaHora->format('m/Y')
                ),
                'data' => [
                    'horas_programadas' => $citasMes,
                    'horas_ejecutadas'  => $terapiasMes,
                ],
            ], 422);
        }

        // ── Duplicado en la misma franja horaria ─────────────────────────────
        // Se permiten varias terapias por paciente el mismo día (F-GDG-020),
        // pero NO dos en la misma franja HH:00-HH:59.
        $existe = Terapia::where('paciente_id', $validated['paciente_id'])
            ->whereBetween('fecha_hora', [
                $fechaHora->startOfHour(),
                $fechaHora->endOfHour(),
            ])
            ->exists();

        if ($existe) {
            return response()->json([
                'status'  => 'error',
                'message' => sprintf(
                    'Ya existe una terapia registrada para este paciente entre las %s y las %s.',
                    $fechaHora->startOfHour()->format('H:i'),
                    $fechaHora->endOfHour()->format('H:i')
                ),
            ], 422);
        }

        $terapia = DB::transaction(function () use ($validated, $user, $fechaHora): Terapia {
            // Cast 'encrypted' del modelo se encarga de cifrar — no usar encrypt() manual.
            $terapia = Terapia::create([
                'paciente_id'      => $validated['paciente_id'],
                'profesional_id'   => $user->id,
                'objetivo_id'      => $validated['objetivo_id'],
                'actividad_id'     => $validated['actividad_id'],
                'especialidad_id'  => $validated['especialidad_id'],
                'firma_electronica' => $validated['firma_electronica'],
                'fecha_hora'       => $fechaHora,
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
