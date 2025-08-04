<?php

namespace App\Console\Commands;

use App\Models\ConfiguracionEnvio;
use App\Jobs\EnviarCorreosProgramados;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerificarEnvioProgramado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encuestas:verificar-envio-programado';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica y ejecuta envíos de correos programados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando envíos programados...');

        try {
            // Obtener configuraciones programadas que están listas para envío
            $configuraciones = ConfiguracionEnvio::programadasPendientes()
                ->where('fecha_envio', '<=', now()->toDateString())
                ->where('hora_envio', '<=', now()->toTimeString())
                ->get();

            if ($configuraciones->isEmpty()) {
                $this->info('✅ No hay envíos programados pendientes para ejecutar.');
                return 0;
            }

            $this->info("📧 Encontradas {$configuraciones->count()} configuraciones listas para envío.");

            foreach ($configuraciones as $configuracion) {
                $this->info("🚀 Programando envío para configuración ID: {$configuracion->id}");

                // Dispatch del job para envío programado
                EnviarCorreosProgramados::dispatch($configuracion->id);

                Log::info('Envío programado dispatchado', [
                    'configuracion_id' => $configuracion->id,
                    'encuesta_id' => $configuracion->encuesta_id,
                    'empresa_id' => $configuracion->empresa_id,
                    'fecha_envio' => $configuracion->fecha_envio,
                    'hora_envio' => $configuracion->hora_envio
                ]);
            }

            $this->info('✅ Todos los envíos programados han sido dispatchados correctamente.');
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error verificando envíos programados: " . $e->getMessage());

            Log::error('Error en comando VerificarEnvioProgramado', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
