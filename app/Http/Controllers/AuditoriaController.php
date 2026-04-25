<?php

namespace App\Http\Controllers;

use App\Models\AuditoriaCambio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditoriaController extends Controller
{
    public function index(): JsonResponse
    {
        // Solo mostramos las auditorias recientes, conociendo quién fue el autor
        $auditorias = AuditoriaCambio::with('usuario')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['status' => 'success', 'data' => $auditorias]);
    }
}
