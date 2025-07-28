<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
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
        $this->middleware('auth');
    }

    /**
     * Mostrar dashboard de seguimiento del envío
     */
    public function dashboard($encuestaId)
    {
        try {
            $encuesta = Encuesta::with(['empresa', 'preguntas'])->findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para ver el seguimiento de esta encuesta.');
            }

            // Obtener estadísticas detalladas
            $estadisticas = $encuesta->obtenerEstadisticasEnvioDetalladas();
            $bloques = $encuesta->obtenerBloquesEnvio();

            // Obtener historial de envíos
            $historialEnvio = SentMail::where('encuesta_id', $encuestaId)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get();

            // Obtener errores de envío
            $erroresEnvio = $this->obtenerErroresEnvio($encuestaId);

            return view('encuestas.seguimiento.dashboard', compact(
                'encuesta',
                'estadisticas',
                'bloques',
                'historialEnvio',
                'erroresEnvio'
            ));

        } catch (Exception $e) {
            Log::error('Error en dashboard de seguimiento', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('encuestas.index')->with('error', 'Error al cargar el dashboard de seguimiento.');
        }
    }

    /**
     * Obtener datos actualizados del seguimiento (AJAX)
     */
    public function actualizarDatos($encuestaId)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return response()->json(['error' => 'No tienes permisos'], 403);
            }

            $estadisticas = $encuesta->obtenerEstadisticasEnvioDetalladas();
            $bloques = $encuesta->obtenerBloquesEnvio();

            return response()->json([
                'estadisticas' => $estadisticas,
                'bloques' => $bloques,
                'timestamp' => now()->toISOString()
            ]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Pausar envío masivo
     */
    public function pausarEnvio($encuestaId)
    {
        try {
            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para pausar esta encuesta.');
            }

            // Solo se puede pausar si está en progreso
            if (!$encuesta->envioEnProgreso()) {
                return redirect()->back()->with('warning', 'La encuesta no está en proceso de envío.');
            }

            $encuesta->update([
                'estado' => 'borrador',
                'envio_masivo_activado' => false
            ]);

            DB::commit();

            Log::info('Envío pausado', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId
            ]);

            return redirect()->back()->with('success', 'Envío pausado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error pausando envío', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al pausar el envío.');
        }
    }

    /**
     * Reanudar envío masivo
     */
    public function reanudarEnvio($encuestaId)
    {
        try {
            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para reanudar esta encuesta.');
            }

            // Solo se puede reanudar si está pausada
            if ($encuesta->estado !== 'borrador' || $encuesta->envioCompletado()) {
                return redirect()->back()->with('warning', 'La encuesta no puede ser reanudada.');
            }

            $encuesta->update([
                'estado' => 'enviada',
                'envio_masivo_activado' => true
            ]);

            DB::commit();

            Log::info('Envío reanudado', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId
            ]);

            return redirect()->back()->with('success', 'Envío reanudado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error reanudando envío', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al reanudar el envío.');
        }
    }

    /**
     * Cancelar envío masivo
     */
    public function cancelarEnvio($encuestaId)
    {
        try {
            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para cancelar esta encuesta.');
            }

            $encuesta->update([
                'estado' => 'borrador',
                'envio_masivo_activado' => false,
                'encuestas_enviadas' => 0,
                'encuestas_pendientes' => $encuesta->numero_encuestas
            ]);

            DB::commit();

            Log::info('Envío cancelado', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId
            ]);

            return redirect()->route('encuestas.show', $encuestaId)
                ->with('success', 'Envío cancelado correctamente.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error cancelando envío', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cancelar el envío.');
        }
    }

    /**
     * Obtener errores de envío
     */
    private function obtenerErroresEnvio($encuestaId): array
    {
        return SentMail::where('encuesta_id', $encuestaId)
            ->whereNotNull('error_message')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->toArray();
    }

    /**
     * Exportar reporte de seguimiento
     */
    public function exportarReporte($encuestaId)
    {
        try {
            $encuesta = Encuesta::with(['empresa', 'preguntas'])->findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para exportar el reporte.');
            }

            $estadisticas = $encuesta->obtenerEstadisticasEnvioDetalladas();
            $historialEnvio = SentMail::where('encuesta_id', $encuestaId)
                ->orderByDesc('created_at')
                ->get();

            // Por ahora retornamos una vista simple del reporte
            return view('encuestas.seguimiento.reporte', compact(
                'encuesta',
                'estadisticas',
                'historialEnvio'
            ));

        } catch (Exception $e) {
            Log::error('Error exportando reporte', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al exportar el reporte.');
        }
    }
}
