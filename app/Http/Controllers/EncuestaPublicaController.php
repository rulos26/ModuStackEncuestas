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
        // 🧪 LOGGING DE PRUEBA - ACCESO A MOSTRAR
        Log::info('🧪 PRUEBA: Acceso a mostrar encuesta', [
            'timestamp' => now(),
            'slug' => $slug,
            'request_url' => request()->url(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

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
        // DEBUG - COMENTADO PERO NO BORRADO

        try {
            DB::beginTransaction();

            // 1. Buscar y validar encuesta
            $encuesta = Encuesta::with(['preguntas.respuestas'])
                ->where('id', $id)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->firstOrFail();

            if (!$encuesta->estaDisponible()) {
                return redirect()->back()->with('error', 'Esta encuesta no está disponible en este momento.');
            }

            // 2. Validar respuestas enviadas
            if (empty($request->respuestas)) {
                return redirect()->back()->with('error', 'Debe responder al menos una pregunta.');
            }

            // 3. Validar preguntas obligatorias
            $preguntasObligatorias = $encuesta->preguntas()->where('obligatoria', true)->pluck('id')->toArray();
            $respuestasEnviadas = array_keys($request->respuestas);

            foreach ($preguntasObligatorias as $preguntaId) {
                if (!in_array($preguntaId, $respuestasEnviadas)) {
                    return redirect()->back()->with('error', 'Debe responder todas las preguntas obligatorias.');
                }
            }

            // 4. Procesar y guardar respuestas
            foreach ($request->respuestas as $preguntaId => $respuestaData) {
                $pregunta = $encuesta->preguntas()->where('id', $preguntaId)->first();
                if (!$pregunta) continue;

                $respuestaId = null;
                $respuestaTexto = null;

                // Determinar tipo de respuesta según tipo de pregunta
                switch ($pregunta->tipo) {
                    case 'respuesta_corta':
                    case 'parrafo':
                    case 'fecha':
                    case 'hora':
                    case 'escala_lineal':
                        $respuestaTexto = is_array($respuestaData) ? implode(', ', $respuestaData) : $respuestaData;
                        break;

                    case 'seleccion_unica':
                    case 'lista_desplegable':
                        $respuestaId = $respuestaData;
                        break;

                    case 'casillas_verificacion':
                        if (is_array($respuestaData)) {
                            // Guardar cada selección como respuesta separada
                            foreach ($respuestaData as $respId) {
                                $this->guardarRespuestaUsuario($encuesta->id, $preguntaId, $respId, null, $request);
                            }
                            continue;
                        } else {
                            $respuestaId = $respuestaData;
                        }
                        break;

                    default:
                        $respuestaTexto = is_array($respuestaData) ? implode(', ', $respuestaData) : $respuestaData;
                        break;
                }

                // Guardar la respuesta
                $this->guardarRespuestaUsuario($encuesta->id, $preguntaId, $respuestaId, $respuestaTexto, $request);
            }

            DB::commit();
            //dd(preguntasObligatorias: $preguntasObligatorias, respuestasEnviadas: $respuestasEnviadas);
            //return redirect()->route('encuestas.fin', $encuesta->slug);
            return redirect()->view('encuestas.fin', compact('encuesta'));
                dd(preguntasObligatorias: $preguntasObligatorias, respuestasEnviadas: $respuestasEnviadas);
        } catch (Exception $e) {
            DB::rollBack();

            // Mostrar error visualmente con detalles
            $errorMessage = 'Error al procesar las respuestas: ' . $e->getMessage();

            return redirect()->back()
                ->with('error', $errorMessage)
                ->with('error_details', [
                    'encuesta_id' => $id,
                    'error_message' => $e->getMessage(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ]);
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

    /**
     * Mostrar página de fin de encuesta
     */
    public function finEncuesta($slug)
    {
        try {
            $encuesta = Encuesta::with(['empresa'])
                ->where('slug', $slug)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->firstOrFail();

            return view('encuestas.fin', compact('encuesta'));

        } catch (Exception $e) {
            return view('encuestas.fin', [
                'encuesta' => null,
                'error' => 'Encuesta no encontrada.'
            ]);
        }
    }
}
