<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
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
