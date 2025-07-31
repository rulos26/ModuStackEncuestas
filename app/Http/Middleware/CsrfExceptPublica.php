<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifyCsrfToken;

class CsrfExceptPublica extends BaseVerifyCsrfToken
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        // Configurar sesiones para hosting
        $this->configureForHosting();

        return parent::handle($request, $next);
    }

    /**
     * Configurar para hosting
     */
    private function configureForHosting(): void
    {
        // Configuraciones específicas para hosting
        config([
            'session.encrypt' => false,
            'session.cookie_secure' => false,
            'session.cookie_same_site' => 'lax',
            'session.cookie_http_only' => true,
            'session.cookie_path' => '/',
            'session.cookie_domain' => null,
            'session.lifetime' => 120,
            'session.expire_on_close' => false
        ]);
    }

    /**
     * Obtener las URLs que deben ser excluidas de CSRF
     */
    protected function except(): array
    {
        return [
            'publica/*',           // Rutas de encuestas públicas
            'api/*',               // Rutas de API
            'webhook/*'            // Webhooks
        ];
    }
}
