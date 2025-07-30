<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\BloqueEnvio;
use App\Models\SentMail;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Exception;

class EncuestaSeguimientoController extends Controller
{
    public function __construct()
    {
        // MIDDLEWARE DESHABILITADO - Acceso directo sin autenticación
        // $this->middleware('auth');
    }

    /**
     * Dashboard principal de seguimiento
     */
    public function dashboard($encuestaId)
    {
        try {
            $encuesta = Encuesta::with(['bloquesEnvio', 'preguntas'])->findOrFail($encuestaId);

            // PERMISOS DESHABILITADOS - Acceso directo sin verificación
            // if ($encuesta->user_id !== Auth::id()) {
            //     return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para ver el seguimiento de esta encuesta.');
            // }

            // Obtener estadísticas de envío
            $estadisticas = $this->obtenerEstadisticasEnvio($encuesta);

            // Obtener bloques de envío
            $bloques = $encuesta->obtenerBloquesEnvio();

            // Obtener correos enviados
            $correosEnviados = SentMail::where('encuesta_id', $encuestaId)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            // Obtener correos pendientes
            $correosPendientes = $this->obtenerCorreosPendientes($encuesta);

            // Actualizar estado según progreso
            $resultadoActualizacion = $encuesta->actualizarEstadoSegunProgreso();

            // Log del resultado de actualización
            if (!$resultadoActualizacion['success']) {
                Log::warning('Error actualizando estado de encuesta en dashboard', [
                    'encuesta_id' => $encuestaId,
                    'error' => $resultadoActualizacion['error']
                ]);
            }

            return view('encuestas.seguimiento.dashboard', compact(
                'encuesta',
                'estadisticas',
                'bloques',
                'correosEnviados',
                'correosPendientes'
            ));

        } catch (Exception $e) {
            Log::error('Error accediendo al dashboard de seguimiento', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('encuestas.index')
                ->with('error', 'Error al cargar el dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Obtener correos pendientes de envío
     */
    private function obtenerCorreosPendientes($encuesta)
    {
        $correosPendientes = collect();

        // Obtener empleados que no han recibido correo
        $empleadosSinCorreo = Empleado::whereNotExists(function ($query) use ($encuesta) {
            $query->select(DB::raw(1))
                  ->from('sent_mails')
                  ->whereColumn('sent_mails.to', 'empleados.correo_electronico')
                  ->where('sent_mails.encuesta_id', $encuesta->id);
        })->get();

        foreach ($empleadosSinCorreo as $empleado) {
            $correosPendientes->push([
                'id' => 'emp_' . $empleado->id,
                'nombre' => $empleado->nombre,
                'email' => $empleado->correo_electronico,
                'cargo' => $empleado->cargo,
                'tipo' => 'empleado',
                'empleado_id' => $empleado->id
            ]);
        }

        // Obtener usuarios que no han recibido correo
        $usuariosSinCorreo = User::whereNotExists(function ($query) use ($encuesta) {
            $query->select(DB::raw(1))
                  ->from('sent_mails')
                  ->whereColumn('sent_mails.to', 'users.email')
                  ->where('sent_mails.encuesta_id', $encuesta->id);
        })->get();

        foreach ($usuariosSinCorreo as $usuario) {
            $correosPendientes->push([
                'id' => 'usr_' . $usuario->id,
                'nombre' => $usuario->name,
                'email' => $usuario->email,
                'cargo' => null,
                'tipo' => 'usuario',
                'usuario_id' => $usuario->id
            ]);
        }

        return $correosPendientes;
    }

    /**
     * Enviar correos masivos
     */
    public function enviarCorreosMasivos($encuestaId)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            $correosPendientes = $this->obtenerCorreosPendientes($encuesta);

            $enviados = 0;
            $errores = 0;

            foreach ($correosPendientes as $correo) {
                try {
                    $this->enviarCorreoIndividual($correo, $encuesta);
                    $enviados++;
                } catch (Exception $e) {
                    $errores++;
                    Log::error('Error enviando correo masivo', [
                        'email' => $correo['email'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se enviaron {$enviados} correos exitosamente. Errores: {$errores}",
                'enviados' => $enviados,
                'errores' => $errores
            ]);

        } catch (Exception $e) {
            Log::error('Error en envío masivo', [
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correos masivos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar correos seleccionados
     */
    public function enviarCorreosSeleccionados($encuestaId, Request $request)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            $correosIds = $request->input('correos', []);

            $enviados = 0;
            $errores = 0;

            foreach ($correosIds as $correoId) {
                try {
                    $correo = $this->obtenerCorreoPorId($correoId, $encuesta);
                    if ($correo) {
                        $this->enviarCorreoIndividual($correo, $encuesta);
                        $enviados++;
                    }
                } catch (Exception $e) {
                    $errores++;
                    Log::error('Error enviando correo seleccionado', [
                        'correo_id' => $correoId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Se enviaron {$enviados} correos exitosamente. Errores: {$errores}",
                'enviados' => $enviados,
                'errores' => $errores
            ]);

        } catch (Exception $e) {
            Log::error('Error en envío de correos seleccionados', [
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correos seleccionados: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar correo individual (endpoint público)
     */
    public function enviarCorreoIndividualEndpoint($encuestaId, Request $request)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            $correoId = $request->input('correo_id');

            $correo = $this->obtenerCorreoPorId($correoId, $encuesta);

            if (!$correo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Correo no encontrado'
                ], 404);
            }

            $this->enviarCorreoIndividual($correo, $encuesta);

            return response()->json([
                'success' => true,
                'message' => "Correo enviado exitosamente a {$correo['email']}"
            ]);

        } catch (Exception $e) {
            Log::error('Error enviando correo individual', [
                'encuesta_id' => $encuestaId,
                'correo_id' => $correoId ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar correo individual: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener correo por ID
     */
    private function obtenerCorreoPorId($correoId, $encuesta)
    {
        $correosPendientes = $this->obtenerCorreosPendientes($encuesta);

        return $correosPendientes->firstWhere('id', $correoId);
    }

    /**
     * Enviar correo individual (método interno)
     */
    private function enviarCorreoIndividual($correo, $encuesta)
    {
        // Generar token único para la encuesta
        $token = $encuesta->generarTokenAcceso();

        // Generar enlace de la encuesta
        $enlace = route('encuestas.publica', $encuesta->slug) . '?token=' . $token;

        // Preparar datos del correo
        $datosCorreo = [
            'nombre' => $correo['nombre'],
            'email' => $correo['email'],
            'encuesta' => $encuesta->titulo,
            'enlace' => $enlace,
            'fecha_limite' => $encuesta->fecha_fin ? $encuesta->fecha_fin->format('d/m/Y H:i') : 'Sin fecha límite'
        ];

        // Enviar correo
        Mail::send('emails.encuesta', $datosCorreo, function ($message) use ($correo, $encuesta) {
            $message->to($correo['email'], $correo['nombre'])
                    ->subject('Nueva encuesta disponible: ' . $encuesta->titulo);
        });

        // Registrar envío en la base de datos
        SentMail::create([
            'encuesta_id' => $encuesta->id,
            'to' => $correo['email'],
            'subject' => 'Nueva encuesta disponible: ' . $encuesta->titulo,
            'status' => 'sent',
            'sent_at' => now()
        ]);

        Log::info('Correo enviado exitosamente', [
            'encuesta_id' => $encuesta->id,
            'email' => $correo['email'],
            'tipo' => $correo['tipo']
        ]);
    }

    /**
     * Ver detalles de correo
     */
    public function detallesCorreo($encuestaId, Request $request)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            $correoId = $request->input('correo_id');

            $correo = $this->obtenerCorreoPorId($correoId, $encuesta);

            if (!$correo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Correo no encontrado'
                ], 404);
            }

            $html = view('encuestas.seguimiento.detalles-correo', compact('correo', 'encuesta'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'correo' => $correo
            ]);

        } catch (Exception $e) {
            Log::error('Error obteniendo detalles de correo', [
                'encuesta_id' => $encuestaId,
                'correo_id' => $correoId ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener detalles del correo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar lista de correos
     */
    public function exportarLista($encuestaId, Request $request)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            $correosIds = $request->input('correos', []);

            $correosPendientes = $this->obtenerCorreosPendientes($encuesta);

            if (!empty($correosIds)) {
                $correosPendientes = $correosPendientes->whereIn('id', $correosIds);
            }

            // Generar CSV
            $filename = 'correos_pendientes_' . $encuesta->id . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/public/' . $filename);

            $handle = fopen($filepath, 'w');

            // Headers
            fputcsv($handle, ['Nombre', 'Email', 'Cargo', 'Tipo']);

            // Datos
            foreach ($correosPendientes as $correo) {
                fputcsv($handle, [
                    $correo['nombre'],
                    $correo['email'],
                    $correo['cargo'] ?? 'N/A',
                    $correo['tipo']
                ]);
            }

            fclose($handle);

            return response()->json([
                'success' => true,
                'message' => 'Lista exportada exitosamente',
                'download_url' => asset('storage/' . $filename),
                'filename' => $filename
            ]);

        } catch (Exception $e) {
            Log::error('Error exportando lista de correos', [
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al exportar lista: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar datos del dashboard (AJAX)
     */
    public function actualizarDatos($encuestaId)
    {
        try {
            $encuesta = Encuesta::with(['bloquesEnvio'])->findOrFail($encuestaId);

            // PERMISOS DESHABILITADOS - Acceso directo sin verificación
            // if ($encuesta->user_id !== Auth::id()) {
            //     return response()->json(['error' => 'No tienes permisos'], 403);
            // }

            // Obtener estadísticas actualizadas
            $estadisticas = $this->obtenerEstadisticasEnvio($encuesta);

            // Obtener bloques actualizados
            $bloques = $encuesta->obtenerBloquesEnvio();

            // Obtener correos recientes
            $correosEnviados = SentMail::where('encuesta_id', $encuestaId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Actualizar estado
            $resultadoActualizacion = $encuesta->actualizarEstadoSegunProgreso();

            // Log del resultado de actualización
            if (!$resultadoActualizacion['success']) {
                Log::warning('Error actualizando estado de encuesta en actualizarDatos', [
                    'encuesta_id' => $encuestaId,
                    'error' => $resultadoActualizacion['error']
                ]);
            }

            return response()->json([
                'estadisticas' => $estadisticas,
                'bloques' => $bloques,
                'correos_enviados' => $correosEnviados,
                'estado_actual' => $encuesta->estado,
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            Log::error('Error actualizando datos del dashboard', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Error al actualizar datos'], 500);
        }
    }

    /**
     * Pausar envío
     */
    public function pausarEnvio($encuestaId)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);

            // PERMISOS DESHABILITADOS - Acceso directo sin verificación
            // if ($encuesta->user_id !== Auth::id()) {
            //     return redirect()->back()->with('error', 'No tienes permisos para pausar el envío.');
            // }

            $encuesta->pausarEnvio();

            Log::info('Envío pausado manualmente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId
            ]);

            return redirect()->back()->with('success', 'Envío pausado correctamente.');

        } catch (Exception $e) {
            Log::error('Error pausando envío', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al pausar el envío: ' . $e->getMessage());
        }
    }

    /**
     * Reanudar envío
     */
    public function reanudarEnvio($encuestaId)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);

            // PERMISOS DESHABILITADOS - Acceso directo sin verificación
            // if ($encuesta->user_id !== Auth::id()) {
            //     return redirect()->back()->with('error', 'No tienes permisos para reanudar el envío.');
            // }

            $encuesta->reanudarEnvio();

            Log::info('Envío reanudado manualmente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId
            ]);

            return redirect()->back()->with('success', 'Envío reanudado correctamente.');

        } catch (Exception $e) {
            Log::error('Error reanudando envío', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al reanudar el envío: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar envío
     */
    public function cancelarEnvio($encuestaId)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);

            // PERMISOS DESHABILITADOS - Acceso directo sin verificación
            // if ($encuesta->user_id !== Auth::id()) {
            //     return redirect()->back()->with('error', 'No tienes permisos para cancelar el envío.');
            // }

            // Marcar como cancelado
            $encuesta->update([
                'estado' => 'borrador',
                'envio_masivo_activado' => false
            ]);

            // Cancelar bloques pendientes
            BloqueEnvio::where('encuesta_id', $encuestaId)
                ->whereIn('estado', ['pendiente', 'en_proceso'])
                ->update(['estado' => 'cancelado']);

            Log::info('Envío cancelado manualmente', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId
            ]);

            return redirect()->back()->with('success', 'Envío cancelado correctamente.');

        } catch (Exception $e) {
            Log::error('Error cancelando envío', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al cancelar el envío: ' . $e->getMessage());
        }
    }

    /**
     * Obtener estadísticas detalladas del envío
     */
    private function obtenerEstadisticasEnvio($encuesta)
    {
        $bloques = $encuesta->obtenerBloquesEnvio();

        $totalBloques = $bloques->count();
        $bloquesEnviados = $bloques->where('estado', 'enviado')->count();
        $bloquesPendientes = $bloques->where('estado', 'pendiente')->count();
        $bloquesEnProceso = $bloques->where('estado', 'en_proceso')->count();
        $bloquesError = $bloques->where('estado', 'error')->count();
        $bloquesCancelados = $bloques->where('estado', 'cancelado')->count();

        $correosEnviados = SentMail::where('encuesta_id', $encuesta->id)->count();
        $correosError = SentMail::where('encuesta_id', $encuesta->id)
            ->where('status', 'error')
            ->count();

        $progresoPorcentaje = $totalBloques > 0 ? round(($bloquesEnviados / $totalBloques) * 100, 2) : 0;

        return [
            'total_encuestas' => $encuesta->numero_encuestas,
            'encuestas_enviadas' => $encuesta->encuestas_enviadas ?? 0,
            'encuestas_pendientes' => $encuesta->encuestas_pendientes ?? 0,
            'encuestas_respondidas' => $encuesta->encuestas_respondidas ?? 0,
            'total_bloques' => $totalBloques,
            'bloques_enviados' => $bloquesEnviados,
            'bloques_pendientes' => $bloquesPendientes,
            'bloques_en_proceso' => $bloquesEnProceso,
            'bloques_error' => $bloquesError,
            'bloques_cancelados' => $bloquesCancelados,
            'correos_enviados' => $correosEnviados,
            'correos_error' => $correosError,
            'progreso_porcentaje' => $progresoPorcentaje,
            'estado_encuesta' => $encuesta->estado,
            'ultima_actualizacion' => now()->format('Y-m-d H:i:s')
        ];
    }
}
