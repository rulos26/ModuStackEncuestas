<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SafeSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Configurar sesiones de manera segura
            $this->configureSafeSession();

            // Continuar con el request
            $response = $next($request);

            // Verificar que la respuesta no sea null antes de intentar establecer cookies
            if ($response !== null) {
                return $response;
            } else {
                // Si la respuesta es null, crear una respuesta básica
                return response('OK', 200);
            }

        } catch (\Exception $e) {
            Log::error('Error en SafeSessionMiddleware', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
                'method' => $request->method()
            ]);

            // Retornar una respuesta de error en lugar de fallar
            return response('Error interno del servidor', 500);
        }
    }

    /**
     * Configurar sesión de manera segura
     */
    private function configureSafeSession(): void
    {
        // Configuraciones básicas de sesión
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
            'session.cookie_same_site' => 'lax'
        ]);

        // Asegurar directorio de sesiones
        $sessionPath = storage_path('framework/sessions');
        if (!is_dir($sessionPath)) {
            mkdir($sessionPath, 0755, true);
        }

        // Inicializar sesión solo si es necesario
        $this->initializeSessionIfNeeded();
    }

    /**
     * Inicializar sesión solo si es necesario
     */
    private function initializeSessionIfNeeded(): void
    {
        // Verificar si la sesión ya está iniciada
        if (function_exists('session_status')) {
            $sessionStatus = session_status();

            // Solo iniciar sesión si no está iniciada
            if ($sessionStatus === PHP_SESSION_NONE) {
                if (function_exists('session_start')) {
                    session_start();
                }
            }
        }

        // Establecer cookie de sesión solo si hay una sesión válida
        if (function_exists('session_id')) {
            $sessionId = session_id();
            if ($sessionId && !empty($sessionId)) {
                // Usar setcookie de manera segura
                if (function_exists('setcookie')) {
                    setcookie('laravel_session', $sessionId, time() + 7200, '/', '', false, true);
                }
            }
        }
    }
}
