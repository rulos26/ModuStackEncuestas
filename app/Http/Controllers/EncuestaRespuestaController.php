<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class EncuestaRespuestaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create($encuestaId)
    {
        try {
            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);

            // Verificar permisos - solo el propietario puede modificar
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para modificar esta encuesta.');
            }

            $preguntas = $encuesta->preguntas()->whereIn('tipo', ['seleccion_unica', 'seleccion_multiple'])->get();

            if ($preguntas->isEmpty()) {
                return redirect()->back()->with('warning', 'No hay preguntas de selección para agregar respuestas.');
            }

            return view('encuestas.respuestas.create', compact('preguntas', 'encuestaId', 'encuesta'));
        } catch (Exception $e) {
            return redirect()->route('encuestas.index')->with('error', 'Encuesta no encontrada.');
        }
    }

    public function store(Request $request, $encuestaId)
    {
        try {
            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Verificar permisos - solo el propietario puede modificar
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para modificar esta encuesta.');
            }

            // Validar que se enviaron respuestas
            if (empty($request->respuestas)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Debe agregar al menos una respuesta.');
            }

            // Aplanar la estructura de respuestas
            $respuestasAplanadas = [];
            foreach ($request->respuestas as $preguntaId => $respuestasPregunta) {
                foreach ($respuestasPregunta as $respuesta) {
                    if (!empty($respuesta['texto'])) {
                        $respuestasAplanadas[] = [
                            'pregunta_id' => $preguntaId,
                            'texto' => trim($respuesta['texto']),
                            'orden' => $respuesta['orden'] ?? 1,
                        ];
                    }
                }
            }

            if (empty($respuestasAplanadas)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Debe agregar al menos una respuesta válida.');
            }

            // Validar datos
            foreach ($respuestasAplanadas as $respuesta) {
                if (strlen($respuesta['texto']) < 1 || strlen($respuesta['texto']) > 255) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El texto de la respuesta debe tener entre 1 y 255 caracteres.');
                }

                if ($respuesta['orden'] < 1) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'El orden debe ser mayor a 0.');
                }
            }

            // Verificar que las preguntas pertenecen a la encuesta
            $preguntasEncuesta = $encuesta->preguntas()->pluck('id')->toArray();
            $preguntasEnRespuestas = array_unique(array_column($respuestasAplanadas, 'pregunta_id'));

            foreach ($preguntasEnRespuestas as $preguntaId) {
                if (!in_array($preguntaId, $preguntasEncuesta)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Una de las preguntas no pertenece a esta encuesta.');
                }
            }

            // Eliminar respuestas existentes para las preguntas que se van a actualizar
            Respuesta::whereIn('pregunta_id', $preguntasEnRespuestas)->delete();

            // Crear nuevas respuestas
            foreach ($respuestasAplanadas as $data) {
                Respuesta::create([
                    'pregunta_id' => $data['pregunta_id'],
                    'texto' => $data['texto'],
                    'orden' => $data['orden'],
                ]);
            }

            DB::commit();

            return redirect()->route('encuestas.logica.create', $encuestaId)
                ->with('success', 'Respuestas guardadas correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar las respuestas: ' . $e->getMessage());
        }
    }
}
