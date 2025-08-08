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
        // Deshabilitar completamente las sesiones para páginas públicas
        config([
            'session.driver' => 'array', // Usar array en lugar de file para evitar cookies
            'session.lifetime' => 0,
            'session.expire_on_close' => true,
            'session.encrypt' => false,
            'session.cookie' => null,
            'session.cookie_path' => null,
            'session.cookie_domain' => null,
            'session.cookie_secure' => false,
            'session.cookie_http_only' => false,
            'session.cookie_same_site' => null
        ]);

        // Configurar headers para evitar caché y cookies
        if (function_exists('header')) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }

        // Continuar con el request
        $response = $next($request);

        // Asegurar que siempre retornamos una respuesta válida
        if ($response === null) {
            return response('OK', 200);
        }

        return $response;
    }
}
