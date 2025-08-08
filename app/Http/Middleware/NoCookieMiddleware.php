<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NoCookieMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Solo configurar directorios básicos sin tocar cookies
            $this->ensureBasicDirectories();

            // Continuar con el request sin establecer cookies
            $response = $next($request);

            // Asegurar que siempre retornamos una respuesta válida
            if ($response === null) {
                return response('OK', 200);
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('Error en NoCookieMiddleware', [
                'error' => $e->getMessage(),
                'url' => $request->url(),
                'method' => $request->method()
            ]);

            // Retornar una respuesta de error simple
            return response('Error interno del servidor', 500);
        }
    }

    /**
     * Asegurar directorios básicos sin tocar cookies
     */
    private function ensureBasicDirectories(): void
    {
        // Solo crear directorios necesarios
        $directories = [
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
            storage_path('framework/views'),
            storage_path('logs')
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }

        // Configurar headers básicos sin cookies
        if (function_exists('header')) {
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
        }
    }
}
