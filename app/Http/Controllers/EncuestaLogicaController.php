<?php


namespace App\Http\Controllers;

use App\Models\Logica;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class EncuestaLogicaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create($encuestaId)
    {
        try {
            $encuesta = Encuesta::with('preguntas.respuestas')->findOrFail($encuestaId);

            // Verificar permisos - solo el propietario puede modificar
            if ($encuesta->user_id !== Auth::id()) {
                return redirect()->route('encuestas.index')->with('error', 'No tienes permisos para modificar esta encuesta.');
            }

            $preguntas = $encuesta->preguntas()->with('respuestas')->get();

            if ($preguntas->isEmpty()) {
                return redirect()->back()->with('warning', 'No hay preguntas configuradas para establecer lógica.');
            }

            return view('encuestas.logica.create', compact('preguntas', 'encuestaId', 'encuesta'));
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
                'logicas' => 'required|array',
                'logicas.*.pregunta_id' => 'required|exists:preguntas,id',
                'logicas.*.respuesta_id' => 'required|exists:respuestas,id',
                'logicas.*.siguiente_pregunta_id' => 'nullable|exists:preguntas,id',
                'logicas.*.finalizar' => 'nullable|boolean',
            ], [
                'logicas.required' => 'Debe configurar al menos una lógica.',
                'logicas.*.pregunta_id.required' => 'La pregunta es obligatoria.',
                'logicas.*.pregunta_id.exists' => 'La pregunta seleccionada no existe.',
                'logicas.*.respuesta_id.required' => 'La respuesta es obligatoria.',
                'logicas.*.respuesta_id.exists' => 'La respuesta seleccionada no existe.',
                'logicas.*.siguiente_pregunta_id.exists' => 'La pregunta de destino no existe.',
                'logicas.*.finalizar.boolean' => 'El campo finalizar debe ser verdadero o falso.',
            ]);

            // Verificar que las preguntas y respuestas pertenecen a la encuesta
            $preguntasEncuesta = $encuesta->preguntas()->pluck('id')->toArray();
            $respuestasEncuesta = Respuesta::whereIn('pregunta_id', $preguntasEncuesta)->pluck('id')->toArray();

            foreach ($request->logicas as $logica) {
                if (!in_array($logica['pregunta_id'], $preguntasEncuesta)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Una de las preguntas no pertenece a esta encuesta.');
                }

                if (!in_array($logica['respuesta_id'], $respuestasEncuesta)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Una de las respuestas no pertenece a esta encuesta.');
                }

                if (!empty($logica['siguiente_pregunta_id']) && !in_array($logica['siguiente_pregunta_id'], $preguntasEncuesta)) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'La pregunta de destino no pertenece a esta encuesta.');
                }
            }

            // Eliminar lógica existente para esta encuesta
            $preguntasIds = $encuesta->preguntas()->pluck('id')->toArray();
            Logica::whereIn('pregunta_id', $preguntasIds)->delete();

            // Crear nueva lógica
            foreach ($request->logicas as $data) {
                if (!empty($data['pregunta_id']) && !empty($data['respuesta_id'])) {
                    Logica::create([
                        'pregunta_id' => $data['pregunta_id'],
                        'respuesta_id' => $data['respuesta_id'],
                        'siguiente_pregunta_id' => $data['siguiente_pregunta_id'] ?? null,
                        'finalizar' => $data['finalizar'] ?? false,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('encuestas.preview', $encuestaId)
                ->with('success', 'Lógica configurada correctamente.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al configurar la lógica: ' . $e->getMessage());
        }
    }
}
