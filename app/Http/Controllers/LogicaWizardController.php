<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Logica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Exception;

class LogicaWizardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Paso 1: Seleccionar encuesta para configurar lógica
     */
    public function index()
    {
        try {
            // Obtener encuestas que tienen preguntas con respuestas configuradas
            $encuestas = Encuesta::with(['empresa', 'preguntas.respuestas'])
                ->whereHas('preguntas.respuestas')
                ->orderBy('created_at', 'desc')
                ->get();

            // Inicializar contador de sesión si no existe
            if (!Session::has('wizard_logica_count')) {
                Session::put('wizard_logica_count', 0);
            }

            return view('logica.wizard.index', compact('encuestas'));
        } catch (Exception $e) {
            Log::error('Error en wizard de lógica - index', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar el wizard: ' . $e->getMessage());
        }
    }

    /**
     * Paso 2: Mostrar pregunta para configurar lógica
     */
    public function configurar(Request $request)
    {
        try {
            // Obtener encuesta ID desde request o sesión
            $encuestaId = $request->get('encuesta_id') ?? Session::get('wizard_encuesta_id');

            if (!$encuestaId) {
                return redirect()->route('logica.wizard.index')
                    ->with('error', 'Debes seleccionar una encuesta.');
            }

            $encuesta = Encuesta::with(['preguntas.respuestas.logica'])->findOrFail($encuestaId);

            // Guardar encuesta seleccionada en sesión si no existe
            if (!Session::has('wizard_encuesta_id')) {
                Session::put('wizard_encuesta_id', $encuestaId);
            }

            // Obtener preguntas que permiten lógica (selección única, casillas y selección múltiple)
            $preguntas = $encuesta->preguntas()
                ->whereIn('tipo', ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple'])
                ->whereHas('respuestas')
                ->orderBy('orden')
                ->get();

            if ($preguntas->isEmpty()) {
                return redirect()->route('logica.wizard.index')
                    ->with('warning', 'Esta encuesta no tiene preguntas que permitan configurar lógica de salto.');
            }

            // Obtener índice de la pregunta actual
            $preguntaIndex = Session::get('wizard_pregunta_index', 0);

            if ($preguntaIndex >= $preguntas->count()) {
                // Todas las preguntas han sido procesadas
                return redirect()->route('logica.wizard.resumen');
            }

            $preguntaActual = $preguntas[$preguntaIndex];
            $totalPreguntas = $preguntas->count();

            // Obtener lógica existente para esta pregunta
            $logicaExistente = $preguntaActual->logica;

            // Obtener preguntas de destino (excluyendo la actual)
            $preguntasDestino = $preguntas->where('id', '!=', $preguntaActual->id);

            Log::info('Acceso al wizard de lógica', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_actual' => $preguntaActual->id,
                'pregunta_index' => $preguntaIndex,
                'total_preguntas' => $totalPreguntas
            ]);

            return view('logica.wizard.configurar', compact(
                'encuesta',
                'preguntaActual',
                'preguntaIndex',
                'totalPreguntas',
                'logicaExistente',
                'preguntasDestino'
            ));
        } catch (Exception $e) {
            Log::error('Error en wizard de lógica - configurar', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar la configuración: ' . $e->getMessage());
        }
    }

    /**
     * Guardar lógica configurada
     */
    public function store(Request $request)
    {
        try {
            $encuestaId = Session::get('wizard_encuesta_id');
            $preguntaIndex = Session::get('wizard_pregunta_index', 0);

            if (!$encuestaId) {
                return redirect()->route('logica.wizard.index')
                    ->with('error', 'Sesión de wizard expirada. Selecciona una encuesta nuevamente.');
            }

            DB::beginTransaction();

            $encuesta = Encuesta::with(['preguntas.respuestas'])->findOrFail($encuestaId);

            // Obtener preguntas que permiten lógica
            $preguntas = $encuesta->preguntas()
                ->whereIn('tipo', ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple'])
                ->whereHas('respuestas')
                ->orderBy('orden')
                ->get();

            if ($preguntaIndex >= $preguntas->count()) {
                return redirect()->route('logica.wizard.resumen');
            }

            $preguntaActual = $preguntas[$preguntaIndex];

            // Validar datos
            $request->validate([
                'logicas' => 'required|array',
                'logicas.*.respuesta_id' => 'required|exists:respuestas,id',
                'logicas.*.siguiente_pregunta_id' => 'nullable|exists:preguntas,id',
                'logicas.*.finalizar' => 'nullable|boolean',
            ], [
                'logicas.required' => 'Debe configurar al menos una lógica.',
                'logicas.*.respuesta_id.required' => 'La respuesta es obligatoria.',
                'logicas.*.respuesta_id.exists' => 'La respuesta seleccionada no existe.',
                'logicas.*.siguiente_pregunta_id.exists' => 'La pregunta de destino no existe.',
                'logicas.*.finalizar.boolean' => 'El campo finalizar debe ser verdadero o falso.',
            ]);

            // Eliminar lógica existente para esta pregunta
            Logica::where('pregunta_id', $preguntaActual->id)->delete();

            // Crear nueva lógica
            $logicasCreadas = 0;
            foreach ($request->logicas as $data) {
                if (!empty($data['respuesta_id'])) {
                    // Verificar que la respuesta pertenece a la pregunta
                    $respuesta = Respuesta::where('id', $data['respuesta_id'])
                        ->where('pregunta_id', $preguntaActual->id)
                        ->first();

                    if (!$respuesta) {
                        throw new Exception('La respuesta seleccionada no pertenece a esta pregunta.');
                    }

                    // Verificar que la pregunta de destino pertenece a la encuesta
                    if (!empty($data['siguiente_pregunta_id'])) {
                        $preguntaDestino = $preguntas->find($data['siguiente_pregunta_id']);
                        if (!$preguntaDestino) {
                            throw new Exception('La pregunta de destino no pertenece a esta encuesta.');
                        }
                    }

                    $logica = Logica::create([
                        'pregunta_id' => $preguntaActual->id,
                        'respuesta_id' => $data['respuesta_id'],
                        'siguiente_pregunta_id' => $data['siguiente_pregunta_id'] ?? null,
                        'finalizar' => $data['finalizar'] ?? false,
                    ]);
                    $logicasCreadas++;
                }
            }

            // Incrementar contador de lógica
            $logicaCount = Session::get('wizard_logica_count', 0) + $logicasCreadas;
            Session::put('wizard_logica_count', $logicaCount);

            // Avanzar al siguiente índice
            $siguienteIndex = $preguntaIndex + 1;
            Session::put('wizard_pregunta_index', $siguienteIndex);

            DB::commit();

            Log::info('Lógica guardada en wizard', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId,
                'pregunta_id' => $preguntaActual->id,
                'logicas_creadas' => $logicasCreadas,
                'siguiente_index' => $siguienteIndex
            ]);

            // Verificar si hay más preguntas
            if ($siguienteIndex < $preguntas->count()) {
                return redirect()->route('logica.wizard.configurar')
                    ->with('success', 'Lógica configurada correctamente. Continuando con la siguiente pregunta.');
            } else {
                return redirect()->route('logica.wizard.resumen')
                    ->with('success', 'Lógica configurada correctamente. Revisa el resumen final.');
            }

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error guardando lógica en wizard', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId ?? null,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al guardar la lógica: ' . $e->getMessage());
        }
    }

    /**
     * Paso 3: Resumen de lógica configurada
     */
    public function resumen()
    {
        try {
            $encuestaId = Session::get('wizard_encuesta_id');

            if (!$encuestaId) {
                return redirect()->route('logica.wizard.index')
                    ->with('error', 'Sesión de wizard expirada.');
            }

            $encuesta = Encuesta::with(['preguntas.respuestas.logica.siguientePregunta'])->findOrFail($encuestaId);

            // Obtener todas las lógicas configuradas
            $logicas = Logica::whereIn('pregunta_id', $encuesta->preguntas->pluck('id'))
                ->with(['pregunta', 'respuesta', 'siguientePregunta'])
                ->get();

            // Generar resumen
            $resumenLogica = [];
            foreach ($logicas as $logica) {
                $resumenLogica[] = [
                    'pregunta_origen' => $logica->pregunta->texto,
                    'respuesta' => $logica->respuesta->texto,
                    'accion' => $logica->finalizar ? 'Finalizar encuesta' :
                               ($logica->siguientePregunta ? "Ir a: {$logica->siguientePregunta->texto}" : 'Continuar secuencialmente'),
                    'siguiente_pregunta' => $logica->siguientePregunta ? $logica->siguientePregunta->texto : null,
                    'finalizar' => $logica->finalizar
                ];
            }

            return view('logica.wizard.resumen', compact('encuesta', 'resumenLogica', 'logicas'));
        } catch (Exception $e) {
            Log::error('Error en wizard de lógica - resumen', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar el resumen: ' . $e->getMessage());
        }
    }

    /**
     * Confirmar y finalizar wizard
     */
    public function confirmar()
    {
        try {
            $encuestaId = Session::get('wizard_encuesta_id');

            if (!$encuestaId) {
                return redirect()->route('logica.wizard.index')
                    ->with('error', 'Sesión de wizard expirada.');
            }

            $encuesta = Encuesta::findOrFail($encuestaId);

            // Limpiar sesión del wizard
            Session::forget([
                'wizard_encuesta_id',
                'wizard_pregunta_index',
                'wizard_logica_count'
            ]);

            Log::info('Wizard de lógica finalizado', [
                'user_id' => Auth::id(),
                'encuesta_id' => $encuestaId
            ]);

            // Actualizar el estado de la encuesta a "enviada"
            $encuesta->estado = 'publicada';
            $encuesta->save();

            return redirect()->route('encuestas.index')
                ->with('success', 'Lógica de la encuesta configurada exitosamente.');

        } catch (Exception $e) {
            Log::error('Error confirmando wizard de lógica', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al finalizar el wizard: ' . $e->getMessage());
        }
    }

    /**
     * Cancelar wizard
     */
    public function cancel()
    {
        try {
            // Limpiar sesión del wizard
            Session::forget([
                'wizard_encuesta_id',
                'wizard_pregunta_index',
                'wizard_logica_count'
            ]);

            Log::info('Wizard de lógica cancelado', [
                'user_id' => Auth::id()
            ]);

            return redirect()->route('encuestas.index')
                ->with('info', 'Configuración de lógica cancelada.');

        } catch (Exception $e) {
            Log::error('Error cancelando wizard de lógica', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cancelar el wizard: ' . $e->getMessage());
        }
    }
}
