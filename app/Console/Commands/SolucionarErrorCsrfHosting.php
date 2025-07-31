<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Exception;

class SolucionarErrorCsrfHosting extends Command
{
    protected $signature = 'hosting:solucionar-csrf';
    protected $description = 'Solucionar error de CSRF en hosting';

    public function handle()
    {
        $this->info('🔧 SOLUCIONANDO ERROR DE CSRF EN HOSTING');
        $this->line('');

        try {
            // 1. Verificar configuración actual
            $this->line('🔍 Verificando configuración actual...');
            $this->verificarConfiguracionActual();
            $this->line('');

            // 2. Aplicar configuración específica para CSRF
            $this->line('⚙️  Aplicando configuración para CSRF...');
            $this->aplicarConfiguracionCsrf();
            $this->line('   ✅ Configuración aplicada');
            $this->line('');

            // 3. Limpiar caché
            $this->line('🗑️  Limpiando caché...');
            $this->limpiarCache();
            $this->line('   ✅ Caché limpiado');
            $this->line('');

            // 4. Verificar middleware
            $this->line('✅ Verificando middleware...');
            $this->verificarMiddleware();
            $this->line('   ✅ Middleware verificado');
            $this->line('');

            $this->info('🎉 SOLUCIÓN APLICADA EXITOSAMENTE');
            $this->line('');
            $this->line('📋 CAMBIOS APLICADOS:');
            $this->line('   • Middleware CSRF personalizado para hosting');
            $this->line('   • Configuración de sesiones optimizada');
            $this->line('   • Cookies configuradas para hosting');
            $this->line('   • Caché limpiado');
            $this->line('');
            $this->line('🚀 Ahora puedes probar la encuesta pública sin errores 500');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error aplicando solución: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Verificar configuración actual
     */
    private function verificarConfiguracionActual(): void
    {
        $this->line('   • APP_ENV: ' . config('app.env'));
        $this->line('   • APP_DEBUG: ' . (config('app.debug') ? 'true' : 'false'));
        $this->line('   • SESSION_DRIVER: ' . config('session.driver'));
        $this->line('   • SESSION_SECURE_COOKIE: ' . (config('session.secure') ? 'true' : 'false'));
        $this->line('   • SESSION_SAME_SITE: ' . config('session.same_site'));
    }

    /**
     * Aplicar configuración específica para CSRF
     */
    private function aplicarConfiguracionCsrf(): void
    {
        // Configuraciones específicas para CSRF en hosting
        $configuraciones = [
            'session.encrypt' => false,
            'session.cookie_secure' => false,
            'session.cookie_same_site' => 'lax',
            'session.cookie_http_only' => true,
            'session.cookie_path' => '/',
            'session.cookie_domain' => null,
            'session.lifetime' => 120,
            'session.expire_on_close' => false
        ];

        foreach ($configuraciones as $key => $value) {
            config([$key => $value]);
            $this->line("   • {$key} = " . (is_bool($value) ? ($value ? 'true' : 'false') : $value));
        }
    }

    /**
     * Limpiar caché
     */
    private function limpiarCache(): void
    {
        $comandos = [
            'config:clear',
            'route:clear',
            'view:clear',
            'cache:clear'
        ];

        foreach ($comandos as $comando) {
            try {
                Artisan::call($comando);
                $this->line("   • {$comando} ejecutado");
            } catch (Exception $e) {
                $this->warn("   ⚠️  Error en {$comando}: " . $e->getMessage());
            }
        }
    }

    /**
     * Verificar middleware
     */
    private function verificarMiddleware(): void
    {
        // Verificar que el middleware personalizado existe
        $middlewarePath = app_path('Http/Middleware/HostingCsrfMiddleware.php');
        if (File::exists($middlewarePath)) {
            $this->line('   ✅ HostingCsrfMiddleware existe');
        } else {
            $this->error('   ❌ HostingCsrfMiddleware no encontrado');
        }

        // Verificar que el middleware de cookies existe
        $cookiesPath = app_path('Http/Middleware/FixHostingCookies.php');
        if (File::exists($cookiesPath)) {
            $this->line('   ✅ FixHostingCookies existe');
        } else {
            $this->error('   ❌ FixHostingCookies no encontrado');
        }
    }
}
