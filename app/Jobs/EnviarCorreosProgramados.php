<?php

namespace App\Jobs;

use App\Models\ConfiguracionEnvio;
use App\Models\Encuesta;
use App\Models\Empleado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class EnviarCorreosProgramados implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutos
    public $tries = 3;

    protected $configuracionId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $configuracionId)
    {
        $this->configuracionId = $configuracionId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $configuracion = ConfiguracionEnvio::with(['encuesta', 'empresa'])->findOrFail($this->configuracionId);

            if (!$configuracion->estaListoParaEnvio()) {
                Log::info('Configuración no está lista para envío', [
                    'configuracion_id' => $this->configuracionId,
                    'estado' => $configuracion->estado_programacion
                ]);
                return;
            }

            // Marcar como en proceso
            $configuracion->marcarEnProceso();

            Log::info('Iniciando envío programado de correos', [
                'configuracion_id' => $this->configuracionId,
                'encuesta_id' => $configuracion->encuesta_id,
                'empresa_id' => $configuracion->empresa_id,
                'tipo_destinatario' => $configuracion->tipo_destinatario
            ]);

            // Obtener destinatarios según el tipo
            $destinatarios = $this->obtenerDestinatarios($configuracion);

            if (empty($destinatarios)) {
                Log::warning('No se encontraron destinatarios para el envío programado', [
                    'configuracion_id' => $this->configuracionId,
                    'tipo_destinatario' => $configuracion->tipo_destinatario
                ]);
                $configuracion->marcarCompletado();
                return;
            }

            // Si es modo prueba, enviar solo al correo de prueba
            if ($configuracion->modo_prueba && $configuracion->correo_prueba) {
                $this->enviarCorreoPrueba($configuracion);
                $configuracion->marcarCompletado();
                return;
            }

            // Dividir destinatarios en bloques
            $bloques = $this->dividirEnBloques($destinatarios, $configuracion->numero_bloques);

            $enviados = 0;
            $errores = [];

            foreach ($bloques as $indiceBloque => $bloqueDestinatarios) {
                Log::info("Enviando bloque {$indiceBloque} de " . count($bloques), [
                    'configuracion_id' => $this->configuracionId,
                    'destinatarios_bloque' => count($bloqueDestinatarios)
                ]);

                foreach ($bloqueDestinatarios as $destinatario) {
                    try {
                        $this->enviarCorreoIndividual($configuracion, $destinatario);
                        $enviados++;

                        // Pausa pequeña entre envíos para evitar spam
                        usleep(100000); // 0.1 segundos

                    } catch (Exception $e) {
                        $errores[] = "Error enviando a {$destinatario['email']}: " . $e->getMessage();
                        Log::error('Error enviando correo programado', [
                            'configuracion_id' => $this->configuracionId,
                            'destinatario' => $destinatario['email'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                // Pausa entre bloques (excepto el último)
                if ($indiceBloque < count($bloques) - 1) {
                    sleep(30); // 30 segundos entre bloques
                }
            }

            // Marcar como completado
            $configuracion->marcarCompletado();

            // Publicar la encuesta automáticamente
            $configuracion->encuesta->publicar();

            Log::info('Envío programado completado', [
                'configuracion_id' => $this->configuracionId,
                'enviados' => $enviados,
                'errores' => count($errores),
                'total_destinatarios' => count($destinatarios)
            ]);

        } catch (Exception $e) {
            Log::error('Error en job EnviarCorreosProgramados', [
                'configuracion_id' => $this->configuracionId,
                'error' => $e->getMessage()
            ]);

            // Marcar como error
            if (isset($configuracion)) {
                $configuracion->marcarCancelado();
            }

            throw $e;
        }
    }

    /**
     * Obtener destinatarios según el tipo configurado
     */
    private function obtenerDestinatarios(ConfiguracionEnvio $configuracion): array
    {
        switch ($configuracion->tipo_destinatario) {
            case ConfiguracionEnvio::DESTINATARIO_EMPLEADOS:
                return Empleado::where('empresa_id', $configuracion->empresa_id)
                    ->get(['nombre', 'correo_electronico as email'])
                    ->toArray();

            case ConfiguracionEnvio::DESTINATARIO_CLIENTES:
                // Implementar lógica para clientes
                return [];

            case ConfiguracionEnvio::DESTINATARIO_PROVEEDORES:
                // Implementar lógica para proveedores
                return [];

            case ConfiguracionEnvio::DESTINATARIO_PERSONALIZADO:
                // Implementar lógica para lista personalizada
                return [];

            default:
                return [];
        }
    }

    /**
     * Dividir destinatarios en bloques
     */
    private function dividirEnBloques(array $destinatarios, int $numeroBloques): array
    {
        $totalDestinatarios = count($destinatarios);
        $tamanoBloque = ceil($totalDestinatarios / $numeroBloques);

        return array_chunk($destinatarios, $tamanoBloque);
    }

    /**
     * Enviar correo individual
     */
    private function enviarCorreoIndividual(ConfiguracionEnvio $configuracion, array $destinatario)
    {
        // Generar token único para la encuesta
        $token = $configuracion->encuesta->generarTokenAcceso();

        // Generar enlace de la encuesta
        $enlace = route('encuestas.publica', $configuracion->encuesta->slug) . '?token=' . $token;

        // Preparar datos del correo
        $datosCorreo = [
            'nombre' => $destinatario['nombre'],
            'email' => $destinatario['email'],
            'encuesta' => $configuracion->encuesta->titulo,
            'enlace' => $enlace,
            'empresa' => $configuracion->empresa->nombre,
            'fecha_limite' => $configuracion->encuesta->fecha_fin ? $configuracion->encuesta->fecha_fin->format('d/m/Y H:i') : 'Sin fecha límite'
        ];

        // Enviar correo
        Mail::send('emails.encuesta', $datosCorreo, function ($message) use ($configuracion, $destinatario) {
            $message->to($destinatario['email'], $destinatario['nombre'])
                    ->subject($configuracion->asunto);
        });
    }

    /**
     * Enviar correo de prueba
     */
    private function enviarCorreoPrueba(ConfiguracionEnvio $configuracion)
    {
        // Generar token único para la encuesta
        $token = $configuracion->encuesta->generarTokenAcceso();

        // Generar enlace de la encuesta
        $enlace = route('encuestas.publica', $configuracion->encuesta->slug) . '?token=' . $token;

        // Preparar datos del correo de prueba
        $datosCorreo = [
            'nombre' => 'Usuario de Prueba',
            'email' => $configuracion->correo_prueba,
            'encuesta' => $configuracion->encuesta->titulo,
            'enlace' => $enlace,
            'empresa' => $configuracion->empresa->nombre,
            'fecha_limite' => $configuracion->encuesta->fecha_fin ? $configuracion->encuesta->fecha_fin->format('d/m/Y H:i') : 'Sin fecha límite',
            'es_prueba' => true
        ];

        // Enviar correo de prueba
        Mail::send('emails.encuesta', $datosCorreo, function ($message) use ($configuracion) {
            $message->to($configuracion->correo_prueba)
                    ->subject('[PRUEBA] ' . $configuracion->asunto);
        });

        Log::info('Correo de prueba enviado', [
            'configuracion_id' => $configuracion->id,
            'correo_prueba' => $configuracion->correo_prueba
        ]);
    }
}
