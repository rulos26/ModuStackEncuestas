<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;

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
}
