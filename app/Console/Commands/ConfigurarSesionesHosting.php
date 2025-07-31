<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Exception;

class ConfigurarSesionesHosting extends Command
{
    protected $signature = 'hosting:configurar-sesiones';
    protected $description = 'Configurar sesiones para hosting y solucionar problemas de cookies';

    public function handle()
    {
        $this->info('🔧 CONFIGURANDO SESIONES PARA HOSTING');
        $this->line('');

        try {
            // 1. Verificar directorio de sesiones
            $this->line('📁 Verificando directorio de sesiones...');
            $sessionPath = storage_path('framework/sessions');

            if (!File::exists($sessionPath)) {
                File::makeDirectory($sessionPath, 0755, true);
                $this->line('   ✅ Directorio de sesiones creado');
            } else {
                $this->line('   ✅ Directorio de sesiones existe');
            }

            // 2. Verificar permisos
            $this->line('🔐 Verificando permisos...');
            if (is_writable($sessionPath)) {
                $this->line('   ✅ Permisos correctos');
            } else {
                $this->line('   ⚠️  Permisos insuficientes - intentando corregir...');
                chmod($sessionPath, 0755);
                if (is_writable($sessionPath)) {
                    $this->line('   ✅ Permisos corregidos');
                } else {
                    $this->line('   ❌ No se pudieron corregir los permisos');
                }
            }

            // 3. Limpiar sesiones antiguas
            $this->line('🧹 Limpiando sesiones antiguas...');
            $files = File::glob($sessionPath . '/*');
            $deleted = 0;

            foreach ($files as $file) {
                if (File::isFile($file) && time() - File::lastModified($file) > 86400) {
                    File::delete($file);
                    $deleted++;
                }
            }

            $this->line("   ✅ {$deleted} sesiones antiguas eliminadas");

            // 4. Verificar configuración de .env
            $this->line('⚙️  Verificando configuración...');
            $envPath = base_path('.env');

            if (File::exists($envPath)) {
                $envContent = File::get($envPath);

                // Verificar SESSION_DRIVER
                if (strpos($envContent, 'SESSION_DRIVER=file') === false) {
                    if (strpos($envContent, 'SESSION_DRIVER=') !== false) {
                        $envContent = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=file', $envContent);
                    } else {
                        $envContent .= "\nSESSION_DRIVER=file";
                    }
                    File::put($envPath, $envContent);
                    $this->line('   ✅ SESSION_DRIVER configurado como "file"');
                } else {
                    $this->line('   ✅ SESSION_DRIVER ya está configurado correctamente');
                }

                // Verificar SESSION_LIFETIME
                if (strpos($envContent, 'SESSION_LIFETIME=120') === false) {
                    if (strpos($envContent, 'SESSION_LIFETIME=') !== false) {
                        $envContent = preg_replace('/SESSION_LIFETIME=.*/', 'SESSION_LIFETIME=120', $envContent);
                    } else {
                        $envContent .= "\nSESSION_LIFETIME=120";
                    }
                    File::put($envPath, $envContent);
                    $this->line('   ✅ SESSION_LIFETIME configurado como 120 minutos');
                } else {
                    $this->line('   ✅ SESSION_LIFETIME ya está configurado correctamente');
                }
            } else {
                $this->warn('   ⚠️  Archivo .env no encontrado');
            }

            // 5. Limpiar caché de configuración
            $this->line('🗑️  Limpiando caché...');
            $this->call('config:clear');
            $this->call('cache:clear');
            $this->line('   ✅ Caché limpiado');

            // 6. Verificar que las sesiones funcionan
            $this->line('🧪 Probando sesiones...');
            try {
                session_start();
                $_SESSION['test'] = 'hosting_test_' . time();
                $testValue = $_SESSION['test'];
                session_write_close();

                session_start();
                $retrievedValue = $_SESSION['test'] ?? null;
                session_destroy();

                if ($testValue === $retrievedValue) {
                    $this->line('   ✅ Sesiones funcionando correctamente');
                } else {
                    $this->warn('   ⚠️  Sesiones funcionando parcialmente');
                }
            } catch (Exception $e) {
                $this->error('   ❌ Error con sesiones: ' . $e->getMessage());
            }

            $this->line('');
            $this->info('✅ Configuración de sesiones completada');
            $this->line('');
            $this->line('📋 RESUMEN:');
            $this->line('   • Driver de sesiones: file');
            $this->line('   • Directorio: ' . $sessionPath);
            $this->line('   • Permisos: verificados');
            $this->line('   • Caché: limpiado');
            $this->line('');
            $this->line('🎯 Ahora puedes probar el sistema sin errores de cookies');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error configurando sesiones: ' . $e->getMessage());
            return 1;
        }
    }
}
