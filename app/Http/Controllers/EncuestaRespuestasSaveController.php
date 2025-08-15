<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class EncuestaRespuestasSaveController extends Controller
{
    //
    public function save(Request $request, $id)
    {

        $encuesta = Encuesta::with(['preguntas.respuestas'])->where('id', $id)->firstOrFail();

        if (empty($request->respuestas)) {
            return redirect()->back()->with('error', 'Debe responder al menos una pregunta.');
        }
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

        Log::info('✅ Respuesta guardada exitosamente', [
            'encuesta_id' => $encuesta->id,
            'encuesta_titulo' => $encuesta->titulo,
            'encuestas_respondidas' => $encuesta->encuestas_respondidas,
            'encuestas_pendientes' => $encuesta->encuestas_pendientes
        ]);

        return redirect()->route('finalizar', $encuesta->slug, $encuesta->id);

        dd(
            'encuesta_id',
            $id,
            'request_data',
            $request->all(),
            'request_url',
            request()->fullUrl(),
            'user_agent',
            request()->userAgent(),
            'ip',
            request()->ip(),
            'timestamp',
            now()->toDateTimeString(),
            'encuesta',
            $encuesta,
            'preguntasObligatorias',
            $preguntasObligatorias,
            'respuestasEnviadas',
            $respuestasEnviadas,
        );
    }

    public function finEncuesta($slug, $id)
    {
        try {
            Log::info('🏁 ENCUESTA - Accediendo a página de fin', [
                'slug' => $slug,
                'encuesta_id' => $id
            ]);

            $encuesta = Encuesta::with(['empresa'])
                ->where('slug', $slug)
                ->where('id', $id)
                ->where('estado', 'publicada')
                ->first();
  dd($encuesta);
            if (!$encuesta) {
                Log::warning('⚠️ ENCUESTA - Encuesta no encontrada para fin', [
                    'slug' => $slug,
                    'encuesta_id' => $id
                ]);

                return view('encuestas.fin', [
                    'encuesta' => null,
                    'error' => 'Encuesta no encontrada o no disponible.'
                ]);
            }

            Log::info('✅ ENCUESTA - Página de fin cargada', [
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo
            ]);

            return view('encuestas.fin', compact('encuesta'));

        } catch (Exception $e) {
            Log::error('❌ ENCUESTA - Error en página de fin', [
                'slug' => $slug,
                'encuesta_id' => $id,
                'error' => $e->getMessage()
            ]);

            return view('encuestas.fin', [
                'encuesta' => null,
                'error' => 'Error al cargar la página de fin.'
            ]);
        }
    }

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
