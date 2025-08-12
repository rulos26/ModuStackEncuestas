<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\RespuestaUsuario;
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
     * Paso 1: Selección de encuesta para responder
     */
    public function index()
    {
        try {
            // Obtener encuestas disponibles para responder
            $encuestas = Encuesta::with(['empresa', 'preguntas'])
                ->where('estado', 'publicada')
                ->where('habilitada', true)
                ->orderBy('created_at', 'desc')
                ->get();

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
     * Paso 2: Mostrar pregunta para responder
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

            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);

            // Verificar que la encuesta esté habilitada
            if (!$encuesta->habilitada || $encuesta->estado !== 'publicada') {
                return redirect()->route('respuestas.wizard.index')
                    ->with('error', 'Esta encuesta no está disponible para responder.');
            }

            // Guardar encuesta seleccionada en sesión si no existe
            if (!Session::has('wizard_encuesta_id')) {
                Session::put('wizard_encuesta_id', $encuestaId);
            }

            // Obtener preguntas ordenadas
            $preguntas = $encuesta->preguntas()->orderBy('orden')->get();

            if ($preguntas->isEmpty()) {
                return redirect()->route('respuestas.wizard.index')
                    ->with('error', 'Esta encuesta no tiene preguntas configuradas.');
            }

            // Obtener índice de la pregunta actual
            $preguntaIndex = Session::get('wizard_pregunta_index', 0);

            if ($preguntaIndex >= $preguntas->count()) {
                // Todas las preguntas han sido respondidas
                return redirect()->route('respuestas.wizard.resumen');
            }

            $preguntaActual = $preguntas[$preguntaIndex];
            $totalPreguntas = $preguntas->count();

            Log::info('Acceso al wizard de respuestas', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_index' => $preguntaIndex,
                'total_preguntas' => $totalPreguntas
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
     * Paso 3: Guardar respuesta y avanzar
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

            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);
            $preguntas = $encuesta->preguntas()->orderBy('orden')->get();

            if ($preguntaIndex >= $preguntas->count()) {
                return redirect()->route('respuestas.wizard.resumen');
            }

            $preguntaActual = $preguntas[$preguntaIndex];

            // Validar respuesta según el tipo de pregunta
            $this->validarRespuesta($request, $preguntaActual);

            // Guardar respuesta
            $respuestaUsuario = $this->guardarRespuesta($request, $encuestaId, $preguntaActual);

            // Incrementar contador de respuestas
            $respuestasCount = Session::get('wizard_respuestas_count', 0) + 1;
            Session::put('wizard_respuestas_count', $respuestasCount);

            // Avanzar al siguiente índice
            $siguienteIndex = $preguntaIndex + 1;
            Session::put('wizard_pregunta_index', $siguienteIndex);

            DB::commit();

            Log::info('Respuesta guardada en wizard', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaActual->id,
                'respuesta_id' => $respuestaUsuario->id,
                'respuestas_en_sesion' => $respuestasCount
            ]);

            // Verificar si es la última pregunta
            if ($siguienteIndex >= $preguntas->count()) {
                return redirect()->route('respuestas.wizard.resumen')
                    ->with('success', '¡Has completado todas las preguntas!');
            }

            return redirect()->route('respuestas.wizard.responder')
                ->with('success', 'Respuesta guardada correctamente. Continuando con la siguiente pregunta.');

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error guardando respuesta en wizard', [
                'user_id' => Auth::id(),
                'encuesta_id' => Session::get('wizard_encuesta_id'),
                'data' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar la respuesta: ' . $e->getMessage());
        }
    }

    /**
     * Paso 4: Resumen de respuestas
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

            $encuesta = Encuesta::with('preguntas')->findOrFail($encuestaId);

            // Obtener todas las respuestas del usuario para esta encuesta
            $respuestasUsuario = RespuestaUsuario::where('encuesta_id', $encuestaId)
                ->where('ip_address', request()->ip())
                ->with(['pregunta', 'respuesta'])
                ->orderBy('created_at')
                ->get();

            return view('respuestas.wizard.resumen', compact('encuesta', 'respuestasUsuario', 'respuestasCount'));

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
                Log::info('Wizard de respuestas finalizado', [
                    'user_id' => Auth::id(),
                    'encuesta_id' => $encuestaId,
                    'respuestas_creadas' => $respuestasCount
                ]);

                // Limpiar sesión
                Session::forget(['wizard_encuesta_id', 'wizard_pregunta_index', 'wizard_respuestas_count']);

                return redirect()->route('respuestas.wizard.index')
                    ->with('success', "¡Encuesta completada exitosamente! Has respondido {$respuestasCount} pregunta(s).");
            } else {
                // Iniciar otra encuesta
                Session::forget(['wizard_encuesta_id', 'wizard_pregunta_index', 'wizard_respuestas_count']);

                return redirect()->route('respuestas.wizard.index')
                    ->with('info', 'Puedes seleccionar otra encuesta para responder.');
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

            Log::info('Wizard de respuestas cancelado', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'respuestas_en_sesion' => $respuestasCount
            ]);

            return redirect()->route('respuestas.wizard.index')
                ->with('info', 'Wizard cancelado. Tus respuestas parciales se han guardado.');

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
     * Validar respuesta según el tipo de pregunta
     */
    private function validarRespuesta(Request $request, Pregunta $pregunta)
    {
        $rules = [];
        $messages = [];

        switch ($pregunta->tipo) {
            case 'respuesta_corta':
            case 'parrafo':
                $rules['respuesta_texto'] = 'required|string';
                if ($pregunta->min_caracteres) {
                    $rules['respuesta_texto'] .= '|min:' . $pregunta->min_caracteres;
                }
                if ($pregunta->max_caracteres) {
                    $rules['respuesta_texto'] .= '|max:' . $pregunta->max_caracteres;
                }
                $messages['respuesta_texto.required'] = 'Debes responder esta pregunta.';
                break;

            case 'seleccion_unica':
                $rules['respuesta_id'] = 'required|exists:respuestas,id';
                $messages['respuesta_id.required'] = 'Debes seleccionar una opción.';
                break;

            case 'casillas_verificacion':
                $rules['respuesta_ids'] = 'required|array|min:1';
                $rules['respuesta_ids.*'] = 'exists:respuestas,id';
                $messages['respuesta_ids.required'] = 'Debes seleccionar al menos una opción.';
                break;

            case 'escala_lineal':
                $rules['respuesta_escala'] = 'required|integer|between:' . $pregunta->escala_min . ',' . $pregunta->escala_max;
                $messages['respuesta_escala.required'] = 'Debes seleccionar un valor en la escala.';
                break;

            case 'fecha':
                $rules['respuesta_fecha'] = 'required|date';
                $messages['respuesta_fecha.required'] = 'Debes seleccionar una fecha.';
                break;

            case 'hora':
                $rules['respuesta_hora'] = 'required|date_format:H:i';
                $messages['respuesta_hora.required'] = 'Debes seleccionar una hora.';
                break;

            default:
                $rules['respuesta_texto'] = 'required|string';
                $messages['respuesta_texto.required'] = 'Debes responder esta pregunta.';
        }

        $request->validate($rules, $messages);
    }

    /**
     * Guardar respuesta en la base de datos
     */
    private function guardarRespuesta(Request $request, int $encuestaId, Pregunta $pregunta)
    {
        $data = [
            'encuesta_id' => $encuestaId,
            'pregunta_id' => $pregunta->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        switch ($pregunta->tipo) {
            case 'respuesta_corta':
            case 'parrafo':
                $data['respuesta_texto'] = $request->respuesta_texto;
                break;

            case 'seleccion_unica':
                $data['respuesta_id'] = $request->respuesta_id;
                break;

            case 'casillas_verificacion':
                // Para casillas de verificación, crear múltiples registros
                $respuestasIds = $request->respuesta_ids;
                foreach ($respuestasIds as $respuestaId) {
                    RespuestaUsuario::create([
                        'encuesta_id' => $encuestaId,
                        'pregunta_id' => $pregunta->id,
                        'respuesta_id' => $respuestaId,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                }
                return null; // Ya se crearon los registros

            case 'escala_lineal':
                $data['respuesta_texto'] = $request->respuesta_escala;
                break;

            case 'fecha':
                $data['respuesta_texto'] = $request->respuesta_fecha;
                break;

            case 'hora':
                $data['respuesta_texto'] = $request->respuesta_hora;
                break;

            default:
                $data['respuesta_texto'] = $request->respuesta_texto;
        }

        return RespuestaUsuario::create($data);
    }
}
