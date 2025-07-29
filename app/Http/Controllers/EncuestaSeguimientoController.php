<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\BloqueEnvio;
use App\Models\SentMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            // Actualizar estado según progreso
            $encuesta->actualizarEstadoSegunProgreso();
            dd('dashboard', $encuestaId, $encuesta, $estadisticas, $bloques, $correosEnviados, $encuesta->estado, $encuesta->encuestas_enviadas, $encuesta->encuestas_pendientes, $encuesta->encuestas_respondidas);
            return view('encuestas.seguimiento.dashboard', compact(
                'encuesta',
                'estadisticas',
                'bloques',
                'correosEnviados'
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
     * Actualizar datos del dashboard (AJAX)
     */
    public function actualizarDatos($encuestaId)
    {
        dd('actualizarDatos', $encuestaId);
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
            $encuesta->actualizarEstadoSegunProgreso();

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
        dd('pausarEnvio', $encuestaId);
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
        dd('reanudarEnvio', $encuestaId);
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
        dd('cancelarEnvio', $encuestaId);
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
        //dd('obtenerEstadisticasEnvio', $encuesta);
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
