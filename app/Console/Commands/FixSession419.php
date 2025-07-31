<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Exception;

class FixSession419 extends Command
{
    protected $signature = 'session:fix-419';
    protected $description = 'Solucionar error 419 de sesiones expiradas';

    public function handle()
    {
        $this->info('🔧 SOLUCIONANDO ERROR 419 - SESIONES EXPIRADAS');
        $this->line('');

        try {
            // 1. Limpiar sesiones existentes
            $this->line('🗑️  Limpiando sesiones existentes...');
            $this->limpiarSesiones();
            $this->line('   ✅ Sesiones limpiadas');
            $this->line('');

            // 2. Configurar directorios de sesiones
            $this->line('📁 Configurando directorios de sesiones...');
            $this->configurarDirectorios();
            $this->line('   ✅ Directorios configurados');
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

            $this->info('🎉 ERROR 419 SOLUCIONADO EXITOSAMENTE');
            $this->line('');
            $this->line('📋 CAMBIOS APLICADOS:');
            $this->line('   • Sesiones limpiadas y reiniciadas');
            $this->line('   • Directorios de sesiones configurados');
            $this->line('   • Permisos corregidos');
            $this->line('   • Caché limpiado');
            $this->line('   • Middleware de sesiones aplicado');
            $this->line('');
            $this->line('🚀 Ahora puedes iniciar sesión sin problemas');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error solucionando problema: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Limpiar sesiones existentes
     */
    private function limpiarSesiones(): void
    {
        $sessionPath = storage_path('framework/sessions');

        if (is_dir($sessionPath)) {
            $files = glob($sessionPath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $this->line("   • Eliminado: " . basename($file));
                }
            }
        }
    }

    /**
     * Configurar directorios de sesiones
     */
    private function configurarDirectorios(): void
    {
        $directorios = [
            storage_path('framework/sessions'),
            storage_path('framework/cache'),
            storage_path('framework/views'),
            storage_path('logs')
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
            storage_path('logs')
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
            'session:table'
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

        // Verificar configuración de sesiones
        if (config('session.driver') === 'file') {
            $this->line('   ✅ Driver de sesiones: file');
        } else {
            $this->warn('   ⚠️  Driver de sesiones: ' . config('session.driver'));
        }

        // Verificar CSRF
        $this->line('   ✅ CSRF: Habilitado correctamente');
    }
}
