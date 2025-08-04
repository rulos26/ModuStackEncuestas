<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ProbarMigracionSimple extends Command
{
    protected $signature = 'probar:migracion-simple';
    protected $description = 'Probar la migración de configuración de envíos de forma simple';

    public function handle()
    {
        $this->info('🧪 PROBANDO MIGRACIÓN SIMPLE');
        $this->info('============================');

        try {
            // Verificar si la tabla ya existe
            $tablaExiste = Schema::hasTable('configuracion_envios');

            if ($tablaExiste) {
                $this->warn('⚠️  La tabla configuracion_envios ya existe');
                $this->info('ℹ️  Continuando con la prueba...');
            }

            // Ejecutar migración
            $this->info('🔄 Ejecutando migración...');

            $output = Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_07_31_160000_create_configuracion_envios_simple_table.php',
                '--force' => true
            ]);

            if ($output === 0) {
                $this->info('✅ Migración ejecutada exitosamente');

                // Verificar que la tabla se creó correctamente
                if (Schema::hasTable('configuracion_envios')) {
                    $this->info('✅ Tabla configuracion_envios creada correctamente');

                    // Mostrar estructura de la tabla
                    $this->info('📋 Estructura de la tabla:');
                    $columnas = Schema::getColumnListing('configuracion_envios');
                    $this->table(['Columnas'], array_map(function($col) { return [$col]; }, $columnas));

                    // Contar registros
                    $registros = DB::table('configuracion_envios')->count();
                    $this->info("📊 Registros en la tabla: {$registros}");

                    $this->info('🎉 Prueba completada exitosamente');
                } else {
                    $this->error('❌ La tabla no se creó correctamente');
                    return 1;
                }

            } else {
                $this->error('❌ Error al ejecutar la migración');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('❌ Error durante la prueba: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
