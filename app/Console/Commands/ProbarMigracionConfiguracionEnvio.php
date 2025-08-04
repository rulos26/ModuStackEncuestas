<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class ProbarMigracionConfiguracionEnvio extends Command
{
    protected $signature = 'probar:migracion-configuracion-envio';
    protected $description = 'Probar específicamente la migración de configuración de envíos';

    public function handle()
    {
        $this->info('🧪 PROBANDO MIGRACIÓN DE CONFIGURACIÓN DE ENVÍOS');
        $this->info('================================================');

        try {
            // Verificar si la tabla ya existe
            $tablaExiste = Schema::hasTable('configuracion_envios');

            if ($tablaExiste) {
                $this->warn('⚠️  La tabla configuracion_envios ya existe');

                // Mostrar información de la tabla existente
                $this->info('📋 Información de la tabla existente:');
                $columnas = Schema::getColumnListing('configuracion_envios');
                $this->table(['Columnas'], array_map(function($col) { return [$col]; }, $columnas));

                // Contar registros existentes
                $registros = DB::table('configuracion_envios')->count();
                $this->info("📊 Registros existentes: {$registros}");

                $this->question('¿Desea continuar con la prueba? (y/N)');
                if (!$this->confirm('¿Continuar?')) {
                    $this->info('❌ Prueba cancelada por el usuario');
                    return 0;
                }
            }

            // Ejecutar solo la migración específica
            $this->info('🔄 Ejecutando migración específica...');

            $output = Artisan::call('migrate', [
                '--path' => 'database/migrations/2025_07_31_150000_create_configuracion_envios_table.php',
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

                    // Verificar índices
                    $this->info('🔍 Verificando índices...');
                    $indices = DB::select("SHOW INDEX FROM configuracion_envios");
                    $indicesInfo = [];
                    foreach ($indices as $indice) {
                        $indicesInfo[] = [
                            $indice->Key_name,
                            $indice->Column_name,
                            $indice->Non_unique ? 'No único' : 'Único'
                        ];
                    }
                    $this->table(['Índice', 'Columna', 'Tipo'], $indicesInfo);

                    // Verificar foreign keys
                    $this->info('🔗 Verificando foreign keys...');
                    $foreignKeys = DB::select("
                        SELECT
                            CONSTRAINT_NAME,
                            COLUMN_NAME,
                            REFERENCED_TABLE_NAME,
                            REFERENCED_COLUMN_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'configuracion_envios'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");

                    if (!empty($foreignKeys)) {
                        $fkInfo = [];
                        foreach ($foreignKeys as $fk) {
                            $fkInfo[] = [
                                $fk->CONSTRAINT_NAME,
                                $fk->COLUMN_NAME,
                                $fk->REFERENCED_TABLE_NAME,
                                $fk->REFERENCED_COLUMN_NAME
                            ];
                        }
                        $this->table(['Constraint', 'Columna', 'Tabla Referenciada', 'Columna Referenciada'], $fkInfo);
                    } else {
                        $this->warn('⚠️  No se encontraron foreign keys');
                    }

                    // Probar inserción de datos de prueba
                    $this->info('🧪 Probando inserción de datos...');

                    // Verificar si existen empresas y encuestas
                    $empresas = DB::table('empresas')->count();
                    $encuestas = DB::table('encuestas')->count();

                    $this->info("📊 Empresas disponibles: {$empresas}");
                    $this->info("📊 Encuestas disponibles: {$encuestas}");

                    if ($empresas > 0 && $encuestas > 0) {
                        // Obtener primera empresa y encuesta
                        $empresa = DB::table('empresas')->first();
                        $encuesta = DB::table('encuestas')->first();

                        if ($empresa && $encuesta) {
                            // Insertar dato de prueba
                            $datoPrueba = [
                                'empresa_id' => $empresa->id,
                                'encuesta_id' => $encuesta->id,
                                'nombre_remitente' => 'Sistema de Prueba',
                                'correo_remitente' => 'prueba@test.com',
                                'asunto' => 'Encuesta de Prueba',
                                'cuerpo_mensaje' => 'Este es un mensaje de prueba para la encuesta.',
                                'tipo_envio' => 'automatico',
                                'plantilla' => null,
                                'activo' => true,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];

                            try {
                                DB::table('configuracion_envios')->insert($datoPrueba);
                                $this->info('✅ Datos de prueba insertados correctamente');

                                // Verificar inserción
                                $registros = DB::table('configuracion_envios')->count();
                                $this->info("📊 Total de registros después de la prueba: {$registros}");

                                // Limpiar datos de prueba
                                DB::table('configuracion_envios')->where('correo_remitente', 'prueba@test.com')->delete();
                                $this->info('🧹 Datos de prueba eliminados');

                            } catch (\Exception $e) {
                                $this->error('❌ Error al insertar datos de prueba: ' . $e->getMessage());
                            }
                        } else {
                            $this->warn('⚠️  No se encontraron empresas o encuestas para la prueba');
                        }
                    } else {
                        $this->warn('⚠️  No hay suficientes datos para la prueba (empresas: {$empresas}, encuestas: {$encuestas})');
                    }

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
            $this->error('📋 Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info('🎉 Prueba de migración completada exitosamente');
        return 0;
    }
}
