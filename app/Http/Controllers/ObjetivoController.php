<?php

namespace App\Http\Controllers;

use App\Models\Objetivo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ObjetivoController extends Controller
{
    public function index(): JsonResponse
    {
        // Se trae el objetivo con todo el árbol (actividades y sus respuestas predeterminadas)
        $objetivos = Objetivo::with('actividades.respuestas')->get();
        return response()->json(['status' => 'success', 'data' => $objetivos]);
    }
}
