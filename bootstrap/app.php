<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware; 
use Illuminate\Http\Request;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // --- ESTE BLOQUE TE FALTA ---
    ->withMiddleware(function (Middleware $middleware) {
        // No necesitas escribir nada aquí adentro por ahora, 
        // pero tener el bloque vacío ayuda a Laravel a inicializar 
        // correctamente los grupos por defecto (como 'api').
    })
    // ----------------------------
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, $e) {
            if ($request->is('api/*')) {
                return true;
            }

            return $request->expectsJson();
        });
    })->create();