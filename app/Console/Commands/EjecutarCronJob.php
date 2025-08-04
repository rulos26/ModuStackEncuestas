<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use App\Jobs\EnviarCorreosProgramados;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EjecutarCronJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ejecutar:cron-job {--force : Forzar ejecución sin verificar fecha/hora}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta manualmente el cron job de verificación de envíos programados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 EJECUTANDO CRON JOB DE VERIFICACIÓN DE ENVÍOS PROGRAMADOS');
        $this->line('');

        $forzar = $this->option('force');
        $ahora = now();

        $this->info("🕐 Hora actual: {$ahora->format('Y-m-d H:i:s')}");
        $this->line('');

        // Buscar configuraciones programadas pendientes
        $query = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'pendiente')
            ->where('activo', true);

        if (!$forzar) {
            $query->where(function($q) use ($ahora) {
                $q->whereRaw("CONCAT(fecha_envio, ' ', hora_envio) <= ?", [$ahora->format('Y-m-d H:i:s')]);
            });
        }

        $configuraciones = $query->with(['encuesta', 'empresa'])->get();

        $this->info("📈 Configuraciones encontradas: {$configuraciones->count()}");

        if ($configuraciones->isEmpty()) {
            $this->warn('⚠️ No hay configuraciones programadas para ejecutar');
            if (!$forzar) {
                $this->info('💡 Usa --force para forzar la ejecución de todas las configuraciones');
            }
            return;
        }

        $enviadas = 0;
        $errores = 0;

        foreach ($configuraciones as $configuracion) {
            $this->line('');
            $this->info("🎯 Procesando configuración ID: {$configuracion->id}");

            try {
                $fechaHoraEnvio = $configuracion->fecha_envio . ' ' . $configuracion->hora_envio;
                $fechaEnvio = Carbon::parse($fechaHoraEnvio);

                $this->line("   📋 Encuesta: {$configuracion->encuesta->titulo}");
                $this->line("   📋 Empresa: {$configuracion->empresa->nombre}");
                $this->line("   📋 Fecha/Hora programada: {$fechaEnvio->format('Y-m-d H:i:s')}");
                $this->line("   📋 Destinatarios: {$configuracion->tipo_destinatario}");

                if (!$forzar && $fechaEnvio > $ahora) {
                    $this->warn("   ⏳ No es momento de enviar (programado para el futuro)");
                    continue;
                }

                // Marcar como en proceso
                $configuracion->update(['estado_programacion' => 'en_proceso']);
                $this->info("   🔄 Estado actualizado a 'en_proceso'");

                // Dispatch del job
                EnviarCorreosProgramados::dispatch($configuracion->id);
                $this->info("   ✅ Job dispatchado correctamente");

                $enviadas++;

            } catch (\Exception $e) {
                $this->error("   ❌ Error procesando configuración: " . $e->getMessage());
                Log::error("Error en cron job - Configuración ID {$configuracion->id}: " . $e->getMessage());

                // Revertir estado
                $configuracion->update(['estado_programacion' => 'pendiente']);
                $errores++;
            }
        }

        $this->line('');
        $this->info('📊 RESUMEN DE EJECUCIÓN:');
        $this->info("   ✅ Configuraciones procesadas exitosamente: {$enviadas}");
        $this->info("   ❌ Errores encontrados: {$errores}");
        $this->info("   📈 Total configuraciones: " . ($enviadas + $errores));

        if ($enviadas > 0) {
            $this->info('💡 Los jobs han sido dispatchados. Verifica el worker de colas para ver el progreso.');
        }

        $this->info('✅ CRON JOB EJECUTADO COMPLETAMENTE');
    }
}
