<?php

namespace App\Jobs;

use App\Models\Encuesta;
use App\Models\SentMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EnviarBloqueEncuestas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $encuestaId;
    protected $numeroBloque;
    protected $destinatarios;

    /**
     * Create a new job instance.
     */
    public function __construct(int $encuestaId, int $numeroBloque, array $destinatarios = [])
    {
        $this->encuestaId = $encuestaId;
        $this->numeroBloque = $numeroBloque;
        $this->destinatarios = $destinatarios;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $encuesta = Encuesta::findOrFail($this->encuestaId);
            $bloque = $encuesta->bloquesEnvio()
                ->where('numero_bloque', $this->numeroBloque)
                ->first();

            if (!$bloque) {
                Log::error('Bloque no encontrado', [
                    'encuesta_id' => $this->encuestaId,
                    'numero_bloque' => $this->numeroBloque
                ]);
                return;
            }

            // Verificar que la encuesta esté lista para envío
            if (!$encuesta->puedeEnviarseMasivamente()) {
                Log::warning('Encuesta no está lista para envío', [
                    'encuesta_id' => $this->encuestaId,
                    'estado' => $encuesta->estado
                ]);
                return;
            }

            // Marcar bloque como en proceso
            $bloque->marcarEnProceso();

            // Si no se proporcionaron destinatarios, generar algunos de prueba
            if (empty($this->destinatarios)) {
                $this->destinatarios = $this->generarDestinatariosPrueba($bloque->cantidad_correos);
            }

            $enviados = 0;
            $errores = [];

            foreach ($this->destinatarios as $destinatario) {
                try {
                    $this->enviarCorreoEncuesta($encuesta, $destinatario);
                    $enviados++;

                    // Pausa pequeña entre envíos para evitar spam
                    usleep(100000); // 0.1 segundos

                } catch (Exception $e) {
                    $errores[] = "Error enviando a {$destinatario}: " . $e->getMessage();
                    Log::error('Error enviando correo de encuesta', [
                        'encuesta_id' => $this->encuestaId,
                        'destinatario' => $destinatario,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Marcar bloque como enviado
            $bloque->marcarEnviado($enviados, count($errores), $errores);
            $encuesta->marcarBloqueEnviado($this->numeroBloque);

            Log::info('Bloque de encuestas enviado', [
                'encuesta_id' => $this->encuestaId,
                'numero_bloque' => $this->numeroBloque,
                'enviados' => $enviados,
                'errores' => count($errores)
            ]);

            // Programar siguiente bloque si existe
            $siguienteBloque = $encuesta->obtenerSiguienteBloque();
            if ($siguienteBloque) {
                // Programar siguiente bloque para 7 minutos después
                EnviarBloqueEncuestas::dispatch($this->encuestaId, $siguienteBloque->numero_bloque)
                    ->delay(now()->addMinutes(7));
            } else {
                // Envío completado
                $encuesta->update(['estado' => 'publicada']);
                Log::info('Envío completado para encuesta', [
                    'encuesta_id' => $this->encuestaId
                ]);
            }

        } catch (Exception $e) {
            // Marcar bloque como error
            if (isset($bloque)) {
                $bloque->marcarError([$e->getMessage()]);
            }

            Log::error('Error en job EnviarBloqueEncuestas', [
                'encuesta_id' => $this->encuestaId,
                'numero_bloque' => $this->numeroBloque,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enviar correo individual de encuesta
     */
    private function enviarCorreoEncuesta(Encuesta $encuesta, string $destinatario)
    {
        // Generar token único para el destinatario
        $tokenEncuesta = $encuesta->generarTokenParaDestinatario($destinatario, 24);
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
    private function generarDestinatariosPrueba(int $cantidad): array
    {
        $destinatarios = [];
        $cantidad = min($cantidad, 50); // Máximo 50 para pruebas

        for ($i = 0; $i < $cantidad; $i++) {
            $destinatarios[] = "usuario{$i}@ejemplo.com";
        }

        return $destinatarios;
    }
}
