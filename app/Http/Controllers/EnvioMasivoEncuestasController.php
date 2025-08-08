<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Empleado;
use App\Models\EmpresasCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Exception;

class EnvioMasivoEncuestasController extends Controller
{
    /**
     * Mostrar la pantalla principal de envío masivo
     */
    public function index()
    {
        $encuestas = Encuesta::where('estado', 'publicada')
            ->orWhere('estado', 'enviada')
            ->orderBy('titulo')
            ->get();

        return view('envio-masivo.index', compact('encuestas'));
    }

    /**
     * Procesar el envío masivo de encuestas
     */
    public function enviar(Request $request)
    {
        try {
            $request->validate([
                'encuesta_id' => 'required|exists:encuestas,id'
            ]);

            $encuesta = Encuesta::findOrFail($request->encuesta_id);

            // Verificar que la encuesta esté publicada
            if ($encuesta->estado !== 'publicada' && $encuesta->estado !== 'enviada') {
                return back()->withErrors(['encuesta_id' => 'La encuesta debe estar publicada para poder enviarla.']);
            }

            // Obtener empresa y empleados
            $empresa = $encuesta->empresa;
            if (!$empresa) {
                return back()->withErrors(['error' => 'La encuesta no está asociada a una empresa.']);
            }

            $empleados = Empleado::where('empresa_id', $empresa->id)
                ->whereNotNull('correo_electronico')
                ->where('correo_electronico', '!=', '')
                ->get();

            if ($empleados->isEmpty()) {
                return back()->withErrors(['error' => 'No hay empleados con correos electrónicos válidos en esta empresa.']);
            }

            // Generar link público de la encuesta
            $linkEncuesta = $this->generarLinkPublico($encuesta);

            // Enviar correos
            $resultado = $this->enviarCorreosMasivos($encuesta, $empleados, $linkEncuesta);

            // Actualizar estado de la encuesta
            $encuesta->update(['estado' => 'enviada']);

            return view('envio-masivo.resultado', compact('resultado', 'encuesta'));

        } catch (Exception $e) {
            Log::error('Error en envío masivo de encuestas', [
                'error' => $e->getMessage(),
                'encuesta_id' => $request->encuesta_id ?? 'N/A'
            ]);

            return back()->withErrors(['error' => 'Error procesando el envío: ' . $e->getMessage()]);
        }
    }

    /**
     * Generar link público para la encuesta
     */
    public function generarLinkPublico($encuesta)
    {
        // Construir URL pública sencilla usando el slug de la encuesta
        // Sin token, sin autenticación - enlace directo como en el módulo anterior
        $url = URL::to('/publica/' . $encuesta->slug . '/sin-token');

        Log::info('🔗 Envío Masivo - Generando enlace público', [
            'encuesta_id' => $encuesta->id,
            'encuesta_titulo' => $encuesta->titulo,
            'slug' => $encuesta->slug,
            'url_generada' => $url,
            'tipo' => 'enlace_sencillo_sin_token'
        ]);

        return $url;
    }

    /**
     * Enviar correos masivos a los empleados
     */
    private function enviarCorreosMasivos($encuesta, $empleados, $linkEncuesta)
    {
        $resultado = [
            'enviados' => [],
            'fallidos' => [],
            'total' => $empleados->count(),
            'exitosos' => 0,
            'fallidos_count' => 0
        ];

        foreach ($empleados as $empleado) {
            try {
                // Validar formato de email
                if (!filter_var($empleado->correo_electronico, FILTER_VALIDATE_EMAIL)) {
                    $resultado['fallidos'][] = [
                        'email' => $empleado->correo_electronico,
                        'empleado' => $empleado->nombre,
                        'error' => 'Formato de email inválido'
                    ];
                    $resultado['fallidos_count']++;
                    continue;
                }

                // Enviar correo
                $this->enviarCorreoIndividual($empleado, $encuesta, $linkEncuesta);

                $resultado['enviados'][] = [
                    'email' => $empleado->correo_electronico,
                    'empleado' => $empleado->nombre
                ];
                $resultado['exitosos']++;

                // Pequeña pausa para evitar sobrecarga del servidor SMTP
                usleep(100000); // 0.1 segundos

            } catch (Exception $e) {
                Log::error('Error enviando correo a empleado', [
                    'empleado_id' => $empleado->id,
                    'email' => $empleado->correo_electronico,
                    'error' => $e->getMessage()
                ]);

                $resultado['fallidos'][] = [
                    'email' => $empleado->correo_electronico,
                    'empleado' => $empleado->nombre,
                    'error' => $e->getMessage()
                ];
                $resultado['fallidos_count']++;
            }
        }

        return $resultado;
    }

    /**
     * Enviar correo individual a un empleado
     */
    private function enviarCorreoIndividual($empleado, $encuesta, $linkEncuesta)
    {
        $asunto = "Invitación a participar en: {$encuesta->titulo}";

        $cuerpo = $this->generarCuerpoCorreo($empleado, $encuesta, $linkEncuesta);

        // Enviar usando la configuración SMTP existente
        Mail::raw($cuerpo, function ($message) use ($empleado, $asunto) {
            $message->to($empleado->correo_electronico)
                    ->subject($asunto)
                    ->from(config('mail.from.address'), config('mail.from.name'));
        });
    }

