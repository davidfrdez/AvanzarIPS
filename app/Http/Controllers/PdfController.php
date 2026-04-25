<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Paciente;
use App\Models\Terapia;

class PdfController extends Controller
{
    public function descargarHistoria($paciente_id)
    {
        $paciente = Paciente::findOrFail($paciente_id);
        
        // Cargar todas las terapias con sus relaciones
        $terapias = Terapia::with(['profesional', 'especialidad', 'objetivo', 'actividad', 'resultados.respuesta'])
            ->where('paciente_id', $paciente_id)
            ->orderBy('fecha_hora', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.historia_clinica', compact('paciente', 'terapias'));
        
        // Retorna un PDF directamente para su descarga (nombre de archivo dinámico)
        return $pdf->download("historia_clinica_{$paciente->cedula}.pdf");
    }
}
