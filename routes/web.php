<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/docs');
});

// Scalar UI (lee el spec OpenAPI generado por Scramble en /docs/api.json)
Route::get('/docs', function () {
    return view('docs.scalar');
})->name('docs.scalar');