    /**
     * Generar cuerpo del correo
     */
    public function generarcuerpoCorreo($empleado, $encuesta, $linkEncuesta)
    {
        $cuerpo = "Hola {$empleado->nombre},\n\n";
        $cuerpo .= "Has sido invitado a participar en la siguiente encuesta:\n\n";
        $cuerpo .= "📋 Encuesta: {$encuesta->titulo}\n";
        $cuerpo .= "📅 Fecha límite: " . ($encuesta->fecha_fin ? $encuesta->fecha_fin->format('d/m/Y') : 'Sin fecha límite') . "\n\n";
        $cuerpo .= "🔗 Para acceder a la encuesta, haz clic en el siguiente enlace:\n";
        $cuerpo .= "{$linkEncuesta}\n\n";
        $cuerpo .= "Este enlace es público y no requiere autenticación.\n\n";
        $cuerpo .= "Si tienes problemas para acceder a la encuesta, contacta al administrador del sistema.\n\n";
        $cuerpo .= "Saludos,\n";
        $cuerpo .= "Equipo de Encuestas\n";
        $cuerpo .= config('app.name');

        return $cuerpo;
    }

    /**
     * Obtener estadísticas de envío
     */
    public function estadisticas()
    {
        $encuestas = Encuesta::whereIn('estado', ['enviada', 'publicada'])
            ->with('empresa')
            ->orderBy('created_at', 'desc')
            ->get();

        $estadisticas = [
            'total_encuestas' => $encuestas->count(),
            'encuestas_enviadas' => $encuestas->where('estado', 'enviada')->count(),
            'encuestas_publicadas' => $encuestas->where('estado', 'publicada')->count(),
            'empresas_activas' => $encuestas->pluck('empresa_id')->unique()->count()
        ];

        return view('envio-masivo.estadisticas', compact('encuestas', 'estadisticas'));
    }

    /**
     * Vista previa del correo
     */
    public function vistaPrevia(Request $request)
    {
        $request->validate([
            'encuesta_id' => 'required|exists:encuestas,id'
        ]);

        $encuesta = Encuesta::findOrFail($request->encuesta_id);
        $empresa = $encuesta->empresa;

        if (!$empresa) {
            return back()->withErrors(['error' => 'La encuesta no está asociada a una empresa.']);
        }

        $empleados = Empleado::where('empresa_id', $empresa->id)
            ->whereNotNull('correo_electronico')
            ->where('correo_electronico', '!=', '')
            ->get();

        $linkEncuesta = $this->generarLinkPublico($encuesta);
        $empleadoEjemplo = $empleados->first();

        if ($empleadoEjemplo) {
            $cuerpoCorreo = $this->generarcuerpoCorreo($empleadoEjemplo, $encuesta, $linkEncuesta);
        } else {
            $cuerpoCorreo = "No hay empleados disponibles para mostrar vista previa.";
        }

        return view('envio-masivo.vista-previa', compact(
            'encuesta',
            'empresa',
            'empleados',
            'cuerpoCorreo',
            'linkEncuesta'
        ));
    }

    /**
     * Obtener empleados de una empresa (AJAX)
     */
    public function obtenerEmpleados(Request $request)
    {
        $request->validate([
            'encuesta_id' => 'required|exists:encuestas,id'
        ]);

        $encuesta = Encuesta::with('empresa')->findOrFail($request->encuesta_id);
        $empresa = $encuesta->empresa;

        if (!$empresa) {
            return response()->json([
                'error' => 'La encuesta no está asociada a una empresa'
            ], 400);
        }

        $empleados = Empleado::where('empresa_id', $empresa->id)
            ->whereNotNull('correo_electronico')
            ->where('correo_electronico', '!=', '')
            ->get();

        $empleadosConEmail = $empleados->filter(function($empleado) {
            return filter_var($empleado->correo_electronico, FILTER_VALIDATE_EMAIL);
        });

        $linkEncuesta = $this->generarLinkPublico($encuesta);

        return response()->json([
            'empresa' => $empresa->nombre,
            'estado' => ucfirst($encuesta->estado),
            'preguntas_count' => $encuesta->preguntas->count(),
            'empleados' => [
                'total' => $empleados->count(),
                'con_email' => $empleadosConEmail->count()
            ],
            'empleados_con_email' => $empleadosConEmail->count(),
            'destinatarios' => $empleadosConEmail->count(),
            'asunto' => "Invitación a participar en: {$encuesta->titulo}",
            'link' => $linkEncuesta
        ]);
    }

    /**
     * Validar configuración de correo
     */
    public function validarConfiguracion()
    {
        $configuracion = [
            'mail_mailer' => config('mail.default'),
            'mail_host' => config('mail.mailers.smtp.host'),
            'mail_port' => config('mail.mailers.smtp.port'),
            'mail_username' => config('mail.mailers.smtp.username'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name')
        ];

        $errores = [];
        $advertencias = [];

        // Verificar configuración básica
        if (empty($configuracion['mail_host'])) {
            $errores[] = 'Host SMTP no configurado';
        }

        if (empty($configuracion['mail_username'])) {
            $errores[] = 'Usuario SMTP no configurado';
        }

        if (empty($configuracion['mail_from_address'])) {
            $errores[] = 'Dirección de correo remitente no configurada';
        }

        // Verificar si está usando log driver
        if ($configuracion['mail_mailer'] === 'log') {
            $advertencias[] = 'Estás usando el driver "log". Los correos se guardarán en logs en lugar de enviarse.';
        }

        return response()->json([
            'configuracion' => $configuracion,
            'errores' => $errores,
            'advertencias' => $advertencias,
            'valido' => empty($errores)
        ]);
    }
}
