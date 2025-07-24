<?php


namespace App\Http\Controllers;

use App\Models\Encuesta;

class EncuestaPreviewController extends Controller
{
    public function preview($id)
    {
        $encuesta = Encuesta::with('preguntas.respuestas')->findOrFail($id);
        return view('encuestas.preview', compact('encuesta'));
    }
}