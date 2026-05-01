<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\PlantillaCitasExport;
use App\Exports\PlantillaUsuariosExport;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Catálogo y plantillas de cargas masivas disponibles en la API.
 *
 * Sirve dos propósitos:
 *  1. `index()` devuelve la lista de tipos de carga disponibles para que el
 *     frontend pueda renderizar dinámicamente una pantalla de "Cargas masivas"
 *     sin hardcodear las rutas.
 *  2. Endpoints de descarga de plantillas (citas, usuarios) que sirven hoy
 *     como **ejemplos** para futuras cargas. La importación correspondiente
 *     se implementará cuando se priorice cada flujo.
 *
 * El único flujo con import funcional hoy es `pacientes`, que vive en
 * `PacienteImportController`. Este controller solo lo expone en el catálogo.
 */
class CargasMasivasController extends Controller
{
    /**
     * Lista las cargas masivas disponibles.
     *
     * Cada entrada incluye:
     *  - `key`: identificador estable (`pacientes`, `citas`, `usuarios`).
     *  - `nombre`: etiqueta humana.
     *  - `descripcion`: qué hace y a qué tabla afecta.
     *  - `plantilla_url`: endpoint para descargar el xlsx de plantilla.
     *  - `import_url`: endpoint para subir el xlsx (null si aún no implementado).
     *  - `disponible`: true si el flujo completo (descarga + import) ya funciona.
     *  - `permiso`: permiso requerido para usarlo.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                [
                    'key' => 'pacientes',
                    'nombre' => 'Pacientes',
                    'descripcion' => 'Carga masiva de pacientes en la tabla `pacientes`. Tope 500 filas.',
                    'plantilla_url' => '/api/pacientes/plantilla-excel',
                    'import_url' => '/api/pacientes/importar-excel',
                    'disponible' => true,
                    'permiso' => 'pacientes.crear',
                    'tope_filas' => 500,
                ],
                [
                    'key' => 'citas',
                    'nombre' => 'Citas (ejemplo, no implementado)',
                    'descripcion' => 'Plantilla ejemplo para futura carga masiva de citas. Solo descarga; el import aún no está disponible.',
                    'plantilla_url' => '/api/cargas-masivas/citas/plantilla',
                    'import_url' => null,
                    'disponible' => false,
                    'permiso' => 'pacientes.crear',
                    'tope_filas' => null,
                ],
                [
                    'key' => 'usuarios',
                    'nombre' => 'Usuarios (ejemplo, no implementado)',
                    'descripcion' => 'Plantilla ejemplo para futura carga masiva de personal/médicos. Solo descarga; el import aún no está disponible.',
                    'plantilla_url' => '/api/cargas-masivas/usuarios/plantilla',
                    'import_url' => null,
                    'disponible' => false,
                    'permiso' => 'usuarios.crear',
                    'tope_filas' => null,
                ],
            ],
        ]);
    }

    /**
     * Descargar plantilla EJEMPLO para futura carga masiva de citas.
     *
     * Estructura: paciente_id, medico_id, especialidad_id, programada_para.
     *
     * @response file
     */
    public function plantillaCitas(): BinaryFileResponse
    {
        return Excel::download(new PlantillaCitasExport(), 'plantilla_citas.xlsx');
    }

    /**
     * Descargar plantilla EJEMPLO para futura carga masiva de usuarios.
     *
     * Estructura: nombre, correo, rol_id, especialidad_id, esta_activo.
     * El password NO va en la plantilla por seguridad — se debe generar
     * un temporal al implementar el import.
     *
     * @response file
     */
    public function plantillaUsuarios(): BinaryFileResponse
    {
        return Excel::download(new PlantillaUsuariosExport(), 'plantilla_usuarios.xlsx');
    }
}
