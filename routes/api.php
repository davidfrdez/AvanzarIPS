<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// FUERA del middleware
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']); // Ruta PÚBLICA
});

// DENTRO del middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    // ... otras rutas protegidas
});


Route::post('/password/forgot', [PasswordResetController::class, 'sendCode']); // Endpoint 1
Route::post('/password/validate', [PasswordResetController::class, 'validateCode']); // Endpoint 2
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']); // Endpoint 3