<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Models\SentMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EnviarEncuestasBloques extends Command
{
    protected $signature = 'encuestas:enviar-bloques {--encuesta_id=} {--bloque=}';
    protected $description = 'Envía encuestas en bloques de 100 correos cada 5-10 minutos';

    public function handle()
    {
        $this->info('=== SISTEMA DE ENVÍO MASIVO DE ENCUESTAS ===');

        try {
            // Obtener encuestas que necesitan envío
            $encuestas = $this->obtenerEncuestasPendientes();

            if ($encuestas->isEmpty()) {
                $this->info('No hay encuestas pendientes de envío.');
                return 0;
            }

            foreach ($encuestas as $encuesta) {
                $this->procesarEncuesta($encuesta);
            }

            $this->info('Proceso de envío completado.');
            return 0;

        } catch (Exception $e) {
            $this->error('Error en el proceso de envío: ' . $e->getMessage());
            Log::error('Error en comando EnviarEncuestasBloques', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Obtener encuestas pendientes de envío
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
     * Procesar una encuesta específica
     */
    private function procesarEncuesta(Encuesta $encuesta)
    {
        $this->info("Procesando encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");

        // Obtener el siguiente bloque a enviar
        $siguienteBloque = $encuesta->obtenerSiguienteBloque();

        if (!$siguienteBloque) {
            $this->info("No hay más bloques pendientes para la encuesta {$encuesta->id}");
            return;
        }

        $this->info("Enviando bloque {$siguienteBloque->numero_bloque} ({$siguienteBloque->cantidad_correos} correos)");

        // Simular destinatarios (en un caso real, vendrían de la base de datos)
        $destinatarios = $this->generarDestinatarios($siguienteBloque->cantidad_correos);

        $enviados = 0;
        $errores = [];

        foreach ($destinatarios as $destinatario) {
            try {
                $this->enviarCorreoEncuesta($encuesta, $destinatario);
                $enviados++;

                // Pausa pequeña entre envíos para evitar spam
                usleep(100000); // 0.1 segundos

            } catch (Exception $e) {
                $errores[] = "Error enviando a {$destinatario}: " . $e->getMessage();
                Log::error('Error enviando correo de encuesta', [
                    'encuesta_id' => $encuesta->id,
                    'destinatario' => $destinatario,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Marcar bloque como enviado
        $encuesta->marcarBloqueEnviado($siguienteBloque->numero_bloque);

        $this->info("Bloque {$siguienteBloque->numero_bloque} completado: {$enviados} enviados, " . count($errores) . " errores");

        // Verificar si la encuesta está completada
        if ($encuesta->envioCompletado()) {
            $encuesta->update(['estado' => 'publicada']);
            $this->info("¡Envío completado para la encuesta {$encuesta->titulo}!");
        }
    }

    /**
     * Enviar correo individual de encuesta
     */
    private function enviarCorreoEncuesta(Encuesta $encuesta, string $destinatario)
    {
        // Generar token único para el destinatario
        $tokenEncuesta = $encuesta->generarTokenParaDestinatario($destinatario, 24);

        // URL de la encuesta
        $urlEncuesta = $tokenEncuesta->obtenerEnlace();

        // Plantilla por defecto
        $plantilla = $encuesta->plantilla_correo ?: "
            <h2>Invitación a participar en encuesta</h2>
            <p>Hola,</p>
            <p>Has sido invitado a participar en la siguiente encuesta:</p>
            <h3>{$encuesta->titulo}</h3>
            <p>Para acceder a la encuesta, haz clic en el siguiente enlace:</p>
            <p><a href='{$urlEncuesta}' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Participar en la encuesta</a></p>
            <p>Este enlace es único y personalizado para ti.</p>
            <p><strong>Importante:</strong> Este enlace expira en 24 horas.</p>
            <p>Gracias por tu participación.</p>
        ";

        // Reemplazar variables en la plantilla
        $plantilla = str_replace(
            ['{NOMBRE_ENCUESTA}', '{URL_ENCUESTA}', '{TOKEN}'],
            [$encuesta->titulo, $urlEncuesta, $tokenEncuesta->token_acceso],
            $plantilla
        );

        // Enviar correo
        Mail::send([], [], function ($message) use ($destinatario, $encuesta, $plantilla) {
            $message->to($destinatario)
                    ->subject($encuesta->asunto_correo ?: 'Invitación a participar en encuesta')
                    ->html($plantilla);
        });

        // Registrar envío
        SentMail::create([
            'to' => $destinatario,
            'subject' => $encuesta->asunto_correo ?: 'Invitación a participar en encuesta',
            'body' => $plantilla,
            'sent_by' => $encuesta->user_id,
            'encuesta_id' => $encuesta->id
        ]);
    }

    /**
     * Generar destinatarios de prueba
     */
    private function generarDestinatarios(int $cantidad): array
    {
        $destinatarios = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $destinatarios[] = "usuario{$i}@ejemplo.com";
        }
        return $destinatarios;
    }
}
