<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PublicPageMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo configurar headers para evitar caché
        if (function_exists('header')) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        // Continuar con el request sin modificar sesiones
        return $next($request);
    }
}
