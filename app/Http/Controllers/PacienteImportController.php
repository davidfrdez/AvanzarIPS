<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AccionAuditoria;
use App\Exports\PlantillaPacientesExport;
use App\Http\Requests\ImportPacientesRequest;
use App\Imports\PacientesImport;
use App\Models\AuditoriaCambio;
use App\Services\Contracts\PacienteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Endpoints de carga masiva de pacientes vía Excel.
 *
 * - GET  /api/pacientes/plantilla-excel  → descarga plantilla.xlsx
 * - POST /api/pacientes/importar-excel   → procesa archivo subido
 */
class PacienteImportController extends Controller
{
    public function __construct(
        protected readonly PacienteServiceInterface $pacienteService,
    ) {
    }

    /**
     * Descargar plantilla Excel para carga masiva de pacientes.
     *
     * Devuelve un archivo `.xlsx` con dos hojas:
     *
     * - **Pacientes**: encabezados oficiales + una fila de ejemplo.
     * - **Catálogos**: valores válidos para `tipo_documento` (CC, TI, CE, RC, PA, PE)
     *   y `sexo` (M, F).
     *
     * El archivo se llama `plantilla_pacientes.xlsx`.
     *
     * @response file
     */
    public function plantilla(): BinaryFileResponse
    {
        return Excel::download(
            new PlantillaPacientesExport(),
            'plantilla_pacientes.xlsx'
        );
    }

    /**
     * Importar pacientes desde Excel (carga masiva).
     *
     * Sube un archivo `.xlsx` o `.xls` (campo `archivo`, multipart/form-data)
     * con las columnas idénticas a la plantilla descargable.
     *
     * **Tope máximo:** 500 filas por archivo. Las filas adicionales son ignoradas.
     *
     * **Política de errores (best-effort):** las filas válidas se insertan, las
     * inválidas se devuelven en `data.errores` con `fila` (número en Excel),
     * `cedula` y un mapa `errores` por campo.
     *
     * **Códigos:**
     * - `201` cuando todas las filas se importaron sin errores.
     * - `207` cuando hay éxitos parciales (algunas filas con errores).
     * - `403` si el usuario no tiene el permiso `pacientes.crear`.
     * - `422` si el archivo no es un .xlsx/.xls válido o supera 5 MB.
     */
    public function import(ImportPacientesRequest $request): JsonResponse
    {
        /** @var \Illuminate\Http\UploadedFile $archivo */
        $archivo = $request->file('archivo');

        $import = new PacientesImport($this->pacienteService);
        Excel::import($import, $archivo);

        $excedioLimite = $import->totalFilas >= PacientesImport::LIMITE_FILAS;

        // Bitácora del lote (Ley 2015): un único registro de auditoría por
        // batch resumiendo qué se importó, además de los registros individuales
        // que ya genera el trait Auditable de Paciente por cada fila insertada.
        AuditoriaCambio::create([
            'usuario_id' => Auth::id(),
            'accion' => AccionAuditoria::CARGA_MASIVA->value,
            'nombre_tabla' => 'pacientes',
            'registro_id' => 0, // 0 = batch (no aplica a un solo registro)
            'detalles' => json_encode([
                'tipo_carga' => 'pacientes',
                'archivo' => $archivo->getClientOriginalName(),
                'tamano_bytes' => $archivo->getSize(),
                'total_filas_procesadas' => $import->totalFilas,
                'total_insertadas' => count($import->insertadas),
                'total_errores' => count($import->errores),
                'excedio_limite' => $excedioLimite,
                'cedulas_insertadas' => array_map(
                    static fn (array $row): ?string => $row['cedula'] ?? null,
                    $import->insertadas,
                ),
            ], JSON_UNESCAPED_UNICODE),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => sprintf(
                '%d pacientes importados, %d con errores.',
                count($import->insertadas),
                count($import->errores),
            ),
            'data' => [
                'total_filas_procesadas' => $import->totalFilas,
                'insertadas' => $import->insertadas,
                'total_insertadas' => count($import->insertadas),
                'errores' => $import->errores,
                'total_errores' => count($import->errores),
                'limite_filas' => PacientesImport::LIMITE_FILAS,
                'excedio_limite' => $excedioLimite,
            ],
        ], count($import->errores) > 0 ? 207 : 201);
    }
}
