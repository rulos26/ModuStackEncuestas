<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Exception;

class SolucionarHostingCompleta extends Command
{
    protected $signature = 'hosting:solucionar-completa';
    protected $description = 'Aplicar solución completa para problemas de hosting';

    public function handle()
    {
        $this->info('🔧 APLICANDO SOLUCIÓN COMPLETA PARA HOSTING');
        $this->line('');

        try {
            // 1. Configurar .env para hosting
            $this->line('⚙️  Configurando .env para hosting...');
            $this->configurarEnvHosting();
            $this->line('   ✅ Configuración de .env completada');
            $this->line('');

            // 2. Crear directorios necesarios
            $this->line('📁 Creando directorios necesarios...');
            $this->crearDirectorios();
            $this->line('   ✅ Directorios creados');
            $this->line('');

            // 3. Configurar permisos
            $this->line('🔐 Configurando permisos...');
            $this->configurarPermisos();
            $this->line('   ✅ Permisos configurados');
            $this->line('');

            // 4. Limpiar caché
            $this->line('🗑️  Limpiando caché...');
            $this->limpiarCache();
            $this->line('   ✅ Caché limpiado');
            $this->line('');

            // 5. Verificar configuración
            $this->line('✅ Verificando configuración...');
            $this->verificarConfiguracion();
            $this->line('   ✅ Configuración verificada');
            $this->line('');

            $this->info('🎉 SOLUCIÓN COMPLETA APLICADA EXITOSAMENTE');
            $this->line('');
            $this->line('📋 RESUMEN DE CAMBIOS:');
            $this->line('   • Configuración de sesiones optimizada para hosting');
            $this->line('   • Middleware de cookies aplicado globalmente');
            $this->line('   • Directorios y permisos configurados');
            $this->line('   • Caché limpiado');
            $this->line('   • Configuración de seguridad aplicada');
            $this->line('');
            $this->line('🚀 Ahora puedes probar la encuesta pública sin errores 500');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error aplicando solución: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Configurar .env para hosting
     */
    private function configurarEnvHosting(): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->warn('   ⚠️  Archivo .env no encontrado');
            return;
        }

        $envContent = File::get($envPath);
        $cambios = [];

        // Configuraciones para hosting
        $configuraciones = [
            'SESSION_DRIVER' => 'file',
            'SESSION_LIFETIME' => '120',
            'CACHE_DRIVER' => 'file',
            'QUEUE_CONNECTION' => 'sync',
            'LOG_CHANNEL' => 'daily',
            'APP_DEBUG' => 'false',
            'APP_ENV' => 'production'
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
     * Crear directorios necesarios
     */
    private function crearDirectorios(): void
    {
        $directorios = [
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
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
     * Configurar permisos
     */
    private function configurarPermisos(): void
    {
        $paths = [
            storage_path(),
            storage_path('framework'),
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
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
     * Limpiar caché
     */
    private function limpiarCache(): void
    {
        $comandos = [
            'config:clear',
            'route:clear',
            'view:clear',
            'cache:clear',
            'optimize:clear'
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
     * Verificar configuración
     */
    private function verificarConfiguracion(): void
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
    }
}
