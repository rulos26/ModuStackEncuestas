<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Empresa;
use App\Http\Requests\EncuestaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EncuestaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $encuestas = Encuesta::with('empresa', 'user')->orderByDesc('created_at')->paginate(10);
        return view('encuestas.index', compact('encuestas'));
    }

    public function show($id)
    {
        $encuesta = Encuesta::with('preguntas.respuestas')->findOrFail($id);
        return view('encuestas.show', compact('encuesta'));
    }

    public function create()
    {
        $empresas = Empresa::all();
        return view('encuestas.create', compact('empresas'));
    }

    public function store(EncuestaRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $encuesta = Encuesta::create($data);
        return redirect()->route('encuestas.show', $encuesta)->with('success', 'Encuesta creada correctamente.');
    }

    public function edit(Encuesta $encuesta)
    {
        $empresas = Empresa::all();
        return view('encuestas.edit', compact('encuesta', 'empresas'));
    }

    public function update(EncuestaRequest $request, Encuesta $encuesta)
    {
        $encuesta->update($request->validated());
        return redirect()->route('encuestas.show', $encuesta)->with('success', 'Encuesta actualizada correctamente.');
    }

    public function destroy(Encuesta $encuesta)
    {
        $encuesta->delete();
        return redirect()->route('encuestas.index')->with('success', 'Encuesta eliminada correctamente.');
    }

    public function clonar($id)
    {
        $original = Encuesta::with('preguntas.respuestas')->findOrFail($id);

        // Clonar la encuesta
        $nuevaEncuesta = $original->replicate();
        $nuevaEncuesta->titulo = $original->titulo . ' (Copia)';
        $nuevaEncuesta->slug = null; // Se generará automáticamente si tienes el boot en el modelo
        $nuevaEncuesta->push();

        // Clonar preguntas y respuestas
        foreach ($original->preguntas as $pregunta) {
            $nuevaPregunta = $pregunta->replicate();
            $nuevaPregunta->encuesta_id = $nuevaEncuesta->id;
            $nuevaPregunta->push();

            foreach ($pregunta->respuestas as $respuesta) {
                $nuevaRespuesta = $respuesta->replicate();
                $nuevaRespuesta->pregunta_id = $nuevaPregunta->id;
                $nuevaRespuesta->save();
            }
        }

        return redirect()->route('encuestas.show', $nuevaEncuesta->id)
            ->with('success', 'Encuesta clonada correctamente.');
    }
}
