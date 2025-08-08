<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NoSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        Log::info('🔧 NO SESSION MIDDLEWARE - Iniciando', [
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);

        // No configurar sesiones, dejar que Laravel maneje esto
        // Solo continuar con el request

        // Continuar con el request
        $response = $next($request);

        Log::info('🔧 NO SESSION MIDDLEWARE - Completado', [
            'response_status' => $response ? $response->getStatusCode() : 'null'
        ]);

        return $response;
    }
}
