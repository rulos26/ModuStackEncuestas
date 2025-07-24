<?php


namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Encuesta; // Agrega esta línea
use Illuminate\Http\Request;

class PreguntaController extends Controller
{
  // EncuestaPreguntaController.php

public function create($encuestaId)
{
    $encuesta = Encuesta::findOrFail($encuestaId); // Cambia EncuestaController por Encuesta
    return view('encuestas.preguntas.create', compact('encuesta'));
}

public function store(Request $request, $encuestaId)
{
    $request->validate([
        'texto' => 'required|string',
        'tipo' => 'required|in:texto,seleccion_unica,seleccion_multiple,numero,fecha',
        'orden' => 'required|integer',
        'obligatoria' => 'boolean',
    ]);

    Pregunta::create([
        'encuesta_id' => $encuestaId,
        'texto' => $request->texto,
        'tipo' => $request->tipo,
        'orden' => $request->orden,
        'obligatoria' => $request->obligatoria ?? false,
    ]);

    return redirect()->back()->with('success', 'Pregunta agregada correctamente.');
}

}