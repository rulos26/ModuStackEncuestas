<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerificarMigracionesPreguntas extends Command
{
    protected $signature = 'preguntas:verificar-migraciones {--ejecutar}';
    protected $description = 'Verifica y ejecuta las migraciones necesarias para preguntas';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO MIGRACIONES DE PREGUNTAS');
        $this->info('=======================================');

        try {
            // Verificar si existe la tabla preguntas
            $this->verificarTablaPreguntas();

            // Verificar estructura actual
            $this->verificarEstructuraActual();

            // Verificar si necesita actualización
            $this->verificarNecesitaActualizacion();

            // Ejecutar migración si se solicita
            if ($this->option('ejecutar')) {
                $this->ejecutarMigracion();
            }

            $this->info("\n🎉 VERIFICACIÓN COMPLETADA");
            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ ERROR DURANTE LA VERIFICACIÓN:");
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function verificarTablaPreguntas()
    {
        $this->info("\n📋 VERIFICANDO TABLA PREGUNTAS:");

        if (!Schema::hasTable('preguntas')) {
            $this->error("   ❌ Tabla 'preguntas' no existe");
            $this->info("   💡 Ejecuta: php artisan migrate");
            throw new \Exception('La tabla preguntas no existe. Ejecuta las migraciones.');
        }

        $count = DB::table('preguntas')->count();
        $this->info("   ✅ Tabla 'preguntas' existe con {$count} registros");
    }

    private function verificarEstructuraActual()
    {
        $this->info("\n📊 ESTRUCTURA ACTUAL DE LA TABLA:");

        $columnas = Schema::getColumnListing('preguntas');

        foreach ($columnas as $columna) {
            $tipo = Schema::getColumnType('preguntas', $columna);
            $this->info("   • {$columna}: {$tipo}");
        }

        // Verificar columnas requeridas
        $columnasRequeridas = [
            'id', 'encuesta_id', 'texto', 'tipo', 'orden', 'obligatoria'
        ];

        $columnasFaltantes = [];
        foreach ($columnasRequeridas as $columna) {
            if (!in_array($columna, $columnas)) {
                $columnasFaltantes[] = $columna;
            }
        }

        if (!empty($columnasFaltantes)) {
            $this->error("   ❌ Columnas faltantes: " . implode(', ', $columnasFaltantes));
        } else {
            $this->info("   ✅ Todas las columnas básicas están presentes");
        }
    }

    private function verificarNecesitaActualizacion()
    {
        $this->info("\n🔄 VERIFICANDO SI NECESITA ACTUALIZACIÓN:");

        $columnas = Schema::getColumnListing('preguntas');

        // Verificar columnas nuevas que deberían estar
        $columnasNuevas = [
            'descripcion', 'placeholder', 'min_caracteres', 'max_caracteres',
            'escala_min', 'escala_max', 'escala_etiqueta_min', 'escala_etiqueta_max',
            'tipos_archivo_permitidos', 'tamano_max_archivo',
            'latitud_default', 'longitud_default', 'zoom_default',
            'condiciones_mostrar', 'logica_salto', 'opciones_filas', 'opciones_columnas'
        ];

        $columnasFaltantes = [];
        foreach ($columnasNuevas as $columna) {
            if (!in_array($columna, $columnas)) {
                $columnasFaltantes[] = $columna;
            }
        }

        if (!empty($columnasFaltantes)) {
            $this->warn("   ⚠️ Faltan columnas nuevas: " . implode(', ', $columnasFaltantes));
            $this->info("   💡 Ejecuta: php artisan preguntas:verificar-migraciones --ejecutar");
            return true;
        } else {
            $this->info("   ✅ La tabla está actualizada");
            return false;
        }
    }

    private function ejecutarMigracion()
    {
        $this->info("\n🚀 EJECUTANDO MIGRACIÓN:");

        try {
            // Ejecutar la migración específica
            $this->call('migrate', [
                '--path' => 'database/migrations/2025_07_13_090000_actualizar_tabla_preguntas.php'
            ]);

            $this->info("   ✅ Migración ejecutada exitosamente");

            // Verificar estructura después de la migración
            $this->verificarEstructuraActual();

        } catch (\Exception $e) {
            $this->error("   ❌ Error ejecutando migración: " . $e->getMessage());
            throw $e;
        }
    }
}
