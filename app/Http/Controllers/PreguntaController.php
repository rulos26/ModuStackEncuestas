<?php

namespace App\Http\Controllers;

use App\Models\Pregunta;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class PreguntaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create($encuestaId)
    {
        try {
            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);

            // Verificar que la encuesta pertenece al usuario autenticado
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para modificar esta encuesta.');
            }

            return view('encuestas.preguntas.create', compact('encuesta'));
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
                'texto' => 'required|string|max:500|min:3',
                'tipo' => 'required|in:texto,seleccion_unica,seleccion_multiple,numero,fecha',
                'orden' => 'required|integer|min:1',
                'obligatoria' => 'boolean',
            ], [
                'texto.required' => 'El texto de la pregunta es obligatorio.',
                'texto.max' => 'El texto de la pregunta no puede exceder 500 caracteres.',
                'texto.min' => 'El texto de la pregunta debe tener al menos 3 caracteres.',
                'tipo.required' => 'El tipo de pregunta es obligatorio.',
                'tipo.in' => 'El tipo de pregunta seleccionado no es válido.',
                'orden.required' => 'El orden es obligatorio.',
                'orden.integer' => 'El orden debe ser un número entero.',
                'orden.min' => 'El orden debe ser mayor a 0.',
            ]);

            // Verificar que el orden no esté duplicado
            $ordenExistente = Pregunta::where('encuesta_id', $encuestaId)
                ->where('orden', $request->orden)
                ->exists();

            if ($ordenExistente) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Ya existe una pregunta con ese orden. Por favor, elige otro orden.');
            }

            Pregunta::create([
                'encuesta_id' => $encuestaId,
                'texto' => $request->texto,
                'tipo' => $request->tipo,
                'orden' => $request->orden,
                'obligatoria' => $request->has('obligatoria'),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Pregunta agregada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al agregar la pregunta: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar una pregunta
     */
    public function destroy($encuestaId, $preguntaId)
    {
        try {
            DB::beginTransaction();

            $encuesta = Encuesta::findOrFail($encuestaId);
            $pregunta = Pregunta::where('encuesta_id', $encuestaId)
                ->where('id', $preguntaId)
                ->firstOrFail();

            // Verificar permisos - solo el propietario puede eliminar
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->back()->with('error', 'No tienes permisos para eliminar esta pregunta.');
            }

            $pregunta->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Pregunta eliminada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al eliminar la pregunta: ' . $e->getMessage());
        }
    }
}
