<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class LimpiarMigracionesEncuestas extends Command
{
    protected $signature = 'migraciones:limpiar-encuestas {--ejecutar} {--backup}';
    protected $description = 'Limpia migraciones duplicadas de encuestas y ejecuta la migración consolidada';

    private $migracionesAEliminar = [
        '2025_07_13_041400_create_encuestas_table.php',
        '2025_07_23_000003_add_slug_habilitada_to_encuestas_table.php',
        '2025_07_25_000000_mejorar_campos_encuesta.php',
        '2025_07_13_080000_cambiar_fechas_a_date.php',
        '2025_07_13_070000_eliminar_tiempo_disponible_legacy.php',
        '2025_07_13_091000_agregar_fechas_encuestas.php',
        '2025_07_23_000000_create_preguntas_table.php',
        '2025_07_24_000001_add_campos_adicionales_preguntas.php',
        '2025_07_24_000002_finalize_preguntas_tipos.php',
        '2025_07_13_090000_actualizar_tabla_preguntas.php',
        '2025_07_23_000001_create_respuestas_table.php',
        '2025_07_23_000002_create_logicas_table.php',
        '2025_07_23_000004_create_respuestas_usuario_table.php',
        '2025_07_13_050000_create_bloques_envio_table.php',
        '2025_07_13_060000_create_tokens_encuesta_table.php'
    ];

    public function handle()
    {
        $this->info('🧹 LIMPIEZA DE MIGRACIONES DE ENCUESTAS');
        $this->info('=====================================');

        try {
            // Verificar conexión a la base de datos
            $this->verificarConexionBD();

            // Analizar migraciones existentes
            $this->analizarMigracionesExistentes();

            // Crear backup si se solicita
            if ($this->option('backup')) {
                $this->crearBackup();
            }

            // Si se solicita ejecutar la limpieza
            if ($this->option('ejecutar')) {
                $this->ejecutarLimpieza();
            } else {
                $this->mostrarRecomendaciones();
            }

        } catch (\Exception $e) {
            $this->error('❌ Error durante la limpieza: ' . $e->getMessage());
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

    private function analizarMigracionesExistentes()
    {
        $this->info("\n📋 ANALIZANDO MIGRACIONES EXISTENTES:");

        $migrationsPath = database_path('migrations');
        $archivosExistentes = [];
        $archivosAEliminar = [];

        // Obtener todos los archivos de migración
        $archivos = File::files($migrationsPath);

        foreach ($archivos as $archivo) {
            $nombreArchivo = $archivo->getFilename();
            $archivosExistentes[] = $nombreArchivo;

            if (in_array($nombreArchivo, $this->migracionesAEliminar)) {
                $archivosAEliminar[] = $nombreArchivo;
            }
        }

        $this->info("   📁 Total de migraciones encontradas: " . count($archivosExistentes));
        $this->info("   🗑️  Migraciones a eliminar: " . count($archivosAEliminar));

        if (count($archivosAEliminar) > 0) {
            $this->warn("   📝 Archivos que serán eliminados:");
            foreach ($archivosAEliminar as $archivo) {
                $this->line("      - {$archivo}");
            }
        }

        // Verificar si existe la migración consolidada
        $migracionConsolidada = '2025_07_13_100000_create_sistema_encuestas_completo.php';
        if (in_array($migracionConsolidada, $archivosExistentes)) {
            $this->info("   ✅ Migración consolidada encontrada: {$migracionConsolidada}");
        } else {
            $this->error("   ❌ Migración consolidada no encontrada: {$migracionConsolidada}");
        }
    }

    private function crearBackup()
    {
        $this->info("\n💾 CREANDO BACKUP DE MIGRACIONES:");

        $migrationsPath = database_path('migrations');
        $backupPath = database_path('migrations_backup_' . date('Y-m-d_H-i-s'));

        try {
            // Crear directorio de backup
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            // Copiar archivos a eliminar al backup
            $archivosCopiados = 0;
            foreach ($this->migracionesAEliminar as $archivo) {
                $archivoOrigen = $migrationsPath . '/' . $archivo;
                $archivoDestino = $backupPath . '/' . $archivo;

                if (File::exists($archivoOrigen)) {
                    File::copy($archivoOrigen, $archivoDestino);
                    $archivosCopiados++;
                }
            }

            $this->info("   ✅ Backup creado en: {$backupPath}");
            $this->info("   📁 Archivos copiados: {$archivosCopiados}");

        } catch (\Exception $e) {
            $this->error("   ❌ Error creando backup: " . $e->getMessage());
        }
    }

    private function ejecutarLimpieza()
    {
        $this->info("\n🧹 EJECUTANDO LIMPIEZA:");

        if ($this->confirm('¿Estás seguro de que quieres eliminar las migraciones duplicadas? Esto es irreversible.')) {
            try {
                $migrationsPath = database_path('migrations');
                $archivosEliminados = 0;

                // Eliminar archivos duplicados
                foreach ($this->migracionesAEliminar as $archivo) {
                    $archivoPath = $migrationsPath . '/' . $archivo;

                    if (File::exists($archivoPath)) {
                        File::delete($archivoPath);
                        $archivosEliminados++;
                        $this->info("   🗑️  Eliminado: {$archivo}");
                    }
                }

                $this->info("   ✅ Total de archivos eliminados: {$archivosEliminados}");

                // Ejecutar la migración consolidada
                $this->ejecutarMigracionConsolidada();

            } catch (\Exception $e) {
                $this->error("   ❌ Error durante la limpieza: " . $e->getMessage());
            }
        } else {
            $this->info("   ⏸️  Limpieza cancelada por el usuario");
        }
    }

    private function ejecutarMigracionConsolidada()
    {
        $this->info("\n🚀 EJECUTANDO MIGRACIÓN CONSOLIDADA:");

        try {
            // Verificar si las tablas existen
            $tablasExistentes = $this->verificarTablasExistentes();

            if (!empty($tablasExistentes)) {
                $this->warn("   ⚠️  Las siguientes tablas ya existen:");
                foreach ($tablasExistentes as $tabla) {
                    $this->line("      - {$tabla}");
                }

                if ($this->confirm('¿Quieres eliminar las tablas existentes y recrearlas con la nueva estructura?')) {
                    $this->eliminarTablasExistentes($tablasExistentes);
                } else {
                    $this->info("   ⏸️  Migración cancelada. Las tablas existentes se mantienen.");
                    return;
                }
            }

            // Ejecutar la migración consolidada
            $this->info("   🚀 Ejecutando migración consolidada...");
            $this->call('migrate', [
                '--path' => 'database/migrations/2025_07_13_100000_create_sistema_encuestas_completo.php',
                '--force' => true
            ]);

            $this->info("   ✅ Migración consolidada ejecutada exitosamente");

            // Verificar el resultado
            $this->verificarResultadoMigracion();

        } catch (\Exception $e) {
            $this->error("   ❌ Error ejecutando migración consolidada: " . $e->getMessage());
        }
    }

    private function verificarTablasExistentes()
    {
        $tablasEncuestas = [
            'encuestas',
            'preguntas',
            'respuestas',
            'logicas',
            'respuestas_usuario',
            'bloques_envio',
            'tokens_encuesta'
        ];

        $tablasExistentes = [];
        foreach ($tablasEncuestas as $tabla) {
            if (Schema::hasTable($tabla)) {
                $tablasExistentes[] = $tabla;
            }
        }

        return $tablasExistentes;
    }

    private function eliminarTablasExistentes($tablas)
    {
        $this->info("   🗑️  Eliminando tablas existentes...");

        // Eliminar en orden inverso para respetar foreign keys
        $ordenEliminacion = [
            'tokens_encuesta',
            'bloques_envio',
            'respuestas_usuario',
            'logicas',
            'respuestas',
            'preguntas',
            'encuestas'
        ];

        foreach ($ordenEliminacion as $tabla) {
            if (in_array($tabla, $tablas)) {
                Schema::dropIfExists($tabla);
                $this->info("      - Tabla '{$tabla}' eliminada");
            }
        }
    }

    private function verificarResultadoMigracion()
    {
        $this->info("\n✅ VERIFICANDO RESULTADO DE LA MIGRACIÓN:");

        $tablasEsperadas = [
            'encuestas' => ['id', 'slug', 'titulo', 'fecha_inicio', 'fecha_fin'],
            'preguntas' => ['id', 'encuesta_id', 'texto', 'tipo', 'descripcion'],
            'respuestas' => ['id', 'pregunta_id', 'texto'],
            'logicas' => ['id', 'pregunta_id', 'respuesta_id'],
            'respuestas_usuario' => ['id', 'encuesta_id', 'pregunta_id'],
            'bloques_envio' => ['id', 'encuesta_id', 'numero_bloque'],
            'tokens_encuesta' => ['id', 'encuesta_id', 'token_acceso']
        ];

        foreach ($tablasEsperadas as $tabla => $columnas) {
            if (Schema::hasTable($tabla)) {
                $this->info("   ✅ Tabla '{$tabla}' creada correctamente");

                // Verificar columnas principales
                $columnasExistentes = Schema::getColumnListing($tabla);
                foreach ($columnas as $columna) {
                    if (in_array($columna, $columnasExistentes)) {
                        $this->line("      - Columna '{$columna}' ✓");
                    } else {
                        $this->warn("      - Columna '{$columna}' ❌");
                    }
                }
            } else {
                $this->error("   ❌ Tabla '{$tabla}' no fue creada");
            }
        }
    }

    private function mostrarRecomendaciones()
    {
        $this->info("\n💡 RECOMENDACIONES:");
        $this->info("   📌 Para ejecutar la limpieza automática:");
        $this->info("      php artisan migraciones:limpiar-encuestas --ejecutar");
        $this->info("");
        $this->info("   📌 Para crear backup antes de limpiar:");
        $this->info("      php artisan migraciones:limpiar-encuestas --ejecutar --backup");
        $this->info("");
        $this->info("   📌 Para ejecutar desde el módulo visual:");
        $this->info("      1. Ve a: Diagnósticos → Herramientas del Sistema → Gestión de Migraciones");
        $this->info("      2. Haz clic en: 'Ejecutar Migraciones'");
        $this->info("");
        $this->info("   ⚠️  IMPORTANTE:");
        $this->info("      - Esta operación eliminará migraciones duplicadas");
        $this->info("      - Se creará una estructura consolidada y optimizada");
        $this->info("      - Si tienes datos importantes, crea un backup primero");
    }
}
