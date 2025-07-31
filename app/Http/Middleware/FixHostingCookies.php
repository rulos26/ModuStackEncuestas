<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

class FixHostingCookies
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Verificar si estamos en un entorno de hosting
        if ($this->isHostingEnvironment()) {
            // 2. Configurar sesiones para hosting
            $this->configureForHosting();

            // 3. Configurar CSRF para hosting
            $this->configureCsrfForHosting();

            // 4. Log para debugging
            Log::info('FixHostingCookies middleware ejecutado', [
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip()
            ]);
        }

        return $next($request);
    }

    /**
     * Detectar si estamos en un entorno de hosting
     */
    private function isHostingEnvironment(): bool
    {
        // Detectar hosting por dominio o configuración
        $host = request()->getHost();
        $isHosting = strpos($host, 'rulossoluciones.com') !== false ||
                     strpos($host, '.com') !== false ||
                     strpos($host, '.net') !== false ||
                     strpos($host, '.org') !== false ||
                     config('app.env') === 'production';

        return $isHosting;
    }

    /**
     * Configurar para hosting
     */
    private function configureForHosting(): void
    {
        // 1. Configurar sesiones para archivos
        config(['session.driver' => 'file']);

        // 2. Asegurar que el directorio de sesiones existe
        $sessionPath = storage_path('framework/sessions');
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }

        // 3. Configurar cookies de manera segura
        config([
            'session.secure' => false, // No forzar HTTPS en hosting
            'session.same_site' => 'lax',
            'session.http_only' => true
        ]);

        // 4. Configurar timezone si no está definido
        if (!date_default_timezone_get()) {
            date_default_timezone_set('America/Bogota');
        }

        // 5. Configurar límites de memoria si es necesario
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '256M');
            ini_set('max_execution_time', 300);
        }
    }

    /**
     * Configurar CSRF para hosting
     */
    private function configureCsrfForHosting(): void
    {
        // 1. Configurar CSRF para funcionar sin cookies
        config([
            'session.encrypt' => false,
            'session.cookie' => 'laravel_session',
            'session.lifetime' => 120,
            'session.expire_on_close' => false,
            'session.lottery' => [2, 100],
            'session.cookie_path' => '/',
            'session.cookie_domain' => null,
            'session.cookie_secure' => false,
            'session.cookie_http_only' => true,
            'session.cookie_same_site' => 'lax'
        ]);

        // 2. Asegurar que la respuesta tenga headers correctos
        if (function_exists('header')) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
    }
}
