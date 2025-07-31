<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DisableCsrfForHosting
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Verificar si estamos en hosting
        if ($this->isHostingEnvironment()) {
            // 2. Configurar para hosting
            $this->configureForHosting();

            // 3. Log para debugging
            Log::info('DisableCsrfForHosting ejecutado', [
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip()
            ]);
        }

        return $next($request);
    }

    /**
     * Detectar si estamos en hosting
     */
    private function isHostingEnvironment(): bool
    {
        $host = request()->getHost();
        return strpos($host, 'rulossoluciones.com') !== false ||
               strpos($host, '.com') !== false ||
               config('app.env') === 'production';
    }

    /**
     * Configurar para hosting
     */
    private function configureForHosting(): void
    {
        // Configuraciones críticas para hosting
        config([
            'session.encrypt' => false,
            'session.cookie_secure' => false,
            'session.cookie_same_site' => 'lax',
            'session.cookie_http_only' => true,
            'session.cookie_path' => '/',
            'session.cookie_domain' => null,
            'session.lifetime' => 120,
            'session.expire_on_close' => false,
            'session.lottery' => [2, 100]
        ]);

        // Asegurar que las sesiones funcionen
        if (function_exists('session_start') && !session_id()) {
            session_start();
        }

        // Configurar headers para evitar problemas de cookies
        if (function_exists('header')) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
    }
}
