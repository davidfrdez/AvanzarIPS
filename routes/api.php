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
Route::get('/roles', [UserController::class, 'Roles']);
Route::get('/especialidades', [UserController::class, 'Especialidades']);
// Rutas de recuperación de contraseña
Route::post('/password/forgot', [PasswordResetController::class, 'sendCode']);
Route::post('/password/validate', [PasswordResetController::class, 'validateCode']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);


// ---------------------------------------------------
// RUTAS PROTEGIDAS (Requieren token)
// ---------------------------------------------------

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::post('/pacientes', [PacienteController::class, 'store']);
    Route::post('/citas', [CitaController::class, 'store']);
});