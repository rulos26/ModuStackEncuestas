<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Empresa;
use App\Http\Requests\EncuestaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class EncuestaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $encuestas = Encuesta::with(['empresa', 'user'])
                ->orderByDesc('created_at')
                ->paginate(10);
            return view('encuestas.index', compact('encuestas'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar las encuestas: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa', 'user'])->findOrFail($id);
            return view('encuestas.show', compact('encuesta'));
        } catch (Exception $e) {
            return redirect()->route('encuestas.index')->with('error', 'Encuesta no encontrada.');
        }
    }

    public function create()
    {
        try {
            $empresas = Empresa::orderBy('nombre')->get();
            return view('encuestas.create', compact('empresas'));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al cargar el formulario: ' . $e->getMessage());
        }
    }

    public function store(EncuestaRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['user_id'] = Auth::id();
            $data['habilitada'] = $request->has('habilitada');

            $encuesta = Encuesta::create($data);

            DB::commit();

            return redirect()->route('encuestas.show', $encuesta)
                ->with('success', 'Encuesta creada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear la encuesta: ' . $e->getMessage());
        }
    }

    public function edit(Encuesta $encuesta)
    {
        try {
            $empresas = Empresa::orderBy('nombre')->get();
            return view('encuestas.edit', compact('encuesta', 'empresas'));
        } catch (Exception $e) {
            return redirect()->route('encuestas.index')->with('error', 'Error al cargar la encuesta.');
        }
    }

    public function update(EncuestaRequest $request, Encuesta $encuesta)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['habilitada'] = $request->has('habilitada');

            $encuesta->update($data);

            DB::commit();

            return redirect()->route('encuestas.show', $encuesta)
                ->with('success', 'Encuesta actualizada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar la encuesta: ' . $e->getMessage());
        }
    }

    public function destroy(Encuesta $encuesta)
    {
        try {
            DB::beginTransaction();

            // Verificar si la encuesta tiene respuestas antes de eliminar
            if ($encuesta->preguntas()->exists()) {
                return redirect()->back()->with('error', 'No se puede eliminar una encuesta que tiene preguntas. Elimine las preguntas primero.');
            }

            $encuesta->delete();

            DB::commit();

            return redirect()->route('encuestas.index')
                ->with('success', 'Encuesta eliminada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar la encuesta: ' . $e->getMessage());
        }
    }

    /**
     * Clona una encuesta existente con todas sus preguntas y respuestas
     */
    public function clonar($id)
    {
        try {
            DB::beginTransaction();

            $encuestaOriginal = Encuesta::with(['preguntas.respuestas'])->findOrFail($id);

            // Crear nueva encuesta
            $nuevaEncuesta = $encuestaOriginal->replicate();
            $nuevaEncuesta->titulo = $encuestaOriginal->titulo . ' (Copia)';
            $nuevaEncuesta->estado = 'borrador';
            $nuevaEncuesta->habilitada = false;
            $nuevaEncuesta->user_id = Auth::id();
            $nuevaEncuesta->save();

            // Clonar preguntas y respuestas
            foreach ($encuestaOriginal->preguntas as $pregunta) {
                $nuevaPregunta = $pregunta->replicate();
                $nuevaPregunta->encuesta_id = $nuevaEncuesta->id;
                $nuevaPregunta->save();

                // Clonar respuestas de la pregunta
                foreach ($pregunta->respuestas as $respuesta) {
                    $nuevaRespuesta = $respuesta->replicate();
                    $nuevaRespuesta->pregunta_id = $nuevaPregunta->id;
                    $nuevaRespuesta->save();
                }
            }

            DB::commit();

            return redirect()->route('encuestas.show', $nuevaEncuesta)
                ->with('success', 'Encuesta clonada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al clonar la encuesta: ' . $e->getMessage());
        }
    }
}
