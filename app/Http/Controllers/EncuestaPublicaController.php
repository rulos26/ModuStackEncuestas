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
        // 🧪 LOGGING DE PRUEBA - CONEXIÓN VISTA-CONTROLADOR
        Log::info('🧪 PRUEBA: Conexión vista-controlador establecida', [
            'timestamp' => now(),
            'encuesta_id' => $id,
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'all_data' => $request->all(),
            'respuestas' => $request->input('respuestas'),
            'csrf_token' => $request->input('_token'),
            'session_id' => session()->getId()
        ]);

        try {
            DB::beginTransaction();

            $encuesta = Encuesta::with(['preguntas.respuestas'])
                ->where('id', $id)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->firstOrFail();

            // 🧪 LOGGING DE PRUEBA - ENCUESTA ENCONTRADA
            Log::info('🧪 PRUEBA: Encuesta encontrada', [
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'slug' => $encuesta->slug,
                'estado' => $encuesta->estado,
                'habilitada' => $encuesta->habilitada,
                'preguntas_count' => $encuesta->preguntas->count()
            ]);

            // Verificar si la encuesta está disponible
            if (!$encuesta->estaDisponible()) {
                Log::warning('🧪 PRUEBA: Encuesta no disponible', [
                    'encuesta_id' => $encuesta->id,
                    'esta_disponible' => $encuesta->estaDisponible()
                ]);
                return redirect()->back()->with('error', 'Esta encuesta no está disponible en este momento.');
            }

            // Validar que se enviaron respuestas
            if (empty($request->respuestas)) {
                Log::warning('🧪 PRUEBA: No se enviaron respuestas', [
                    'encuesta_id' => $encuesta->id,
                    'respuestas_enviadas' => $request->respuestas
                ]);
                return redirect()->back()->with('error', 'Debe responder al menos una pregunta.');
            }

            // 🧪 LOGGING DE PRUEBA - RESPUESTAS RECIBIDAS
            Log::info('🧪 PRUEBA: Respuestas recibidas', [
                'encuesta_id' => $encuesta->id,
                'respuestas_count' => count($request->respuestas),
                'respuestas_detalle' => $request->respuestas
            ]);

            // Validar respuestas obligatorias
            $preguntasObligatorias = $encuesta->preguntas()->where('obligatoria', true)->pluck('id')->toArray();
            $respuestasEnviadas = array_keys($request->respuestas);

            // 🧪 LOGGING DE PRUEBA - VALIDACIÓN OBLIGATORIAS
            Log::info('🧪 PRUEBA: Validación preguntas obligatorias', [
                'encuesta_id' => $encuesta->id,
                'preguntas_obligatorias' => $preguntasObligatorias,
                'respuestas_enviadas' => $respuestasEnviadas,
                'todas_respondidas' => empty(array_diff($preguntasObligatorias, $respuestasEnviadas))
            ]);

            foreach ($preguntasObligatorias as $preguntaId) {
                if (!in_array($preguntaId, $respuestasEnviadas)) {
                    Log::warning('🧪 PRUEBA: Pregunta obligatoria sin responder', [
                        'encuesta_id' => $encuesta->id,
                        'pregunta_id' => $preguntaId
                    ]);
                    return redirect()->back()->with('error', 'Debe responder todas las preguntas obligatorias.');
                }
            }

            // Guardar respuestas
            $respuestasGuardadas = 0;
            foreach ($request->respuestas as $preguntaId => $respuestaData) {
                // Verificar que la pregunta existe y pertenece a la encuesta
                $pregunta = $encuesta->preguntas()->where('id', $preguntaId)->first();
                if (!$pregunta) {
                    Log::warning('🧪 PRUEBA: Pregunta no encontrada', [
                        'encuesta_id' => $encuesta->id,
                        'pregunta_id' => $preguntaId
                    ]);
                    continue;
                }

                $respuestaId = null;
                $respuestaTexto = null;

                // 🧪 LOGGING DE PRUEBA - PROCESANDO RESPUESTA
                Log::info('🧪 PRUEBA: Procesando respuesta', [
                    'encuesta_id' => $encuesta->id,
                    'pregunta_id' => $preguntaId,
                    'pregunta_tipo' => $pregunta->tipo,
                    'respuesta_data' => $respuestaData
                ]);

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
                                $respuestasGuardadas++;
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
                if ($this->guardarRespuestaUsuario($encuesta->id, $preguntaId, $respuestaId, $respuestaTexto, $request)) {
                    $respuestasGuardadas++;
                }
            }

            // 🧪 LOGGING DE PRUEBA - RESPUESTAS GUARDADAS
            Log::info('🧪 PRUEBA: Respuestas guardadas exitosamente', [
                'encuesta_id' => $encuesta->id,
                'respuestas_guardadas' => $respuestasGuardadas,
                'total_respuestas' => count($request->respuestas)
            ]);

            DB::commit();

            return redirect()->route('encuestas.publica', $encuesta->slug)
                ->with('success', '¡Gracias por responder la encuesta!');

        } catch (Exception $e) {
            DB::rollBack();

            // 🧪 LOGGING DE PRUEBA - ERROR
            Log::error('🧪 PRUEBA: Error en responder encuesta', [
                'encuesta_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()->back()->with('error', 'Error al procesar las respuestas. Por favor, inténtelo de nuevo.');
        }
    }

    /**
     * Guardar una respuesta de usuario en la base de datos
     */
    private function guardarRespuestaUsuario($encuestaId, $preguntaId, $respuestaId, $respuestaTexto, $request)
    {
        // 🧪 LOGGING DE PRUEBA - GUARDANDO RESPUESTA
        Log::info('🧪 PRUEBA: Guardando respuesta usuario', [
            'encuesta_id' => $encuestaId,
            'pregunta_id' => $preguntaId,
            'respuesta_id' => $respuestaId,
            'respuesta_texto' => $respuestaTexto,
            'ip' => $request->ip()
        ]);

        // Verificar que la respuesta existe si es de selección
        if ($respuestaId) {
            $pregunta = \App\Models\Pregunta::find($preguntaId);
            $respuesta = $pregunta->respuestas()->where('id', $respuestaId)->first();
            if (!$respuesta) {
                Log::warning('🧪 PRUEBA: Respuesta no válida', [
                    'encuesta_id' => $encuestaId,
                    'pregunta_id' => $preguntaId,
                    'respuesta_id' => $respuestaId
                ]);
                return false; // Respuesta no válida
            }
        }

        try {
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

            Log::info('🧪 PRUEBA: Respuesta guardada exitosamente', [
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaId
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('🧪 PRUEBA: Error guardando respuesta', [
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
