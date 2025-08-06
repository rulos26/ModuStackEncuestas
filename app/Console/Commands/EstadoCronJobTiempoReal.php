<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstadoCronJobTiempoReal extends Command
{
    protected $signature = 'cron-job:estado-tiempo-real {--intervalo=30 : Intervalo en segundos}';
    protected $description = 'Muestra el estado del Cron Job en tiempo real';

    public function handle()
    {
        $intervalo = $this->option('intervalo');

        $this->info('🔄 MONITOREO EN TIEMPO REAL DEL CRON JOB');
        $this->info("📊 Actualizando cada {$intervalo} segundos...");
        $this->info('Presiona Ctrl+C para salir');
        $this->line('');

        while (true) {
            $this->mostrarEstado();
            $this->line('');
            $this->line('─' . str_repeat('─', 80));
            $this->line('');

            sleep($intervalo);
        }
    }

    private function mostrarEstado()
    {
        $ahora = now();

        // Limpiar pantalla (funciona en algunos terminales)
        if (function_exists('system') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            system('clear');
        }

        $this->info("🕐 Última actualización: {$ahora->format('Y-m-d H:i:s')}");
        $this->line('');

        // 1. Estado del Cron Job
        $this->mostrarEstadoCronJob();

        // 2. Configuraciones programadas
        $this->mostrarConfiguracionesProgramadas();

        // 3. Sistema de colas
        $this->mostrarSistemaColas();

        // 4. Próximos envíos
        $this->mostrarProximosEnvios();
    }

    private function mostrarEstadoCronJob()
    {
        $this->info('📊 ESTADO DEL CRON JOB');

        // Verificar si el comando se ha ejecutado recientemente
        $logFile = storage_path('logs/laravel.log');
        $ultimaEjecucion = 'Nunca';

        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            $patron = '/verificar-envio-programado/';
            $matches = preg_match_all($patron, $logs);

            if ($matches > 0) {
                $ultimaEjecucion = "{$matches} veces en logs";
            }
        }

        $this->line("   📈 Ejecuciones detectadas: {$ultimaEjecucion}");

        // Verificar configuraciones ejecutadas en los últimos 5 minutos
        $configuracionesEjecutadas = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'en_proceso')
            ->where('updated_at', '>=', now()->subMinutes(5))
            ->count();

        $this->line("   📈 Ejecutadas en últimos 5 min: {$configuracionesEjecutadas}");

        if ($configuracionesEjecutadas > 0) {
            $this->info("   ✅ Cron Job funcionando correctamente");
        } else {
            $this->warn("   ⚠️ No hay actividad reciente del Cron Job");
        }
    }

    private function mostrarConfiguracionesProgramadas()
    {
        $this->info('📧 CONFIGURACIONES PROGRAMADAS');

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

        $this->line("   📈 Total: {$total} | Pendientes: {$pendientes} | En proceso: {$enProceso} | Completadas: {$completadas}");
    }

    private function mostrarSistemaColas()
    {
        $this->info('⏳ SISTEMA DE COLAS');

        $jobsPendientes = DB::table('jobs')->count();
        $jobsFallidos = DB::table('failed_jobs')->count();

        $this->line("   📈 Jobs pendientes: {$jobsPendientes}");
        $this->line("   📈 Jobs fallidos: {$jobsFallidos}");

        if ($jobsFallidos > 0) {
            $this->error("   ❌ Hay {$jobsFallidos} jobs fallidos");
        }

        if ($jobsPendientes > 0) {
            $this->warn("   ⚠️ Hay {$jobsPendientes} jobs pendientes");
        }
    }

    private function mostrarProximosEnvios()
    {
        $this->info('🚀 PRÓXIMOS ENVÍOS');

        $configuracionesPendientes = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'pendiente')
            ->where('activo', true)
            ->orderBy('fecha_envio')
            ->orderBy('hora_envio')
            ->limit(3)
            ->get();

        if ($configuracionesPendientes->count() > 0) {
            foreach ($configuracionesPendientes as $config) {
                $fechaHora = $config->fecha_envio . ' ' . $config->hora_envio;
                $fechaEnvio = Carbon::parse($fechaHora);
                $ahora = now();
                $diferencia = $ahora->diffForHumans($fechaEnvio);

                $this->line("   📋 ID: {$config->id} | Fecha: {$fechaHora} | {$diferencia}");
            }
        } else {
            $this->line("   📋 No hay envíos programados pendientes");
        }
    }
}
