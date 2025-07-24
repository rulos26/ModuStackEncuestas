<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EncuestaPublicaController extends Controller
{
    // Mostrar encuesta pública por slug
    public function mostrar($slug)
    {
        $encuesta = Encuesta::with('preguntas.respuestas')
                    ->where('slug', $slug)
                    ->where('habilitada', true)
                    ->firstOrFail();

        return view('encuestas.publica', compact('encuesta'));
    }

    // Guardar respuestas (opcionalmente implementado aquí)
    public function responder(Request $request, $id)
    {
        $encuesta = Encuesta::with('preguntas')->findOrFail($id);

        foreach ($encuesta->preguntas as $pregunta) {
            if (isset($request->respuestas[$pregunta->id])) {
                $respuestaId = $request->respuestas[$pregunta->id];

                // Aquí puedes guardar cada respuesta en una tabla como "respuestas_usuario"
                // Ejemplo de insert manual (recomendado crear modelo):
                DB::table('respuestas_usuario')->insert([
                    'encuesta_id' => $encuesta->id,
                    'pregunta_id' => $pregunta->id,
                    'respuesta_id' => $respuestaId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('encuestas.publica', $encuesta->slug)
            ->with('success', '¡Gracias por responder la encuesta!');
    }
}
