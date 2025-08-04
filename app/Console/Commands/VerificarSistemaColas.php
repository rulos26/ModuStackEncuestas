<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class VerificarSistemaColas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verificar:sistema-colas {--fix : Intentar arreglar problemas encontrados}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el estado del sistema de colas y jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 VERIFICANDO SISTEMA DE COLAS Y JOBS');
        $this->line('');

        // 1. Verificar tabla de jobs
        $this->verificarTablaJobs();

        // 2. Verificar configuración de colas
        $this->verificarConfiguracionColas();

        // 3. Verificar jobs fallidos
        $this->verificarJobsFallidos();

        // 4. Verificar jobs pendientes
        $this->verificarJobsPendientes();

        // 5. Verificar conexión a Redis (si se usa)
        $this->verificarRedis();

        $this->info('✅ VERIFICACIÓN COMPLETADA');
    }

    private function verificarTablaJobs()
    {
        $this->info('📊 1. Verificando tabla de jobs...');

        try {
            // Verificar si la tabla existe
            $tablaExiste = DB::getSchemaBuilder()->hasTable('jobs');

            if (!$tablaExiste) {
                $this->error('   ❌ La tabla "jobs" no existe');
                if ($this->option('fix')) {
                    $this->info('   🔧 Ejecutando migración de jobs...');
                    $this->call('queue:table');
                    $this->call('migrate');
                }
                return;
            }

            $this->info('   ✅ Tabla "jobs" existe');

            // Verificar estructura de la tabla
            $columnas = DB::getSchemaBuilder()->getColumnListing('jobs');
            $columnasRequeridas = ['id', 'queue', 'payload', 'attempts', 'reserved_at', 'available_at', 'created_at'];

            foreach ($columnasRequeridas as $columna) {
                if (!in_array($columna, $columnas)) {
                    $this->error("   ❌ Columna '{$columna}' no existe en tabla jobs");
                }
            }

            $this->info('   ✅ Estructura de tabla correcta');

        } catch (\Exception $e) {
            $this->error('   ❌ Error verificando tabla jobs: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarConfiguracionColas()
    {
        $this->info('⚙️ 2. Verificando configuración de colas...');

        try {
            $driver = config('queue.default');
            $this->info("   📋 Driver de cola: {$driver}");

            $conexiones = config('queue.connections');
            $conexionActual = $conexiones[$driver] ?? null;

            if ($conexionActual) {
                $this->info("   📋 Conexión configurada correctamente");

                if ($driver === 'database') {
                    $this->info("   📋 Usando base de datos para colas");
                } elseif ($driver === 'redis') {
                    $this->info("   📋 Usando Redis para colas");
                } elseif ($driver === 'sync') {
                    $this->warn("   ⚠️ Usando driver 'sync' - los jobs se ejecutan inmediatamente");
                }
            } else {
                $this->error("   ❌ Configuración de conexión no encontrada para driver: {$driver}");
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error verificando configuración: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarJobsFallidos()
    {
        $this->info('❌ 3. Verificando jobs fallidos...');

        try {
            // Verificar si la tabla failed_jobs existe
            $tablaExiste = DB::getSchemaBuilder()->hasTable('failed_jobs');

            if (!$tablaExiste) {
                $this->warn('   ⚠️ Tabla "failed_jobs" no existe');
                if ($this->option('fix')) {
                    $this->info('   🔧 Creando tabla failed_jobs...');
                    $this->call('queue:failed-table');
                    $this->call('migrate');
                }
                return;
            }

            $jobsFallidos = DB::table('failed_jobs')->count();
            $this->info("   📈 Jobs fallidos: {$jobsFallidos}");

            if ($jobsFallidos > 0) {
                $this->warn("   ⚠️ Hay {$jobsFallidos} jobs fallidos");

                // Mostrar algunos jobs fallidos
                $ultimosFallidos = DB::table('failed_jobs')
                    ->orderBy('failed_at', 'desc')
                    ->take(3)
                    ->get();

                foreach ($ultimosFallidos as $job) {
                    $this->line("      • ID: {$job->id} | Queue: {$job->queue} | Fecha: {$job->failed_at}");
                    $this->line("        Error: " . substr($job->exception, 0, 100) . "...");
                }

                if ($this->option('fix')) {
                    $this->info('   🔧 Limpiando jobs fallidos...');
                    $this->call('queue:flush');
                }
            } else {
                $this->info('   ✅ No hay jobs fallidos');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error verificando jobs fallidos: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarJobsPendientes()
    {
        $this->info('⏳ 4. Verificando jobs pendientes...');

        try {
            $jobsPendientes = DB::table('jobs')->count();
            $this->info("   📈 Jobs pendientes: {$jobsPendientes}");

            if ($jobsPendientes > 0) {
                $this->info('   📋 Detalles de jobs pendientes:');

                $jobs = DB::table('jobs')
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();

                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);
                    $jobClass = $payload['displayName'] ?? 'Desconocido';

                    $this->line("      • ID: {$job->id} | Queue: {$job->queue} | Job: {$jobClass}");
                    $this->line("        Creado: {$job->created_at} | Intentos: {$job->attempts}");
                }
            } else {
                $this->info('   ✅ No hay jobs pendientes');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error verificando jobs pendientes: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarRedis()
    {
        $this->info('🔴 5. Verificando Redis...');

        try {
            $driver = config('queue.default');

            if ($driver === 'redis') {
                $redis = Redis::connection();
                $redis->ping();
                $this->info('   ✅ Conexión a Redis exitosa');

                // Verificar colas en Redis
                $colas = $redis->keys('queues:*');
                $this->info("   📈 Colas encontradas: " . count($colas));

                foreach ($colas as $cola) {
                    $longitud = $redis->lLen($cola);
                    $this->line("      • {$cola}: {$longitud} jobs");
                }
            } else {
                $this->info("   ℹ️ No se usa Redis (driver actual: {$driver})");
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error verificando Redis: ' . $e->getMessage());
        }

        $this->line('');
    }
}
