<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class EncuestaPublicaController extends Controller
{
    /**
     * Mostrar encuesta pública por slug
     */
    public function mostrar($slug)
    {
        try {
            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])
                    ->where('slug', $slug)
                    ->where('habilitada', true)
                ->where('estado', 'publicada')
                    ->firstOrFail();

            // Verificar si la encuesta está disponible
            if (!$encuesta->estaDisponible()) {
                return view('encuestas.publica', [
                    'encuesta' => null,
                    'error' => 'Esta encuesta no está disponible en este momento.'
                ]);
            }

        return view('encuestas.publica', compact('encuesta'));
        } catch (Exception $e) {
            return view('encuestas.publica', [
                'encuesta' => null,
                'error' => 'Encuesta no encontrada o no disponible.'
            ]);
        }
    }

    /**
     * Guardar respuestas de la encuesta pública
     */
    public function responder(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $encuesta = Encuesta::with(['preguntas.respuestas'])
                ->where('id', $id)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->firstOrFail();

            // Verificar si la encuesta está disponible
            if (!$encuesta->estaDisponible()) {
                return redirect()->back()->with('error', 'Esta encuesta no está disponible en este momento.');
            }

            // Validar que se enviaron respuestas
            if (empty($request->respuestas)) {
                return redirect()->back()->with('error', 'Debe responder al menos una pregunta.');
            }

            // Validar respuestas obligatorias
            $preguntasObligatorias = $encuesta->preguntas()->where('obligatoria', true)->pluck('id')->toArray();
            $respuestasEnviadas = array_keys($request->respuestas);

            foreach ($preguntasObligatorias as $preguntaId) {
                if (!in_array($preguntaId, $respuestasEnviadas)) {
                    return redirect()->back()->with('error', 'Debe responder todas las preguntas obligatorias.');
                }
            }

            // Guardar respuestas
            foreach ($request->respuestas as $preguntaId => $respuestaData) {
                // Verificar que la pregunta existe y pertenece a la encuesta
                $pregunta = $encuesta->preguntas()->where('id', $preguntaId)->first();
                if (!$pregunta) {
                    continue;
                }

                $respuestaId = null;
                $respuestaTexto = null;

                // Determinar el tipo de respuesta según el tipo de pregunta
                switch ($pregunta->tipo) {
                    case 'respuesta_corta':
                    case 'parrafo':
                    case 'fecha':
                    case 'hora':
                        // Respuesta de texto libre
                        $respuestaTexto = is_array($respuestaData) ? implode(', ', $respuestaData) : $respuestaData;
                        break;

                    case 'seleccion_unica':
                        // Respuesta de selección única
                        $respuestaId = $respuestaData;
                        break;

                    case 'casillas_verificacion':
                        // Respuesta de múltiple selección
                        if (is_array($respuestaData)) {
                            // Guardar cada selección como una respuesta separada
                            foreach ($respuestaData as $respId) {
                                $this->guardarRespuestaUsuario($encuesta->id, $preguntaId, $respId, null, $request);
                            }
                            continue; // Continuar con la siguiente pregunta
                        } else {
                            $respuestaId = $respuestaData;
                        }
                        break;

                    case 'lista_desplegable':
                        // Respuesta de selección única
                        $respuestaId = $respuestaData;
                        break;

                    case 'escala_lineal':
                        // Respuesta de escala (guardar como texto)
                        $respuestaTexto = $respuestaData;
                        break;

                    default:
                        // Para otros tipos, intentar como texto
                        $respuestaTexto = is_array($respuestaData) ? implode(', ', $respuestaData) : $respuestaData;
                        break;
                }

                // Guardar la respuesta
                $this->guardarRespuestaUsuario($encuesta->id, $preguntaId, $respuestaId, $respuestaTexto, $request);
            }

            DB::commit();

            return redirect()->route('encuestas.publica', $encuesta->slug)
                ->with('success', '¡Gracias por responder la encuesta!');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error guardando respuestas de encuesta pública', [
                'encuesta_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error al procesar las respuestas. Por favor, inténtelo de nuevo.');
        }
    }

    /**
     * Guardar una respuesta de usuario en la base de datos
     */
    private function guardarRespuestaUsuario($encuestaId, $preguntaId, $respuestaId, $respuestaTexto, $request)
    {
        // Verificar que la respuesta existe si es de selección
        if ($respuestaId) {
            $pregunta = \App\Models\Pregunta::find($preguntaId);
            $respuesta = $pregunta->respuestas()->where('id', $respuestaId)->first();
            if (!$respuesta) {
                return false; // Respuesta no válida
            }
        }

        // Guardar respuesta del usuario
        DB::table('respuestas_usuario')->insert([
            'encuesta_id' => $encuestaId,
            'pregunta_id' => $preguntaId,
            'respuesta_id' => $respuestaId,
            'respuesta_texto' => $respuestaTexto,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }
}
