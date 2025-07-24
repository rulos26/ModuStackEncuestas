<?php


namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Http\Request;

class EncuestaRespuestaController extends Controller
{
    public function create($encuestaId)
{
    $preguntas = Pregunta::where('encuesta_id', $encuestaId)->get();
    return view('encuestas.respuestas.create', compact('preguntas', 'encuestaId'));
}

public function store(Request $request, $encuestaId)
{
    $request->validate([
        'respuestas' => 'required|array',
        'respuestas.*.pregunta_id' => 'required|exists:preguntas,id',
        'respuestas.*.texto' => 'required|string',
        'respuestas.*.orden' => 'nullable|integer',
    ]);

    foreach ($request->respuestas as $data) {
        Respuesta::create([
            'pregunta_id' => $data['pregunta_id'],
            'texto' => $data['texto'],
            'orden' => $data['orden'] ?? 1,
        ]);
    }

    return redirect()->route('encuestas.logica.create', $encuestaId)
                     ->with('success', 'Respuestas guardadas correctamente.');
}

}