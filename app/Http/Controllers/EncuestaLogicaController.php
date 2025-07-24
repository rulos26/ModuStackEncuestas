<?php


namespace App\Http\Controllers;

use App\Models\Logica;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Http\Request;

class EncuestaLogicaController extends Controller
{
    public function create($encuestaId)
{
    $preguntas = Pregunta::with('respuestas')->where('encuesta_id', $encuestaId)->get();
    return view('encuestas.logica.create', compact('preguntas', 'encuestaId'));
}

public function store(Request $request, $encuestaId)
{
    $request->validate([
        'logicas' => 'required|array',
        'logicas.*.pregunta_id' => 'required|exists:preguntas,id',
        'logicas.*.respuesta_id' => 'required|exists:respuestas,id',
        'logicas.*.siguiente_pregunta_id' => 'nullable|exists:preguntas,id',
        'logicas.*.finalizar' => 'nullable|boolean',
    ]);

    foreach ($request->logicas as $data) {
        Logica::create([
            'pregunta_id' => $data['pregunta_id'],
            'respuesta_id' => $data['respuesta_id'],
            'siguiente_pregunta_id' => $data['siguiente_pregunta_id'] ?? null,
            'finalizar' => $data['finalizar'] ?? false,
        ]);
    }

    return redirect()->route('encuestas.preview', $encuestaId)
                     ->with('success', 'Lógica configurada correctamente.');
}

}