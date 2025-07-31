<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'verificar.token.encuesta' => \App\Http\Middleware\VerificarTokenEncuesta::class,
        'validar.flujo.encuesta' => \App\Http\Middleware\ValidarFlujoEncuesta::class,
        'fix.hosting.cookies' => \App\Http\Middleware\FixHostingCookies::class,
    ]);

    // Aplicar middleware de hosting globalmente
    $middleware->append(\App\Http\Middleware\FixHostingCookies::class);
})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
