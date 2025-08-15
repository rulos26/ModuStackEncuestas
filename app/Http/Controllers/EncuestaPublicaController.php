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
        // 🔍 DEBUG: Información de entrada
        Log::info('🔍 ENCUESTA PÚBLICA - Iniciando método mostrar', [
            'slug' => $slug,
            'request_url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            // 🔍 DEBUG: Antes de buscar la encuesta
            Log::info('🔍 ENCUESTA PÚBLICA - Buscando encuesta en BD', [
                'slug' => $slug,
                'filtros' => [
                    'habilitada' => true,
                    'estado' => 'publicada'
                ]
            ]);

            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])
                ->where('slug', $slug)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->firstOrFail();

            // 🔍 DEBUG: Encuesta encontrada
            Log::info('✅ ENCUESTA PÚBLICA - Encuesta encontrada', [
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'slug' => $encuesta->slug,
                'estado' => $encuesta->estado,
                'habilitada' => $encuesta->habilitada,
                'empresa_id' => $encuesta->empresa_id,
                'empresa_nombre' => $encuesta->empresa ? $encuesta->empresa->nombre : 'Sin empresa',
                'preguntas_count' => $encuesta->preguntas->count(),
                'fecha_inicio' => $encuesta->fecha_inicio,
                'fecha_fin' => $encuesta->fecha_fin
            ]);

            // Verificar si la encuesta está disponible
            if (!$encuesta->estaDisponible()) {
                Log::warning('⚠️ ENCUESTA PÚBLICA - Encuesta no disponible', [
                    'encuesta_id' => $encuesta->id,
                    'slug' => $encuesta->slug,
                    'fecha_inicio' => $encuesta->fecha_inicio,
                    'fecha_fin' => $encuesta->fecha_fin,
                    'now' => now()->toDateTimeString()
                ]);

                return view('encuestas.publica', [
                    'encuesta' => null,
                    'error' => 'Esta encuesta no está disponible en este momento.'
                ]);
            }

            // 🔍 DEBUG: Encuesta disponible, renderizando vista
            Log::info('✅ ENCUESTA PÚBLICA - Renderizando vista pública', [
                'encuesta_id' => $encuesta->id,
                'preguntas_count' => $encuesta->preguntas->count(),
                'vista' => 'encuestas.publica'
            ]);

            return view('encuestas.publica', compact('encuesta'));
        } catch (Exception $e) {
            // 🔍 DEBUG: Error capturado
            Log::error('❌ ENCUESTA PÚBLICA - Error en método mostrar', [
                'slug' => $slug,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return view('encuestas.publica', [
                'encuesta' => null,
                'error' => 'Encuesta no encontrada o no disponible.'
            ]);
        }
    }

    public function mostrarVistaPublica($encuestaId)
    {
         dd($encuestaId);
        try {
            Log::info('🔍 ENCUESTA PÚBLICA - Mostrando vista por ID', [
                'encuesta_id' => $encuestaId,
                'request_url' => request()->fullUrl(),
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip()
            ]);

            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])
                ->where('id', $encuestaId)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->first();

            if (!$encuesta) {
                Log::warning('❌ ENCUESTA PÚBLICA - Encuesta no encontrada', [
                    'encuesta_id' => $encuestaId
                ]);
                return view('encuestas.publica', [
                    'encuesta' => null,
                    'error' => 'Encuesta no encontrada.'
                ]);
            }

            // Verificar si la encuesta está disponible
            if (!$encuesta->estaDisponible()) {
                Log::warning('⚠️ ENCUESTA PÚBLICA - Encuesta no disponible', [
                    'encuesta_id' => $encuesta->id,
                    'fecha_inicio' => $encuesta->fecha_inicio,
                    'fecha_fin' => $encuesta->fecha_fin
                ]);

                return view('encuestas.publica', [
                    'encuesta' => null,
                    'error' => 'Esta encuesta no está disponible en este momento.'
                ]);
            }

            Log::info('✅ ENCUESTA PÚBLICA - Vista pública renderizada', [
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'preguntas_count' => $encuesta->preguntas->count()
            ]);

            return view('encuestas.publica', compact('encuesta'));

        } catch (Exception $e) {
            Log::error('❌ ENCUESTA PÚBLICA - Error mostrando vista pública', [
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return view('encuestas.publica', [
                'encuesta' => null,
                'error' => 'Error al cargar la encuesta: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mostrar encuesta pública por ID (sin slug)
     */
    public function mostrarPorId($id)
    {
        try {
            // Buscar encuesta por ID sin filtros estrictos para debugging
            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])
                ->where('id', $id)
                ->first();

            if (!$encuesta) {
                Log::warning('❌ ENCUESTA PÚBLICA POR ID - Encuesta no encontrada', [
                    'encuesta_id' => $id
                ]);
                return view('encuestas.publica', [
                    'encuesta' => null,
                    'error' => 'Encuesta no encontrada.'
                ]);
            }

            // Log de información básica
            Log::info('✅ ENCUESTA PÚBLICA POR ID - Encuesta encontrada', [
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'estado' => $encuesta->estado,
                'habilitada' => $encuesta->habilitada,
                'preguntas_count' => $encuesta->preguntas->count()
            ]);

            // Verificar disponibilidad solo si es necesario
            if (!$encuesta->estaDisponible()) {
                Log::warning('⚠️ ENCUESTA PÚBLICA POR ID - Encuesta no disponible', [
                    'encuesta_id' => $encuesta->id,
                    'fecha_inicio' => $encuesta->fecha_inicio,
                    'fecha_fin' => $encuesta->fecha_fin
                ]);

                return view('encuestas.publica', [
                    'encuesta' => null,
                    'error' => 'Esta encuesta no está disponible en este momento.'
                ]);
            }

            return view('encuestas.publica', compact('encuesta'));

        } catch (Exception $e) {
            Log::error('❌ ENCUESTA PÚBLICA POR ID - Error en método mostrarPorId', [
                'encuesta_id' => $id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            return view('encuestas.publica', [
                'encuesta' => null,
                'error' => 'Error al cargar la encuesta: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Mostrar encuesta pública por slug sin verificación de token
     */
    public function mostrarSinToken($slug)
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
        //dd($request->all());

        // 🔍 DEBUG: Información de entrada
        Log::info('🔍 ENCUESTA PÚBLICA - Iniciando método responder', [
            'encuesta_id' => $id,
            'request_data' => $request->all(),
            'request_url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString()
        ]);

        try {
            DB::beginTransaction();

            // 1. Buscar y validar encuesta
            $encuesta = Encuesta::with(['preguntas.respuestas'])
                ->where('id', $id)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->firstOrFail();
            dd($encuesta);
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
                            continue 2; // Continuar con el bucle exterior
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

            // 5. Actualizar contadores de la encuesta
            $this->actualizarContadoresEncuesta($encuesta);

            DB::commit();

            Log::info('✅ Respuesta guardada exitosamente', [
                'encuesta_id' => $encuesta->id,
                'encuesta_titulo' => $encuesta->titulo,
                'encuestas_respondidas' => $encuesta->encuestas_respondidas,
                'encuestas_pendientes' => $encuesta->encuestas_pendientes
            ]);

            return redirect()->route('encuestas.fin', $encuesta->slug);
                //dd(preguntasObligatorias: $preguntasObligatorias, respuestasEnviadas: $respuestasEnviadas);
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
     * Actualizar contadores de la encuesta después de guardar una respuesta
     */
    private function actualizarContadoresEncuesta($encuesta)
    {
        try {
            // Incrementar encuestas respondidas
            $encuesta->increment('encuestas_respondidas');

            // Decrementar encuestas pendientes (pero no menos de 0)
            $encuesta->decrement('encuestas_pendientes');

            // Asegurar que encuestas_pendientes no sea negativo
            if ($encuesta->encuestas_pendientes < 0) {
                $encuesta->update(['encuestas_pendientes' => 0]);
            }

            // Recargar el modelo para obtener los valores actualizados
            $encuesta->refresh();

            Log::info('📊 Contadores de encuesta actualizados', [
                'encuesta_id' => $encuesta->id,
                'encuestas_respondidas' => $encuesta->encuestas_respondidas,
                'encuestas_pendientes' => $encuesta->encuestas_pendientes,
                'numero_encuestas' => $encuesta->numero_encuestas
            ]);

        } catch (Exception $e) {
            Log::error('❌ Error actualizando contadores de encuesta', [
                'encuesta_id' => $encuesta->id,
                'error' => $e->getMessage()
            ]);
            throw $e; // Re-lanzar la excepción para que se maneje en el método principal
        }
    }

    /**
     * Mostrar página de fin de encuesta
     */
    public function finEncuesta($slug)
    {
        try {
            Log::info("🔍 FIN ENCUESTA - Buscando encuesta con slug: {$slug}");

            $encuesta = Encuesta::with(['empresa'])
                ->where('slug', $slug)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->first();

            if (!$encuesta) {
                Log::warning("❌ FIN ENCUESTA - Encuesta no encontrada: {$slug}");
                return view('encuestas.fin', [
                    'encuesta' => null,
                    'error' => 'Encuesta no encontrada.'
                ]);
            }

            Log::info("✅ FIN ENCUESTA - Encuesta encontrada: {$encuesta->titulo}");
            return view('encuestas.fin', compact('encuesta'));

        } catch (Exception $e) {
            Log::error("❌ FIN ENCUESTA - Error: " . $e->getMessage());
            return view('encuestas.fin', [
                'encuesta' => null,
                'error' => 'Error al cargar la encuesta: ' . $e->getMessage()
            ]);
        }
    }
}
