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

            $request->validate([
                'respuestas' => 'required|array',
                'respuestas.*.pregunta_id' => 'required|exists:preguntas,id',
                'respuestas.*.texto' => 'required|string|max:255|min:1',
                'respuestas.*.orden' => 'nullable|integer|min:1',
            ], [
                'respuestas.required' => 'Debe agregar al menos una respuesta.',
                'respuestas.*.pregunta_id.required' => 'La pregunta es obligatoria.',
                'respuestas.*.pregunta_id.exists' => 'La pregunta seleccionada no existe.',
                'respuestas.*.texto.required' => 'El texto de la respuesta es obligatorio.',
                'respuestas.*.texto.max' => 'El texto de la respuesta no puede exceder 255 caracteres.',
                'respuestas.*.texto.min' => 'El texto de la respuesta debe tener al menos 1 carácter.',
                'respuestas.*.orden.integer' => 'El orden debe ser un número entero.',
                'respuestas.*.orden.min' => 'El orden debe ser mayor a 0.',
            ]);

            // Verificar que las preguntas pertenecen a la encuesta
            $preguntasEncuesta = $encuesta->preguntas()->pluck('id')->toArray();

            foreach ($request->respuestas as $respuesta) {
                if (!in_array($respuesta['pregunta_id'], $preguntasEncuesta)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Una de las preguntas no pertenece a esta encuesta.');
                }
            }

            // Eliminar respuestas existentes para las preguntas que se van a actualizar
            $preguntasIds = collect($request->respuestas)->pluck('pregunta_id')->unique();
            Respuesta::whereIn('pregunta_id', $preguntasIds)->delete();

            // Crear nuevas respuestas
            foreach ($request->respuestas as $data) {
                if (!empty($data['texto'])) {
                    Respuesta::create([
                        'pregunta_id' => $data['pregunta_id'],
                        'texto' => trim($data['texto']),
                        'orden' => $data['orden'] ?? 1,
                    ]);
                }
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
