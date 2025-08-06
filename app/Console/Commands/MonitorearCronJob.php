<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use App\Jobs\EnviarCorreosProgramados;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitorearCronJob extends Command
{
    protected $signature = 'monitorear:cron-job {--ultimas=10 : Número de minutos a revisar}';
    protected $description = 'Monitorea el estado del Cron Job y verifica por qué no se envían emails';

    public function handle()
    {
        $this->info('🔍 MONITOREANDO CRON JOB Y SISTEMA DE ENVÍOS');
        $this->line('');

        $ultimasMinutos = $this->option('ultimas');
        $fechaInicio = now()->subMinutes($ultimasMinutos);

        // 1. Verificar ejecuciones del Cron Job
        $this->verificarEjecucionesCronJob($fechaInicio);

        // 2. Verificar configuraciones programadas
        $this->verificarConfiguracionesProgramadas();

        // 3. Verificar sistema de colas
        $this->verificarSistemaColas();

        // 4. Verificar logs de envío
        $this->verificarLogsEnvio($fechaInicio);

        // 5. Verificar configuración de email
        $this->verificarConfiguracionEmail();

        $this->info('✅ MONITOREO COMPLETADO');
    }

    private function verificarEjecucionesCronJob($fechaInicio)
    {
        $this->info('📊 1. Verificando ejecuciones del Cron Job...');

        // Buscar en logs si se ha ejecutado el comando
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            $patron = '/verificar-envio-programado/';
            $matches = preg_match_all($patron, $logs);

            $this->info("   📈 Veces que aparece 'verificar-envio-programado' en logs: {$matches}");
        }

        // Verificar si hay configuraciones que deberían haberse ejecutado
        $configuracionesEjecutadas = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'en_proceso')
            ->where('updated_at', '>=', $fechaInicio)
            ->count();

        $this->info("   📈 Configuraciones ejecutadas en los últimos {$this->option('ultimas')} minutos: {$configuracionesEjecutadas}");

        if ($configuracionesEjecutadas == 0) {
            $this->warn("   ⚠️ No se han ejecutado configuraciones recientemente");
        }
    }

    private function verificarConfiguracionesProgramadas()
    {
        $this->info('📧 2. Verificando configuraciones programadas...');

        $total = ConfiguracionEnvio::where('tipo_envio', 'programado')->count();
        $pendientes = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'pendiente')
            ->count();
        $enProceso = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'en_proceso')
            ->count();
        $completadas = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'completado')
            ->count();

        $this->info("   📈 Total configuraciones programadas: {$total}");
        $this->info("   📈 Configuraciones pendientes: {$pendientes}");
        $this->info("   📈 Configuraciones en proceso: {$enProceso}");
        $this->info("   📈 Configuraciones completadas: {$completadas}");

        // Mostrar configuraciones que deberían ejecutarse
        $configuracionesPendientes = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'pendiente')
            ->where('activo', true)
            ->get();

        if ($configuracionesPendientes->count() > 0) {
            $this->info("   📋 Configuraciones pendientes que deberían ejecutarse:");
            foreach ($configuracionesPendientes as $config) {
                try {
                    $fechaHora = $config->fecha_envio . ' ' . $config->hora_envio;
                    $fechaEnvio = Carbon::parse($fechaHora);
                    $ahora = now();

                    $this->line("      • ID: {$config->id} | Fecha: {$fechaHora}");
                    $this->line("        Estado: {$config->estado_programacion} | ¿Lista?: " . ($fechaEnvio <= $ahora ? '✅ SÍ' : '⏳ NO'));
                } catch (\Exception $e) {
                    $this->line("      • ID: {$config->id} | Error en fecha: {$config->fecha_envio} {$config->hora_envio}");
                }
            }
        }
    }

    private function verificarSistemaColas()
    {
        $this->info('⏳ 3. Verificando sistema de colas...');

        $jobsPendientes = DB::table('jobs')->count();
        $jobsFallidos = DB::table('failed_jobs')->count();

        $this->info("   📈 Jobs pendientes: {$jobsPendientes}");
        $this->info("   📈 Jobs fallidos: {$jobsFallidos}");

        if ($jobsFallidos > 0) {
            $this->error("   ❌ Hay {$jobsFallidos} jobs fallidos");

            $jobsFallidosDetalle = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(3)
                ->get();

            foreach ($jobsFallidosDetalle as $job) {
                $this->line("      • ID: {$job->id} | Error: {$job->exception}");
            }
        }

        if ($jobsPendientes > 0) {
            $this->warn("   ⚠️ Hay {$jobsPendientes} jobs pendientes");
        }
    }

    private function verificarLogsEnvio($fechaInicio)
    {
        $this->info('📝 4. Verificando logs de envío...');

        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);

            // Buscar logs relacionados con envío de emails
            $patrones = [
                'EnviarCorreosProgramados' => 'Jobs de envío',
                'Mail::send' => 'Envíos de correo',
                'SMTP' => 'Errores SMTP',
                'Connection refused' => 'Errores de conexión',
                'Authentication failed' => 'Errores de autenticación'
            ];

            foreach ($patrones as $patron => $descripcion) {
                $matches = preg_match_all("/{$patron}/i", $logs);
                $this->info("   📈 {$descripcion}: {$matches} ocurrencias");
            }

            // Buscar errores recientes
            $lineas = file($logFile);
            $erroresRecientes = [];

            foreach (array_reverse($lineas) as $linea) {
                if (strpos($linea, 'ERROR') !== false || strpos($linea, 'Exception') !== false) {
                    $erroresRecientes[] = trim($linea);
                    if (count($erroresRecientes) >= 5) break;
                }
            }

            if (!empty($erroresRecientes)) {
                $this->warn("   ⚠️ Errores recientes encontrados:");
                foreach ($erroresRecientes as $error) {
                    $this->line("      • " . substr($error, 0, 100) . "...");
                }
            }
        }
    }

    private function verificarConfiguracionEmail()
    {
        $this->info('📧 5. Verificando configuración de email...');

        $configuracion = config('mail');

        $this->info("   📋 Driver de correo: " . ($configuracion['default'] ?? 'No configurado'));
        $this->info("   📋 Host SMTP: " . ($configuracion['mailers']['smtp']['host'] ?? 'No configurado'));
        $this->info("   📋 Puerto SMTP: " . ($configuracion['mailers']['smtp']['port'] ?? 'No configurado'));
        $this->info("   📋 Usuario SMTP: " . ($configuracion['mailers']['smtp']['username'] ?? 'No configurado'));

        // Verificar si hay configuración de correo
        if (empty($configuracion['mailers']['smtp']['host'])) {
            $this->error("   ❌ No hay configuración SMTP");
        } else {
            $this->info("   ✅ Configuración SMTP encontrada");
        }
    }
}
