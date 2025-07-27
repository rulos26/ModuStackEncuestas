<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            foreach ($request->respuestas as $preguntaId => $respuestaId) {
                // Verificar que la pregunta existe y pertenece a la encuesta
                $pregunta = $encuesta->preguntas()->where('id', $preguntaId)->first();
                if (!$pregunta) {
                    continue;
                }

                // Verificar que la respuesta existe y pertenece a la pregunta
                $respuesta = $pregunta->respuestas()->where('id', $respuestaId)->first();
                if (!$respuesta) {
                    continue;
                }

                // Guardar respuesta del usuario
                DB::table('respuestas_usuario')->insert([
                    'encuesta_id' => $encuesta->id,
                    'pregunta_id' => $preguntaId,
                    'respuesta_id' => $respuestaId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('encuestas.publica', $encuesta->slug)
                ->with('success', '¡Gracias por responder la encuesta!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al procesar las respuestas. Por favor, inténtelo de nuevo.');
        }
    }
}
