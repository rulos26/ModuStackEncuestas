<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmergencyHostingFix
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Configuración de emergencia para hosting
        $this->emergencyHostingConfig();

        // 2. Log para debugging
        Log::info('EmergencyHostingFix ejecutado', [
            'url' => $request->url(),
            'method' => $request->method(),
            'ip' => $request->ip()
        ]);

        return $next($request);
    }

    /**
     * Configuración de emergencia para hosting
     */
    private function emergencyHostingConfig(): void
    {
        // 1. Configurar sesiones de emergencia
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

        // 2. Configurar caché de emergencia
        config([
            'cache.default' => 'file',
            'cache.stores.file' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/data'),
            ]
        ]);

        // 3. Configurar aplicación de emergencia
        config([
            'app.debug' => false,
            'app.env' => 'production'
        ]);

        // 4. Asegurar directorios críticos
        $this->ensureCriticalDirectories();

        // 5. Configurar headers de emergencia
        $this->setEmergencyHeaders();

        // 6. Inicializar sesión manualmente si es necesario
        $this->initializeSession();
    }

    /**
     * Asegurar directorios críticos
     */
    private function ensureCriticalDirectories(): void
    {
        $directories = [
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/views'),
            storage_path('logs'),
            public_path('storage')
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (is_dir($directory)) {
                chmod($directory, 0755);
            }
        }
    }

    /**
     * Configurar headers de emergencia
     */
    private function setEmergencyHeaders(): void
    {
        if (function_exists('header')) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
    }

    /**
     * Inicializar sesión manualmente
     */
    private function initializeSession(): void
    {
        // Configurar sesión manualmente
        if (function_exists('session_start') && !session_id()) {
            session_start();
        }

        // Configurar cookies manualmente si es necesario
        if (function_exists('setcookie') && session_id()) {
            $sessionId = session_id();
            if ($sessionId) {
                setcookie('laravel_session', $sessionId, time() + 7200, '/', '', false, true);
            }
        }
    }
}
