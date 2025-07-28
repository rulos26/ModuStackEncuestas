<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class EncuestaPreviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function preview($encuestaId, Request $request)
    {
        try {
            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])->findOrFail($encuestaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para ver esta encuesta.');
            }

            $preguntas = $encuesta->preguntas;

            if ($preguntas->isEmpty()) {
                return redirect()->route('encuestas.preguntas.create', $encuestaId)
                    ->with('warning', 'No hay preguntas configuradas. Agregue preguntas antes de la vista previa.');
            }

            // Configurar navegación por bloques (3-4 preguntas por paso)
            $preguntasPorBloque = 3;
            $bloqueActual = $request->get('bloque', 1);
            $totalBloques = ceil($preguntas->count() / $preguntasPorBloque);

            // Obtener preguntas del bloque actual
            $preguntasBloque = $preguntas->forPage($bloqueActual, $preguntasPorBloque);

            // Calcular estadísticas del bloque
            $preguntasObligatorias = $preguntasBloque->where('obligatoria', true)->count();
            $preguntasOpcionales = $preguntasBloque->where('obligatoria', false)->count();

            return view('encuestas.preview', compact(
                'encuesta',
                'preguntasBloque',
                'bloqueActual',
                'totalBloques',
                'preguntasObligatorias',
                'preguntasOpcionales'
            ));

        } catch (Exception $e) {
            return redirect()->route('encuestas.index')->with('error', 'Error al cargar la vista previa: ' . $e->getMessage());
        }
    }

    /**
     * Editar pregunta desde la vista previa
     */
    public function editarPregunta($encuestaId, $preguntaId)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            $pregunta = Pregunta::findOrFail($preguntaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para editar esta encuesta.');
            }

            // Verificar que la pregunta pertenece a la encuesta
            if ($pregunta->encuesta_id != $encuestaId) {
                return redirect()->route('encuestas.preview', $encuestaId)->with('error', 'La pregunta no pertenece a esta encuesta.');
            }

            return redirect()->route('encuestas.preguntas.edit', [$encuestaId, $preguntaId])
                ->with('info', 'Editando pregunta desde la vista previa.');

        } catch (Exception $e) {
            return redirect()->route('encuestas.preview', $encuestaId)->with('error', 'Error al editar la pregunta.');
        }
    }

    /**
     * Eliminar pregunta desde la vista previa
     */
    public function eliminarPregunta($encuestaId, $preguntaId)
    {
        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            $pregunta = Pregunta::findOrFail($preguntaId);

            // Verificar permisos
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para eliminar preguntas de esta encuesta.');
            }

            // Verificar que la pregunta pertenece a la encuesta
            if ($pregunta->encuesta_id != $encuestaId) {
                return redirect()->route('encuestas.preview', $encuestaId)->with('error', 'La pregunta no pertenece a esta encuesta.');
            }

            // Verificar si la encuesta ya ha sido enviada
            if ($encuesta->encuestas_enviadas > 0) {
                return redirect()->route('encuestas.preview', $encuestaId)
                    ->with('error', 'No se pueden eliminar preguntas de una encuesta que ya ha sido enviada.');
            }

            $pregunta->delete();

            return redirect()->route('encuestas.preview', $encuestaId)
                ->with('success', 'Pregunta eliminada correctamente.');

        } catch (Exception $e) {
            return redirect()->route('encuestas.preview', $encuestaId)->with('error', 'Error al eliminar la pregunta.');
        }
    }

    /**
     * Obtener estadísticas de la encuesta
     */
    public function estadisticas($encuestaId)
    {
        try {
            $encuesta = Encuesta::with('preguntas.respuestas')->findOrFail($encuestaId);

            if ($encuesta->user_id !== Auth::id()) {
                return response()->json(['error' => 'No tienes permisos para ver estas estadísticas.'], 403);
            }

            $estadisticas = [
                'total_preguntas' => $encuesta->preguntas->count(),
                'preguntas_obligatorias' => $encuesta->preguntas->where('obligatoria', true)->count(),
                'preguntas_opcionales' => $encuesta->preguntas->where('obligatoria', false)->count(),
                'preguntas_con_respuestas' => $encuesta->preguntas->filter(function($pregunta) {
                    return $pregunta->respuestas->isNotEmpty();
                })->count(),
                'tipos_preguntas' => $encuesta->preguntas->groupBy('tipo')->map->count(),
                'logica_configurada' => $encuesta->preguntas->filter(function($pregunta) {
                    return $pregunta->logica->isNotEmpty();
                })->count()
            ];

            return response()->json($estadisticas);

        } catch (Exception $e) {
            return response()->json(['error' => 'Error al obtener estadísticas.'], 500);
        }
    }
}
