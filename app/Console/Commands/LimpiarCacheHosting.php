<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Exception;

class LimpiarCacheHosting extends Command
{
    protected $signature = 'limpiar:cache-hosting';
    protected $description = 'Limpiar todo el caché del servidor de hosting';

    public function handle()
    {
        $this->info('🧹 LIMPIANDO CACHÉ DEL SERVIDOR DE HOSTING');
        $this->line('');

        try {
            // 1. Limpiar caché de rutas
            $this->line('1️⃣ Limpiando caché de rutas...');
            Artisan::call('route:clear');
            $this->info('   ✅ Caché de rutas limpiado');

            // 2. Limpiar caché de configuración
            $this->line('2️⃣ Limpiando caché de configuración...');
            Artisan::call('config:clear');
            $this->info('   ✅ Caché de configuración limpiado');

            // 3. Limpiar caché de aplicación
            $this->line('3️⃣ Limpiando caché de aplicación...');
            Artisan::call('cache:clear');
            $this->info('   ✅ Caché de aplicación limpiado');

            // 4. Limpiar caché de vistas
            $this->line('4️⃣ Limpiando caché de vistas...');
            Artisan::call('view:clear');
            $this->info('   ✅ Caché de vistas limpiado');

            // 5. Limpiar caché de compilación
            $this->line('5️⃣ Limpiando caché de compilación...');
            Artisan::call('clear-compiled');
            $this->info('   ✅ Caché de compilación limpiado');

            // 6. Optimizar autoloader
            $this->line('6️⃣ Optimizando autoloader...');
            Artisan::call('optimize:clear');
            $this->info('   ✅ Autoloader optimizado');

            // 7. Verificar archivos de caché
            $this->line('7️⃣ Verificando archivos de caché...');
            $cachePaths = [
                storage_path('framework/cache'),
                storage_path('framework/views'),
                storage_path('framework/sessions'),
                storage_path('logs')
            ];

            foreach ($cachePaths as $path) {
                if (File::exists($path)) {
                    $this->line("   📁 {$path} - Existe");
                } else {
                    $this->warn("   ⚠️ {$path} - No existe");
                }
            }

            // 8. Verificar permisos
            $this->line('8️⃣ Verificando permisos...');
            $directories = [
                storage_path('framework/cache'),
                storage_path('framework/views'),
                storage_path('framework/sessions'),
                storage_path('logs')
            ];

            foreach ($directories as $dir) {
                if (File::exists($dir)) {
                    $permissions = substr(sprintf('%o', fileperms($dir)), -4);
                    $this->line("   📁 {$dir} - Permisos: {$permissions}");
                }
            }

            $this->line('');
            $this->info('🎉 ¡Caché del servidor limpiado completamente!');
            $this->line('');
            $this->line('💡 Ahora puedes probar la funcionalidad de edición de respuestas.');
            $this->line('💡 Si el problema persiste, reinicia el servidor web.');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error limpiando caché: ' . $e->getMessage());
            return 1;
        }
    }
}
