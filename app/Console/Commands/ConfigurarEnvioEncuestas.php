<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Jobs\EnviarBloqueEncuestas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ConfigurarEnvioEncuestas extends Command
{
    protected $signature = 'encuestas:configurar-envio
                            {encuesta_id : ID de la encuesta}
                            {--minutos=7 : Minutos entre bloques (5-10)}
                            {--tamano-bloque=100 : Tamaño del bloque de correos}';

    protected $description = 'Configura el envío automático de encuestas con parámetros flexibles';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $minutosEntreBloques = $this->option('minutos');
        $tamanoBloque = $this->option('tamano-bloque');

        // Validar parámetros
        if ($minutosEntreBloques < 5 || $minutosEntreBloques > 10) {
            $this->error('Los minutos entre bloques deben estar entre 5 y 10.');
            return 1;
        }

        if ($tamanoBloque < 50 || $tamanoBloque > 200) {
            $this->error('El tamaño del bloque debe estar entre 50 y 200.');
            return 1;
        }

        $this->info("=== CONFIGURACIÓN DE ENVÍO MASIVO ===");
        $this->info("Encuesta ID: {$encuestaId}");
        $this->info("Minutos entre bloques: {$minutosEntreBloques}");
        $this->info("Tamaño de bloque: {$tamanoBloque}");

        try {
            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar que la encuesta esté lista
            if (!$encuesta->puedeEnviarseMasivamente()) {
                $this->error('La encuesta no está lista para envío masivo.');
                $this->error('Verifique que esté en estado borrador y tenga validación completada.');
                return 1;
            }

            // Crear bloques con el tiempo configurado
            $encuesta->crearBloquesEnvio($minutosEntreBloques);

            // Configurar encuesta para envío
            $encuesta->update([
                'estado' => 'enviada',
                'envio_masivo_activado' => true
            ]);

            // Obtener primer bloque
            $primerBloque = $encuesta->obtenerSiguienteBloque();

            if ($primerBloque) {
                // Programar primer envío
                EnviarBloqueEncuestas::dispatch($encuestaId, $primerBloque->numero_bloque);

                $this->info("✅ Configuración completada exitosamente");
                $this->info("📧 Total de encuestas: {$encuesta->numero_encuestas}");
                $this->info("📦 Bloques programados: " . ceil($encuesta->numero_encuestas / $tamanoBloque));
                $this->info("⏰ Tiempo entre bloques: {$minutosEntreBloques} minutos");
                $this->info("🚀 Primer envío programado para: " . $primerBloque->fecha_programada->format('H:i:s'));

                Log::info('Configuración de envío masivo completada', [
                    'encuesta_id' => $encuestaId,
                    'minutos_entre_bloques' => $minutosEntreBloques,
                    'tamano_bloque' => $tamanoBloque,
                    'total_encuestas' => $encuesta->numero_encuestas,
                    'total_bloques' => ceil($encuesta->numero_encuestas / $tamanoBloque)
                ]);

            } else {
                $this->error('No se pudo programar el primer bloque.');
                return 1;
            }

            return 0;

        } catch (Exception $e) {
            $this->error('Error en la configuración: ' . $e->getMessage());
            Log::error('Error configurando envío masivo', [
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }
}
