<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Exception;

class RespuestaWizardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Paso 1: Listado de encuestas para agregar respuestas
     */
    public function index()
    {
        try {
            // Obtener todas las encuestas sin filtro
            $encuestas = Encuesta::with(['empresa', 'preguntas.respuestas'])

                ->orderBy('created_at', 'desc')
                ->get();
//dd($encuestas);
            // Inicializar contador de sesión si no existe
            if (!Session::has('wizard_respuestas_count')) {
                Session::put('wizard_respuestas_count', 0);
            }

            return view('respuestas.wizard.index', compact('encuestas'));
        } catch (Exception $e) {
            Log::error('Error en wizard de respuestas - index', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar el wizard: ' . $e->getMessage());
        }
    }

    /**
     * Paso 2: Mostrar pregunta para agregar respuestas
     */
    public function responder(Request $request)
    {
        try {
            // Obtener encuesta ID desde request o sesión
            $encuestaId = $request->get('encuesta_id') ?? Session::get('wizard_encuesta_id');

            if (!$encuestaId) {
                return redirect()->route('respuestas.wizard.index')
                    ->with('error', 'Debes seleccionar una encuesta.');
            }

            $encuesta = Encuesta::with(['preguntas.respuestas'])->findOrFail($encuestaId);

            // Guardar encuesta seleccionada en sesión si no existe
            if (!Session::has('wizard_encuesta_id')) {
                Session::put('wizard_encuesta_id', $encuestaId);
            }

            // Obtener preguntas que necesitan respuestas (selección única, casillas y selección múltiple)
            $preguntas = $encuesta->preguntas()
                ->whereIn('tipo', ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple'])
                ->whereDoesntHave('respuestas')
                ->orderBy('orden')
                ->get();

            if ($preguntas->isEmpty()) {
                return redirect()->route('respuestas.wizard.index')
                    ->with('success', 'Todas las preguntas de esta encuesta ya tienen respuestas configuradas.');
            }

            // Obtener índice de la pregunta actual
            $preguntaIndex = Session::get('wizard_pregunta_index', 0);

            if ($preguntaIndex >= $preguntas->count()) {
                // Todas las preguntas han sido procesadas
                return redirect()->route('respuestas.wizard.resumen');
            }

            $preguntaActual = $preguntas[$preguntaIndex];
            $totalPreguntas = $preguntas->count();

            Log::info('Acceso al wizard de respuestas administrativo', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_index' => $preguntaIndex,
                'total_preguntas' => $totalPreguntas,
                'pregunta_tipo' => $preguntaActual->tipo
            ]);

            return view('respuestas.wizard.responder', compact('encuesta', 'preguntaActual', 'preguntaIndex', 'totalPreguntas'));

        } catch (Exception $e) {
            Log::error('Error en wizard de respuestas - responder', [
                'user_id' => Auth::id(),
                'encuesta_id' => $request->get('encuesta_id'),
                'error' => $e->getMessage()
            ]);
            return redirect()->route('respuestas.wizard.index')
                ->with('error', 'Error al cargar la pregunta: ' . $e->getMessage());
        }
    }

    /**
     * Paso 3: Guardar respuestas y avanzar
     */
    public function store(Request $request)
    {
        try {
            $encuestaId = Session::get('wizard_encuesta_id');
            $preguntaIndex = Session::get('wizard_pregunta_index', 0);

            if (!$encuestaId) {
                return redirect()->route('respuestas.wizard.index')
                    ->with('error', 'Sesión de wizard expirada. Selecciona una encuesta nuevamente.');
            }

            DB::beginTransaction();

            $encuesta = Encuesta::with(['preguntas.respuestas'])->findOrFail($encuestaId);

            // Obtener preguntas que necesitan respuestas
            $preguntas = $encuesta->preguntas()
                ->whereIn('tipo', ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple'])
                ->whereDoesntHave('respuestas')
                ->orderBy('orden')
                ->get();

            if ($preguntaIndex >= $preguntas->count()) {
                return redirect()->route('respuestas.wizard.resumen');
            }

            $preguntaActual = $preguntas[$preguntaIndex];

            // Validar respuestas según el tipo de pregunta
            $this->validarRespuestas($request, $preguntaActual);

            // Guardar respuestas
            $respuestasCreadas = $this->guardarRespuestas($request, $preguntaActual);

            // Incrementar contador de respuestas
            $respuestasCount = Session::get('wizard_respuestas_count', 0) + $respuestasCreadas;
            Session::put('wizard_respuestas_count', $respuestasCount);

            // Avanzar al siguiente índice
            $siguienteIndex = $preguntaIndex + 1;
            Session::put('wizard_pregunta_index', $siguienteIndex);

            DB::commit();

            Log::info('Respuestas guardadas en wizard administrativo', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaActual->id,
                'respuestas_creadas' => $respuestasCreadas,
                'respuestas_en_sesion' => $respuestasCount
            ]);

            // Verificar si es la última pregunta
            if ($siguienteIndex >= $preguntas->count()) {
                return redirect()->route('respuestas.wizard.resumen')
                    ->with('success', '¡Has configurado todas las respuestas!');
            }

            // Volver a mostrar la siguiente pregunta
            return redirect()->route('respuestas.wizard.responder')
                ->with('success', "Se agregaron {$respuestasCreadas} respuesta(s). Continuando con la siguiente pregunta.");

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error guardando respuestas en wizard', [
                'user_id' => Auth::id(),
                'encuesta_id' => Session::get('wizard_encuesta_id'),
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar las respuestas: ' . $e->getMessage());
        }
    }

    /**
     * Paso 4: Resumen de respuestas agregadas
     */
    public function resumen()
    {
        try {
            $encuestaId = Session::get('wizard_encuesta_id');
            $respuestasCount = Session::get('wizard_respuestas_count', 0);

            if (!$encuestaId) {
                return redirect()->route('respuestas.wizard.index')
                    ->with('error', 'Sesión de wizard expirada.');
            }

            $encuesta = Encuesta::with(['preguntas.respuestas'])->findOrFail($encuestaId);

            // Obtener preguntas con sus respuestas recién agregadas
            $preguntasConRespuestas = $encuesta->preguntas()
                ->whereIn('tipo', ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple'])
                ->with('respuestas')
                ->orderBy('orden')
                ->get();

            return view('respuestas.wizard.resumen', compact('encuesta', 'preguntasConRespuestas', 'respuestasCount'));

        } catch (Exception $e) {
            Log::error('Error en resumen del wizard', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('respuestas.wizard.index')
                ->with('error', 'Error al cargar el resumen: ' . $e->getMessage());
        }
    }

    /**
     * Paso 5: Confirmar y finalizar
     */
    public function confirmar(Request $request)
    {
        try {
            $action = $request->get('action');
            $encuestaId = Session::get('wizard_encuesta_id');
            $respuestasCount = Session::get('wizard_respuestas_count', 0);

            if (!$encuestaId) {
                return redirect()->route('respuestas.wizard.index')
                    ->with('error', 'Sesión de wizard expirada.');
            }

            $encuesta = Encuesta::findOrFail($encuestaId);

            if ($action === 'finish') {
                // Finalizar wizard
                Log::info('Wizard de respuestas administrativo finalizado', [
                    'user_id' => Auth::id(),
                    'encuesta_id' => $encuestaId,
                    'respuestas_creadas' => $respuestasCount
                ]);

                // Limpiar sesión
                Session::forget(['wizard_encuesta_id', 'wizard_pregunta_index', 'wizard_respuestas_count']);

                return redirect()->route('respuestas.wizard.index')
                    ->with('success', "¡Configuración completada! Se agregaron {$respuestasCount} respuesta(s) a la encuesta.");
            } else {
                // Iniciar otra encuesta
                Session::forget(['wizard_encuesta_id', 'wizard_pregunta_index', 'wizard_respuestas_count']);

                return redirect()->route('respuestas.wizard.index')
                    ->with('info', 'Puedes seleccionar otra encuesta para configurar respuestas.');
            }

        } catch (Exception $e) {
            Log::error('Error en confirmación del wizard', [
                'user_id' => Auth::id(),
                'action' => $request->get('action'),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('respuestas.wizard.index')
                ->with('error', 'Error en la confirmación: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar wizard
     */
    public function cancel()
    {
        try {
            $encuestaId = Session::get('wizard_encuesta_id');
            $respuestasCount = Session::get('wizard_respuestas_count', 0);

            // Limpiar sesión
            Session::forget(['wizard_encuesta_id', 'wizard_pregunta_index', 'wizard_respuestas_count']);

            Log::info('Wizard de respuestas administrativo cancelado', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'respuestas_en_sesion' => $respuestasCount
            ]);

            return redirect()->route('respuestas.wizard.index')
                ->with('info', 'Wizard cancelado. Las respuestas agregadas se han guardado.');

        } catch (Exception $e) {
            Log::error('Error cancelando wizard', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('respuestas.wizard.index')
                ->with('error', 'Error al cancelar: ' . $e->getMessage());
        }
    }

    /**
     * Validar respuestas según el tipo de pregunta
     */
    private function validarRespuestas(Request $request, Pregunta $pregunta)
    {
        $rules = [];
        $messages = [];

        switch ($pregunta->tipo) {
            case 'seleccion_unica':
                $rules['respuestas'] = 'required|array|min:1';
                $rules['respuestas.*.texto'] = 'required|string|max:255';
                $rules['respuestas.*.orden'] = 'required|integer|min:1';
                $messages['respuestas.required'] = 'Debes agregar al menos una opción de respuesta.';
                $messages['respuestas.*.texto.required'] = 'El texto de la respuesta es obligatorio.';
                break;

            case 'casillas_verificacion':
                $rules['respuestas'] = 'required|array|min:1';
                $rules['respuestas.*.texto'] = 'required|string|max:255';
                $rules['respuestas.*.orden'] = 'required|integer|min:1';
                $messages['respuestas.required'] = 'Debes agregar al menos una opción de respuesta.';
                $messages['respuestas.*.texto.required'] = 'El texto de la respuesta es obligatorio.';
                break;

            case 'seleccion_multiple':
                $rules['respuestas'] = 'required|array|min:1';
                $rules['respuestas.*.texto'] = 'required|string|max:255';
                $rules['respuestas.*.orden'] = 'required|integer|min:1';
                $messages['respuestas.required'] = 'Debes agregar al menos una opción de respuesta.';
                $messages['respuestas.*.texto.required'] = 'El texto de la respuesta es obligatorio.';
                break;

            default:
                throw new Exception('Tipo de pregunta no soportado para agregar respuestas.');
        }

        $request->validate($rules, $messages);
    }

    /**
     * Guardar respuestas en la base de datos
     */
    private function guardarRespuestas(Request $request, Pregunta $pregunta)
    {
        $respuestasCreadas = 0;
        $respuestas = $request->respuestas;

        foreach ($respuestas as $respuestaData) {
            if (!empty($respuestaData['texto'])) {
                Respuesta::create([
                    'pregunta_id' => $pregunta->id,
                    'texto' => $respuestaData['texto'],
                    'orden' => $respuestaData['orden'],
                    'activa' => true,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
                $respuestasCreadas++;
            }
        }

        return $respuestasCreadas;
    }
}
