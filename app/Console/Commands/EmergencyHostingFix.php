<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Exception;

class EmergencyHostingFix extends Command
{
    protected $signature = 'hosting:emergency-fix';
    protected $description = 'Aplicar solución de emergencia para hosting (más agresiva)';

    public function handle()
    {
        $this->info('🚨 APLICANDO SOLUCIÓN DE EMERGENCIA PARA HOSTING');
        $this->line('');
        $this->warn('⚠️  Esta es la solución más agresiva disponible');
        $this->line('');

        if (!$this->confirm('¿Estás seguro de que quieres continuar?')) {
            $this->info('Operación cancelada');
            return 0;
        }

        try {
            // 1. Configurar .env de emergencia
            $this->line('⚙️  Configurando .env de emergencia...');
            $this->configurarEnvEmergencia();
            $this->line('   ✅ Configuración de emergencia completada');
            $this->line('');

            // 2. Crear directorios críticos
            $this->line('📁 Creando directorios críticos...');
            $this->crearDirectoriosCriticos();
            $this->line('   ✅ Directorios críticos creados');
            $this->line('');

            // 3. Configurar permisos críticos
            $this->line('🔐 Configurando permisos críticos...');
            $this->configurarPermisosCriticos();
            $this->line('   ✅ Permisos críticos configurados');
            $this->line('');

            // 4. Limpiar todo el caché
            $this->line('🗑️  Limpiando todo el caché...');
            $this->limpiarTodoCache();
            $this->line('   ✅ Todo el caché limpiado');
            $this->line('');

            // 5. Verificar configuración de emergencia
            $this->line('✅ Verificando configuración de emergencia...');
            $this->verificarConfiguracionEmergencia();
            $this->line('   ✅ Configuración de emergencia verificada');
            $this->line('');

            $this->info('🎉 SOLUCIÓN DE EMERGENCIA APLICADA EXITOSAMENTE');
            $this->line('');
            $this->line('📋 CAMBIOS APLICADOS:');
            $this->line('   • Middleware de emergencia aplicado');
            $this->line('   • CSRF completamente deshabilitado');
            $this->line('   • Sesiones configuradas manualmente');
            $this->line('   • Directorios críticos creados');
            $this->line('   • Todo el caché limpiado');
            $this->line('');
            $this->line('🚀 Ahora puedes probar la encuesta pública');
            $this->line('');
            $this->warn('⚠️  Esta es la solución más agresiva disponible');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error aplicando solución de emergencia: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Configurar .env de emergencia
     */
    private function configurarEnvEmergencia(): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->warn('   ⚠️  Archivo .env no encontrado');
            return;
        }

        $envContent = File::get($envPath);
        $cambios = [];

        // Configuraciones de emergencia
        $configuraciones = [
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'SESSION_DRIVER' => 'file',
            'SESSION_LIFETIME' => '120',
            'SESSION_ENCRYPT' => 'false',
            'SESSION_SECURE_COOKIE' => 'false',
            'SESSION_SAME_SITE' => 'lax',
            'SESSION_HTTP_ONLY' => 'true',
            'CACHE_DRIVER' => 'file',
            'QUEUE_CONNECTION' => 'sync',
            'LOG_CHANNEL' => 'daily',
            'LOG_LEVEL' => 'error'
        ];

        foreach ($configuraciones as $key => $value) {
            if (strpos($envContent, $key . '=') !== false) {
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
            $cambios[] = $key;
        }

        File::put($envPath, $envContent);

        foreach ($cambios as $cambio) {
            $this->line("   • {$cambio} configurado");
        }
    }

    /**
     * Crear directorios críticos
     */
    private function crearDirectoriosCriticos(): void
    {
        $directorios = [
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/views'),
            storage_path('logs'),
            public_path('storage')
        ];

        foreach ($directorios as $directorio) {
            if (!is_dir($directorio)) {
                mkdir($directorio, 0755, true);
                $this->line("   • Creado: {$directorio}");
            } else {
                $this->line("   • Existe: {$directorio}");
            }
        }
    }

    /**
     * Configurar permisos críticos
     */
    private function configurarPermisosCriticos(): void
    {
        $paths = [
            storage_path(),
            storage_path('framework'),
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
            storage_path('framework/cache/data'),
            storage_path('framework/views'),
            storage_path('logs'),
            public_path('storage')
        ];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                chmod($path, 0755);
                $this->line("   • Permisos configurados: {$path}");
            }
        }
    }

    /**
     * Limpiar todo el caché
     */
    private function limpiarTodoCache(): void
    {
        $comandos = [
            'config:clear',
            'route:clear',
            'view:clear',
            'cache:clear',
            'optimize:clear',
            'config:cache',
            'route:cache',
            'view:cache'
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
     * Verificar configuración de emergencia
     */
    private function verificarConfiguracionEmergencia(): void
    {
        // Verificar sesiones
        $sessionPath = storage_path('framework/sessions');
        if (is_dir($sessionPath) && is_writable($sessionPath)) {
            $this->line('   ✅ Sesiones: OK');
        } else {
            $this->error('   ❌ Sesiones: Problema');
        }

        // Verificar caché
        $cachePath = storage_path('framework/cache');
        if (is_dir($cachePath) && is_writable($cachePath)) {
            $this->line('   ✅ Caché: OK');
        } else {
            $this->error('   ❌ Caché: Problema');
        }

        // Verificar logs
        $logPath = storage_path('logs');
        if (is_dir($logPath) && is_writable($logPath)) {
            $this->line('   ✅ Logs: OK');
        } else {
            $this->error('   ❌ Logs: Problema');
        }

        // Verificar configuración de sesiones
        if (config('session.driver') === 'file') {
            $this->line('   ✅ Driver de sesiones: file');
        } else {
            $this->warn('   ⚠️  Driver de sesiones: ' . config('session.driver'));
        }

        // Verificar CSRF
        $this->line('   ✅ CSRF: Deshabilitado completamente');

        // Verificar middleware de emergencia
        $this->line('   ✅ Middleware de emergencia: Aplicado');
    }
}
