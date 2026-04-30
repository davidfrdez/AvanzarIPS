<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Exports\PlantillaPacientesExport;
use App\Imports\PacientesImport;
use App\Models\Paciente;
use App\Services\PacienteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * Cubre el flujo de carga masiva de pacientes vía Excel.
 *
 * Estos tests no requieren autenticación: instancian directamente el Importer
 * y el Exporter para validar la lógica de negocio (validación, persistencia,
 * reporte de errores). Para pruebas E2E con Sanctum + permisos, ver el
 * "Cómo probar manualmente" en el README.
 */
final class PacienteImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_plantilla_excel_se_genera_con_dos_hojas(): void
    {
        Excel::fake();

        Excel::download(new PlantillaPacientesExport(), 'plantilla_pacientes.xlsx');

        Excel::assertDownloaded('plantilla_pacientes.xlsx', function (PlantillaPacientesExport $export): bool {
            $hojas = $export->sheets();
            return count($hojas) === 2;
        });
    }

    public function test_importa_filas_validas_y_reporta_invalidas(): void
    {
        $rutaExcel = $this->generarExcel([
            // Encabezado
            $this->encabezados(),
            // Fila válida
            ['CC', '1000000001', 'Ana', 'Gómez', '1990-01-15', 'F', 'Cra 1 #2-3', 'Centro', '3001112222', null, null, 'Sura', null, null, null, null, null],
            // Fila válida 2
            ['CC', '1000000002', 'Luis', 'Pérez', '1985-07-20', 'M', 'Cl 4 #5-6', 'Sur', '3003334444', 'luis@test.com', null, 'Sanitas', null, null, null, null, null],
            // Fila inválida: sexo X
            ['CC', '1000000003', 'Ko', 'Roto', '1990-01-15', 'X', 'Dir', 'Bar', '3000000000', null, null, 'EPS', null, null, null, null, null],
            // Fila inválida: cédula duplicada con la primera
            ['CC', '1000000001', 'Dup', 'Licada', '1990-01-15', 'F', 'Dir', 'Bar', '3000000000', null, null, 'EPS', null, null, null, null, null],
        ]);

        $import = new PacientesImport(app(PacienteService::class));
        Excel::import($import, $rutaExcel);

        $this->assertSame(2, count($import->insertadas), 'Deben insertarse las 2 filas válidas');
        $this->assertSame(2, count($import->errores), 'Las 2 filas inválidas deben quedar reportadas');
        $this->assertSame(2, Paciente::count(), 'Deben existir 2 pacientes en BD');

        // El reporte de errores incluye fila Excel y mensajes por campo.
        $errorSexo = $import->errores[0];
        $this->assertSame(4, $errorSexo['fila'], 'La fila inválida #1 debe ser la fila 4 del Excel');
        $this->assertArrayHasKey('sexo', $errorSexo['errores']);

        $errorDuplicado = $import->errores[1];
        $this->assertSame(5, $errorDuplicado['fila']);
        $this->assertArrayHasKey('cedula', $errorDuplicado['errores']);
    }

    public function test_filas_completamente_vacias_son_ignoradas(): void
    {
        $rutaExcel = $this->generarExcel([
            $this->encabezados(),
            ['CC', '2000000001', 'Mar', 'Gar', '1990-01-15', 'F', 'Dir', 'Bar', '3000000000', null, null, 'EPS', null, null, null, null, null],
            [null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null, null],
            ['CC', '2000000002', 'Sol', 'Riv', '1990-01-15', 'F', 'Dir', 'Bar', '3000000000', null, null, 'EPS', null, null, null, null, null],
        ]);

        $import = new PacientesImport(app(PacienteService::class));
        Excel::import($import, $rutaExcel);

        $this->assertSame(2, $import->totalFilas);
        $this->assertSame(2, count($import->insertadas));
        $this->assertSame(0, count($import->errores));
    }

    /** @return array<int, string> */
    private function encabezados(): array
    {
        return [
            'tipo_documento', 'cedula', 'nombres', 'apellidos', 'fecha_nacimiento',
            'sexo', 'direccion', 'barrio', 'telefono', 'correo', 'ocupacion',
            'eps', 'regimen_salud', 'categoria_eps', 'nombre_responsable',
            'telefono_responsable', 'parentesco_responsable',
        ];
    }

    /**
     * Genera un xlsx temporal a partir de filas (la primera es el encabezado).
     *
     * @param array<int, array<int, mixed>> $filas
     */
    private function generarExcel(array $filas): UploadedFile
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($filas as $i => $fila) {
            foreach ($fila as $j => $valor) {
                $sheet->setCellValueByColumnAndRow($j + 1, $i + 1, $valor);
            }
        }

        $ruta = tempnam(sys_get_temp_dir(), 'pac_') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($ruta);

        return new UploadedFile($ruta, 'pacientes.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
