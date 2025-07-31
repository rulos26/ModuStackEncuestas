<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FixSessionForHosting
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Configurar sesiones para hosting
        $this->configureSessionForHosting();

        return $next($request);
    }

    /**
     * Configurar sesiones para hosting
     */
    private function configureSessionForHosting(): void
    {
        // Configuraciones específicas para hosting
        config([
            'session.driver' => 'file',
            'session.lifetime' => 120,
            'session.expire_on_close' => false,
            'session.encrypt' => false,
            'session.cookie' => 'laravel_session',
            'session.cookie_path' => '/',
            'session.cookie_domain' => null,
            'session.cookie_secure' => false,
            'session.cookie_http_only' => true,
            'session.cookie_same_site' => 'lax',
            'session.lottery' => [2, 100]
        ]);

        // Asegurar que el directorio de sesiones existe
        $sessionPath = storage_path('framework/sessions');
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }

        // Configurar headers para evitar problemas de cookies
        if (function_exists('header')) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
    }
}
