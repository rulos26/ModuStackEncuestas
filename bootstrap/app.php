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
        'fix.session.hosting' => \App\Http\Middleware\FixSessionForHosting::class,
        'safe.session' => \App\Http\Middleware\SafeSessionMiddleware::class,
        'no.cookie' => \App\Http\Middleware\NoCookieMiddleware::class,
        'validar.fechas' => \App\Http\Middleware\ValidarFechas::class,
    ]);

    // Aplicar middleware sin cookies globalmente
    $middleware->prepend(\App\Http\Middleware\NoCookieMiddleware::class);
})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
