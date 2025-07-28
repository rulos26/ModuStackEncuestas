<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Jobs\EnviarBloqueEncuestas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProgramarEnvioEncuestas extends Command
{
    protected $signature = 'encuestas:programar-envio {--encuesta_id=}';
    protected $description = 'Programa el envío automático de encuestas en bloques';

    public function handle()
    {
        $this->info('=== PROGRAMADOR DE ENVÍO MASIVO DE ENCUESTAS ===');

        try {
            // Obtener encuestas que necesitan envío
            $encuestas = $this->obtenerEncuestasPendientes();

            if ($encuestas->isEmpty()) {
                $this->info('No hay encuestas pendientes de programación.');
                return 0;
            }

            foreach ($encuestas as $encuesta) {
                $this->programarEncuesta($encuesta);
            }

            $this->info('Programación de envíos completada.');
            return 0;

        } catch (Exception $e) {
            $this->error('Error en la programación: ' . $e->getMessage());
            Log::error('Error en comando ProgramarEnvioEncuestas', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Obtener encuestas pendientes de programación
     */
    private function obtenerEncuestasPendientes()
    {
        return Encuesta::where('estado', 'enviada')
            ->where('envio_masivo_activado', true)
            ->where('enviar_por_correo', true)
            ->where('encuestas_enviadas', '<', DB::raw('numero_encuestas'))
            ->where('validacion_completada', true)
            ->get();
    }

    /**
     * Programar una encuesta específica
     */
    private function programarEncuesta(Encuesta $encuesta)
    {
        $this->info("Programando encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");

        // Obtener el siguiente bloque a enviar
        $siguienteBloque = $encuesta->obtenerSiguienteBloque();

        if (!$siguienteBloque) {
            $this->info("No hay más bloques pendientes para la encuesta {$encuesta->id}");
            return;
        }

        $this->info("Programando bloque {$siguienteBloque->numero_bloque} ({$siguienteBloque->cantidad_correos} correos)");

        // Programar el job para envío inmediato
        EnviarBloqueEncuestas::dispatch($encuesta->id, $siguienteBloque->numero_bloque);

        Log::info('Envío programado', [
            'encuesta_id' => $encuesta->id,
            'numero_bloque' => $siguienteBloque->numero_bloque,
            'cantidad_correos' => $siguienteBloque->cantidad_correos
        ]);

        $this->info("Bloque {$siguienteBloque->numero_bloque} programado para envío inmediato");
    }
}
