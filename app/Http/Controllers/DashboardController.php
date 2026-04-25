<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Paciente;
use App\Models\Terapia;
use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function metrics(Request $request): JsonResponse
    {
        // Seguridad: Idealmente validar si el usuario tiene rol de Administrador
        // if ($request->user()->rol_id !== 1) return response()->json(['error' => 'No autorizado'], 403);

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. Métricas Generales (Contadores)
        $totalPacientes = Paciente::count();
        $totalTerapiasMes = Terapia::whereBetween('fecha_hora', [$startOfMonth, $endOfMonth])->count();
        $citasPendientes = Cita::where('programada_para', '>=', $now)->count();
        $medicosActivos = User::where('esta_activo', true)->where('rol_id', 2)->count();

        // 2. Terapias por Especialidad (Gráfico de torta)
        $terapiasPorEspecialidad = Terapia::with('especialidad')
            ->selectRaw('especialidad_id, count(*) as total')
            ->groupBy('especialidad_id')
            ->get()
            ->map(function ($item) {
                return [
                    'especialidad' => $item->especialidad->nombre ?? 'Desconocida',
                    'total' => $item->total
                ];
            });

        // 3. Actividad de Profesionales (Ranking)
        $topProfesionales = Terapia::with('profesional')
            ->whereBetween('fecha_hora', [$startOfMonth, $endOfMonth])
            ->selectRaw('profesional_id, count(*) as total')
            ->groupBy('profesional_id')
            ->orderByDesc('total')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'nombre' => $item->profesional->nombre ?? 'Desconocido',
                    'terapias_realizadas' => $item->total
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'kpis' => [
                    'total_pacientes' => $totalPacientes,
                    'terapias_mes_actual' => $totalTerapiasMes,
                    'citas_pendientes' => $citasPendientes,
                    'medicos_activos' => $medicosActivos
                ],
                'graficos' => [
                    'terapias_por_especialidad' => $terapiasPorEspecialidad,
                    'top_profesionales_mes' => $topProfesionales
                ]
            ]
        ]);
    }
}
