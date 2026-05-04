<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CargasMasivasController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ConsentimientoLegalController;
use App\Http\Controllers\ConsultaEspecialistaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EscalaWeefimController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\HistoriaClinicaIngresoController;
use App\Http\Controllers\ObjetivoController;
use App\Http\Controllers\OrdenMedicaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PacienteImportController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\RespuestaController;
use App\Http\Controllers\TerapiaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------
// RUTAS PÚBLICAS (No requieren token)
// ---------------------------------------------------

// Login con rate limiting (C10) — 5 intentos por minuto por IP+correo
Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::get('/roles', [UserController::class, 'roles']);
Route::get('/especialidades', [UserController::class, 'especialidades']);

Route::post('/password/forgot', [PasswordResetController::class, 'sendCode'])
    ->middleware('throttle:5,1');
Route::post('/password/validate', [PasswordResetController::class, 'validateCode'])
    ->middleware('throttle:10,1');
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword'])
    ->middleware('throttle:5,1');

// ---------------------------------------------------
// RUTAS PROTEGIDAS (Requieren token Sanctum)
// ---------------------------------------------------

Route::middleware('auth:sanctum')->group(function (): void {
    // --- Auth ---
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // --- Usuarios (Personal y Profesionales) ---
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios/{user}', [UserController::class, 'show']);
    Route::put('/usuarios/{user}', [UserController::class, 'update']);              // FE-3
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy']);          // FE-3
    Route::put('/usuarios/{user}/desactivar', [UserController::class, 'desactivar']); // FE-3
    Route::put('/usuarios/{user}/activar', [UserController::class, 'activar']);
    Route::get('/medicos', [UserController::class, 'medicos']);

    // --- Pacientes ---
    // Carga masiva (deben ir ANTES del show con {paciente} para evitar conflicto de ruta).
    Route::get('/pacientes/plantilla-excel', [PacienteImportController::class, 'plantilla']);
    Route::post('/pacientes/importar-excel', [PacienteImportController::class, 'import']);

    Route::get('/pacientes', [PacienteController::class, 'index']);
    Route::post('/pacientes', [PacienteController::class, 'store']);
    Route::get('/pacientes/{paciente}', [PacienteController::class, 'show']);
    Route::put('/pacientes/{paciente}/alta', [PacienteController::class, 'darAlta']);        // dar de alta (desactivar)
    Route::put('/pacientes/{paciente}/reactivar', [PacienteController::class, 'reactivar']); // reactivar
    Route::delete('/pacientes/{paciente}', [PacienteController::class, 'destroy']);

    // --- Citas ---
    Route::get('/citas', [CitaController::class, 'index']);
    Route::post('/citas', [CitaController::class, 'store']);
    Route::post('/citas/batch', [CitaController::class, 'storeBatch']);

    // --- Especialidades (CRUD admin) ---
    Route::post('/especialidades', [EspecialidadController::class, 'store']);
    Route::get('/especialidades/{especialidad}', [EspecialidadController::class, 'show']);
    Route::put('/especialidades/{especialidad}', [EspecialidadController::class, 'update']);
    Route::delete('/especialidades/{especialidad}', [EspecialidadController::class, 'destroy']);

    // --- Árbol Clínico (FE-2 — M2) ---
    Route::apiResource('objetivos', ObjetivoController::class);
    Route::apiResource('actividades', ActividadController::class)
        ->only(['store', 'update', 'destroy'])
        ->parameters(['actividades' => 'actividad']);
    Route::apiResource('respuestas', RespuestaController::class)
        ->only(['store', 'update', 'destroy']);

    // --- Terapias ---
    Route::get('/terapias', [TerapiaController::class, 'index']);
    Route::post('/terapias', [TerapiaController::class, 'store']);

    // --- Formularios Clínicos Complementarios ---
    Route::apiResource('historias-ingreso', HistoriaClinicaIngresoController::class)->only(['index', 'store']);
    Route::apiResource('consentimientos', ConsentimientoLegalController::class)->only(['index', 'store']);
    Route::apiResource('ordenes-medicas', OrdenMedicaController::class)->only(['index', 'store']);
    Route::apiResource('consultas-especialistas', ConsultaEspecialistaController::class)->only(['index', 'store']);
    Route::apiResource('escalas-weefim', EscalaWeefimController::class)->only(['index', 'store']);

    // --- Reportería y Dashboard ---
    Route::get('/dashboard/metrics', [DashboardController::class, 'metrics']);
    Route::get('/auditoria', [AuditoriaController::class, 'index']);

    // --- Exportación ---
    Route::get('/pacientes/{id}/exportar-historia', [PdfController::class, 'descargarHistoria']);

    // --- Cargas masivas (catálogo + plantillas ejemplo) ---
    Route::get('/cargas-masivas', [CargasMasivasController::class, 'index']);
    Route::get('/cargas-masivas/citas/plantilla', [CargasMasivasController::class, 'plantillaCitas']);
    Route::get('/cargas-masivas/usuarios/plantilla', [CargasMasivasController::class, 'plantillaUsuarios']);
});
