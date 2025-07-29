<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiagnosticarFechasEncuestas extends Command
{
    protected $signature = 'encuestas:diagnosticar-fechas {--ejecutar}';
    protected $description = 'Diagnostica y soluciona problemas con las columnas de fecha en la tabla encuestas';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO DE COLUMNAS DE FECHA EN ENCUESTAS');
        $this->info('================================================');

        try {
            // Verificar conexión a la base de datos
            $this->verificarConexionBD();

            // Verificar estructura de la tabla encuestas
            $this->verificarEstructuraTabla();

            // Verificar columnas específicas
            $this->verificarColumnasFecha();

            // Si se solicita ejecutar la corrección
            if ($this->option('ejecutar')) {
                $this->ejecutarCorreccion();
            } else {
                $this->mostrarRecomendaciones();
            }

        } catch (\Exception $e) {
            $this->error('❌ Error durante el diagnóstico: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function verificarConexionBD()
    {
        $this->info("\n📊 VERIFICANDO CONEXIÓN A BASE DE DATOS:");

        try {
            DB::connection()->getPdo();
            $this->info("   ✅ Conexión a base de datos exitosa");
            $this->info("   📍 Base de datos: " . DB::connection()->getDatabaseName());
        } catch (\Exception $e) {
            $this->error("   ❌ Error de conexión: " . $e->getMessage());
            throw $e;
        }
    }

    private function verificarEstructuraTabla()
    {
        $this->info("\n📋 VERIFICANDO ESTRUCTURA DE LA TABLA ENCUESTAS:");

        if (!Schema::hasTable('encuestas')) {
            $this->error("   ❌ La tabla 'encuestas' no existe");
            $this->warn("   💡 Ejecuta: php artisan migrate");
            return;
        }

        $this->info("   ✅ La tabla 'encuestas' existe");

        // Obtener todas las columnas
        $columnas = Schema::getColumnListing('encuestas');
        $this->info("   📝 Columnas encontradas: " . count($columnas));

        foreach ($columnas as $columna) {
            $this->line("      - {$columna}");
        }
    }

    private function verificarColumnasFecha()
    {
        $this->info("\n📅 VERIFICANDO COLUMNAS DE FECHA:");

        $columnas = Schema::getColumnListing('encuestas');

        // Verificar fecha_inicio
        if (in_array('fecha_inicio', $columnas)) {
            $tipo = $this->obtenerTipoColumna('encuestas', 'fecha_inicio');
            $this->info("   ✅ Campo 'fecha_inicio' existe (tipo: {$tipo})");

            if ($tipo !== 'date') {
                $this->warn("   ⚠️  Campo 'fecha_inicio' debería ser tipo 'date', actualmente es '{$tipo}'");
            }
        } else {
            $this->error("   ❌ Campo 'fecha_inicio' no existe");
        }

        // Verificar fecha_fin
        if (in_array('fecha_fin', $columnas)) {
            $tipo = $this->obtenerTipoColumna('encuestas', 'fecha_fin');
            $this->info("   ✅ Campo 'fecha_fin' existe (tipo: {$tipo})");

            if ($tipo !== 'date') {
                $this->warn("   ⚠️  Campo 'fecha_fin' debería ser tipo 'date', actualmente es '{$tipo}'");
            }
        } else {
            $this->error("   ❌ Campo 'fecha_fin' no existe");
        }

        // Verificar tiempo_disponible (legacy)
        if (in_array('tiempo_disponible', $columnas)) {
            $this->warn("   ⚠️  Campo 'tiempo_disponible' (legacy) aún existe y debería ser eliminado");
        } else {
            $this->info("   ✅ Campo 'tiempo_disponible' (legacy) ha sido eliminado correctamente");
        }
    }

    private function obtenerTipoColumna($tabla, $columna)
    {
        try {
            $resultado = DB::select("SHOW COLUMNS FROM {$tabla} WHERE Field = ?", [$columna]);
            if (!empty($resultado)) {
                return $resultado[0]->Type;
            }
        } catch (\Exception $e) {
            $this->warn("   ⚠️  No se pudo obtener el tipo de la columna {$columna}");
        }
        return 'unknown';
    }

    private function ejecutarCorreccion()
    {
        $this->info("\n🔧 EJECUTANDO CORRECCIÓN:");

        if ($this->confirm('¿Estás seguro de que quieres ejecutar la corrección? Esto modificará la estructura de la tabla.')) {
            try {
                $this->info("   🚀 Ejecutando migración de corrección...");

                // Ejecutar la migración específica
                $this->call('migrate', [
                    '--path' => 'database/migrations/2025_07_13_091000_agregar_fechas_encuestas.php',
                    '--force' => true
                ]);

                $this->info("   ✅ Corrección ejecutada exitosamente");

                // Verificar el resultado
                $this->verificarColumnasFecha();

            } catch (\Exception $e) {
                $this->error("   ❌ Error durante la corrección: " . $e->getMessage());
            }
        } else {
            $this->info("   ⏸️  Corrección cancelada por el usuario");
        }
    }

    private function mostrarRecomendaciones()
    {
        $this->info("\n💡 RECOMENDACIONES:");
        $this->info("   📌 Para ejecutar la corrección automática:");
        $this->info("      php artisan encuestas:diagnosticar-fechas --ejecutar");
        $this->info("");
        $this->info("   📌 Para ejecutar desde el módulo visual:");
        $this->info("      1. Ve a: Diagnósticos → Herramientas del Sistema → Gestión de Migraciones");
        $this->info("      2. Haz clic en: 'Agregar Campos de Fecha'");
        $this->info("");
        $this->info("   📌 Para verificar el estado después de la corrección:");
        $this->info("      php artisan encuestas:diagnosticar-fechas");
    }
}
