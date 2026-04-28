<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// ---------------------------------------------------
// RUTAS PÚBLICAS (No requieren token)
// ---------------------------------------------------

// Rutas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']); 
});

// Ruta de roles (Fuera del prefijo 'auth' para que sea /api/roles)
// *Nota: Si quieres que requiera login, muévela al grupo de Sanctum abajo.
Route::get('/roles', [UserController::class, 'roles']);
Route::get('/especialidades', [UserController::class, 'especialidades']);
// Rutas de recuperación de contraseña
Route::post('/password/forgot', [PasswordResetController::class, 'sendCode']);
Route::post('/password/validate', [PasswordResetController::class, 'validateCode']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);


// ---------------------------------------------------
// RUTAS PROTEGIDAS (Requieren token)
// ---------------------------------------------------

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::get('/medicos', [UserController::class, 'medicos']);
    
    Route::post('/pacientes', [PacienteController::class, 'store']);
    Route::get('/pacientes', [PacienteController::class, 'index']);
    
    Route::post('/citas', [CitaController::class, 'store']);
    
    // --- Nuevos Endpoints Clínicos ---
    Route::get('/objetivos', [App\Http\Controllers\ObjetivoController::class, 'index']);
    Route::get('/terapias', [App\Http\Controllers\TerapiaController::class, 'index']);
    Route::post('/terapias', [App\Http\Controllers\TerapiaController::class, 'store']);
    
    // --- Formularios Clínicos Complementarios ---
    Route::apiResource('historias-ingreso', App\Http\Controllers\HistoriaClinicaIngresoController::class)->only(['index', 'store']);
    Route::apiResource('consentimientos', App\Http\Controllers\ConsentimientoLegalController::class)->only(['index', 'store']);
    Route::apiResource('ordenes-medicas', App\Http\Controllers\OrdenMedicaController::class)->only(['index', 'store']);
    Route::apiResource('consultas-especialistas', App\Http\Controllers\ConsultaEspecialistaController::class)->only(['index', 'store']);
    Route::apiResource('escalas-weefim', App\Http\Controllers\EscalaWeefimController::class)->only(['index', 'store']);

    // --- Módulo de Reportería y Dashboard ---
    Route::get('/dashboard/metrics', [App\Http\Controllers\DashboardController::class, 'metrics']);
    Route::get('/auditoria', [App\Http\Controllers\AuditoriaController::class, 'index']);
    
    // --- Exportación y Documentos ---
    Route::get('/pacientes/{id}/exportar-historia', [App\Http\Controllers\PdfController::class, 'descargarHistoria']);
});