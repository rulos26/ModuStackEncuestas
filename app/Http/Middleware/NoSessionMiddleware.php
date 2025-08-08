<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NoSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Solo continuar con el request sin modificar sesiones
        return $next($request);
    }
}
