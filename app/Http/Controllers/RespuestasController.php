<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\AnalisisEncuesta;
use App\Services\IAService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RespuestasController extends Controller
{
    private IAService $iaService;

    public function __construct(IAService $iaService)
    {
        $this->iaService = $iaService;
    }

    /**
     * Vista principal del módulo de respuestas
     */
    public function index()
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['respuestas.view'])) {
                return $this->redirectIfNoAccess('No tienes permisos para acceder al módulo de respuestas.');
            }

            $encuestas = Encuesta::with(['empresa'])
                ->where('estado', 'publicada')
                ->where('habilitada', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return view('respuestas.index', compact('encuestas'));

        } catch (Exception $e) {
            Log::error('Error en módulo de respuestas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar el módulo de respuestas: ' . $e->getMessage());
        }
    }

    /**
     * Generar análisis de respuestas con IA
     */
    public function generarAnalisis(Request $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['respuestas.analyze'])) {
                return $this->redirectIfNoAccess('No tienes permisos para generar análisis de respuestas.');
            }

            $encuestaId = $request->input('encuesta_id');

            if (!$encuestaId) {
                return redirect()->back()->with('error', 'Debe seleccionar una encuesta.');
            }

            $encuesta = Encuesta::with(['preguntas.respuestas', 'preguntas.respuestasUsuario'])
                ->findOrFail($encuestaId);

            // Verificar que la encuesta tenga respuestas
            $totalRespuestas = DB::table('respuestas_usuario')
                ->where('encuesta_id', $encuestaId)
                ->count();

            if ($totalRespuestas === 0) {
                return redirect()->back()->with('error', 'Esta encuesta no tiene respuestas para analizar.');
            }

            DB::beginTransaction();

            try {
                // Preparar datos para la IA
                $datosEncuesta = $this->prepararDatosParaIA($encuesta);

                // Analizar con IA
                $resultados = $this->iaService->analizarRespuestas($datosEncuesta);

                // Guardar resultados en base de datos
                $this->guardarAnalisis($encuestaId, $resultados);

                DB::commit();

                Log::info('✅ Análisis de respuestas generado exitosamente', [
                    'user_id' => auth()->id(),
                    'encuesta_id' => $encuestaId,
                    'total_analisis' => count($resultados)
                ]);

                return redirect()->route('respuestas.ver', $encuestaId)
                    ->with('success', 'Análisis generado exitosamente. Se procesaron ' . count($resultados) . ' preguntas.');

            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (Exception $e) {
            Log::error('Error generando análisis de respuestas', [
                'user_id' => auth()->id(),
                'encuesta_id' => $encuestaId ?? 'N/A',
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error generando análisis: ' . $e->getMessage());
        }
    }

    /**
     * Ver análisis de respuestas
     */
    public function ver($encuestaId)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['respuestas.view'])) {
                return $this->redirectIfNoAccess('No tienes permisos para ver análisis de respuestas.');
            }

            $encuesta = Encuesta::with(['empresa', 'preguntas'])
                ->findOrFail($encuestaId);

            $analisis = AnalisisEncuesta::with(['pregunta'])
                ->where('encuesta_id', $encuestaId)
                ->where('estado', 'completado')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('respuestas.ver', compact('encuesta', 'analisis'));

        } catch (Exception $e) {
            Log::error('Error viendo análisis de respuestas', [
                'user_id' => auth()->id(),
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar el análisis: ' . $e->getMessage());
        }
    }

    /**
     * Preparar datos para la IA
     */
    private function prepararDatosParaIA(Encuesta $encuesta): array
    {
        $datos = [
            'encuesta_id' => $encuesta->id,
            'preguntas' => []
        ];

        foreach ($encuesta->preguntas as $pregunta) {
            $respuestas = $this->obtenerRespuestasPregunta($pregunta->id);

            $datos['preguntas'][] = [
                'id' => $pregunta->id,
                'texto' => $pregunta->texto,
                'tipo' => $pregunta->tipo,
                'respuestas' => $respuestas
            ];
        }

        return $datos;
    }

    /**
     * Obtener respuestas de una pregunta
     */
    private function obtenerRespuestasPregunta(int $preguntaId): array
    {
        $respuestas = DB::table('respuestas_usuario')
            ->where('pregunta_id', $preguntaId)
            ->get();

        $resultado = [];

        foreach ($respuestas as $respuesta) {
            if ($respuesta->respuesta_id) {
                // Respuesta de selección
                $respuestaModel = DB::table('respuestas')
                    ->where('id', $respuesta->respuesta_id)
                    ->first();

                if ($respuestaModel) {
                    $resultado[] = $respuestaModel->texto;
                }
            } else {
                // Respuesta de texto
                $resultado[] = $respuesta->respuesta_texto;
            }
        }

        return $resultado;
    }

    /**
     * Guardar análisis en base de datos
     */
    private function guardarAnalisis(int $encuestaId, array $resultados): void
    {
        foreach ($resultados as $resultado) {
            AnalisisEncuesta::updateOrCreate(
                [
                    'encuesta_id' => $encuestaId,
                    'pregunta_id' => $resultado['pregunta_id']
                ],
                [
                    'tipo_grafico' => $resultado['tipo_grafico'],
                    'analisis_ia' => $resultado['analisis_ia'],
                    'configuracion_grafico' => $resultado['configuracion_grafico'],
                    'datos_procesados' => $resultado['datos_procesados'],
                    'estado' => $resultado['estado'],
                    'fecha_analisis' => now()
                ]
            );
        }
    }

    /**
     * Verificar permisos de usuario
     */
    private function checkUserAccess(array $requiredPermissions = []): bool
    {
        if (empty($requiredPermissions)) {
            return true;
        }

        $user = auth()->user();
        if (!$user) {
            return false;
        }

        // Verificar roles
        $roles = ['Superadmin', 'Admin', 'Cliente'];
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        // Verificar permisos específicos
        foreach ($requiredPermissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirección si no tiene acceso
     */
    private function redirectIfNoAccess(string $message): \Illuminate\Http\RedirectResponse
    {
        Log::warning('Acceso denegado a módulo de respuestas', [
            'user_id' => auth()->id(),
            'message' => $message
        ]);

        return redirect()->route('home')->with('error', $message);
    }
}
