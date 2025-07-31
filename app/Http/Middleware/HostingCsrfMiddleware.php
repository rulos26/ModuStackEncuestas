<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifyCsrfToken;

class HostingCsrfMiddleware extends BaseVerifyCsrfToken
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        // 1. Verificar si estamos en hosting
        if ($this->isHostingEnvironment()) {
            // 2. Configurar CSRF para hosting
            $this->configureForHosting();

            // 3. Log para debugging
            Log::info('HostingCsrfMiddleware ejecutado', [
                'url' => $request->url(),
                'method' => $request->method()
            ]);
        }

        return parent::handle($request, $next);
    }

    /**
     * Determinar si el request debe ser excluido de la verificación CSRF
     */
    protected function tokensMatch($request)
    {
        // En hosting, ser más permisivo con CSRF
        if ($this->isHostingEnvironment()) {
            // Permitir requests sin token en ciertos casos
            if ($request->isMethod('GET')) {
                return true;
            }

            // Para POST, verificar token pero ser más flexible
            $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');

            if (!$token) {
                Log::warning('CSRF token no encontrado en hosting', [
                    'url' => $request->url(),
                    'method' => $request->method()
                ]);

                // En hosting, permitir continuar sin token
                return true;
            }
        }

        return parent::tokensMatch($request);
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
        // Configurar sesiones para funcionar en hosting
        config([
            'session.encrypt' => false,
            'session.cookie_secure' => false,
            'session.cookie_same_site' => 'lax',
            'session.cookie_http_only' => true
        ]);
    }

    /**
     * Obtener las URLs que deben ser excluidas de CSRF
     */
    protected function except(): array
    {
        return [
            'publica/*',
            'api/*',
            'webhook/*'
        ];
    }
}
