<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;

class EncuestaRespuestasSaveController extends Controller
{
    //
    public function save(Request $request, $id)
    {
        $encuesta = Encuesta::with(['preguntas.respuestas'])
        ->where('id', $id)
        ->where('habilitada', true)
        ->where('estado', 'publicada')
        ->firstOrFail();
        dd($request->all(),$encuesta,$id);
    }
}
