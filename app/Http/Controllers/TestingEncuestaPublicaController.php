<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Exception;

class TestingEncuestaPublicaController extends Controller
{
    /**
     * Mostrar la vista de pruebas
     */
    public function index()
    {
        return view('testing.encuesta-publica');
    }

    /**
     * Ejecutar prueba de encuesta pública
     */
    public function ejecutarPrueba(Request $request)
    {
        $inicio = microtime(true);

        try {
            $encuestaId = $request->input('encuesta_id', 13);
            $slug = $request->input('slug_encuesta', 'encuesta-de-prueba-tester-automatico-2025-07-30-194743');
            $tipoPrueba = $request->input('tipo_prueba', 'mostrar');
            $userAgent = $request->input('user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            $ipAddress = $request->input('ip_address', '127.0.0.1');

            Log::info('🧪 TESTING - Iniciando prueba de encuesta pública', [
                'encuesta_id' => $encuestaId,
                'slug' => $slug,
                'tipo_prueba' => $tipoPrueba,
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress
            ]);

            // Simular request
            $request->headers->set('User-Agent', $userAgent);
            $request->server->set('REMOTE_ADDR', $ipAddress);

            $resultado = [
                'encuesta_id' => $encuestaId,
                'slug' => $slug,
                'tipo_prueba' => $tipoPrueba,
                'estado' => 'completado',
                'url' => url("/publica/{$slug}"),
                'tiempo' => round((microtime(true) - $inicio) * 1000, 2),
                'detalles' => '',
                'logs_count' => 0
            ];

            switch ($tipoPrueba) {
                case 'mostrar':
                    $resultado = $this->probarMostrar($encuestaId, $slug, $resultado);
                    break;

                case 'responder':
                    $resultado = $this->probarResponder($encuestaId, $slug, $resultado);
                    break;

                case 'fin':
                    $resultado = $this->probarFin($encuestaId, $slug, $resultado);
                    break;

                case 'debug':
                    $resultado = $this->probarDebug($encuestaId, $slug, $resultado);
                    break;

                case 'vista_publica':
                    $resultado = $this->probarVistaPublica($encuestaId, $slug, $resultado);
                    break;

                default:
                    throw new Exception("Tipo de prueba no válido: {$tipoPrueba}");
            }

            // Contar logs recientes
            $resultado['logs_count'] = $this->contarLogsRecientes();

            Log::info('✅ TESTING - Prueba completada exitosamente', [
                'resultado' => $resultado
            ]);

            return response()->json($resultado);

        } catch (Exception $e) {
            $resultado = [
                'encuesta_id' => $encuestaId ?? 'N/A',
                'slug' => $slug ?? 'N/A',
                'tipo_prueba' => $tipoPrueba ?? 'N/A',
                'estado' => 'error',
                'url' => url("/publica/{$slug}"),
                'tiempo' => round((microtime(true) - $inicio) * 1000, 2),
                'detalles' => "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString(),
                'logs_count' => $this->contarLogsRecientes()
            ];

            Log::error('❌ TESTING - Error en prueba de encuesta pública', [
                'error' => $e->getMessage(),
                'resultado' => $resultado
            ]);

            return response()->json($resultado);
        }
    }

    /**
     * Probar método mostrar
     */
    private function probarMostrar($encuestaId, $slug, $resultado)
    {
        Log::info('🔍 TESTING - Probando método mostrar', ['slug' => $slug]);

        // Verificar si la encuesta existe
        $encuesta = Encuesta::find($encuestaId);
        if (!$encuesta) {
            throw new Exception("Encuesta con ID {$encuestaId} no encontrada");
        }

        // Verificar si el slug coincide
        if ($encuesta->slug !== $slug) {
            throw new Exception("Slug no coincide: esperado '{$encuesta->slug}', recibido '{$slug}'");
        }

        // Verificar estado de la encuesta
        $estados = [
            'habilitada' => $encuesta->habilitada,
            'estado' => $encuesta->estado,
            'fecha_inicio' => $encuesta->fecha_inicio,
            'fecha_fin' => $encuesta->fecha_fin,
            'preguntas_count' => $encuesta->preguntas()->count(),
            'empresa_id' => $encuesta->empresa_id
        ];

        $resultado['detalles'] = "✅ Encuesta encontrada:\n" .
                                "   - ID: {$encuesta->id}\n" .
                                "   - Título: {$encuesta->titulo}\n" .
                                "   - Slug: {$encuesta->slug}\n" .
                                "   - Estado: {$encuesta->estado}\n" .
                                "   - Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No') . "\n" .
                                "   - Preguntas: {$estados['preguntas_count']}\n" .
                                "   - Empresa ID: {$estados['empresa_id']}\n" .
                                "   - Fecha inicio: {$encuesta->fecha_inicio}\n" .
                                "   - Fecha fin: {$encuesta->fecha_fin}\n\n" .
                                "🔍 Estados de la encuesta:\n" .
                                json_encode($estados, JSON_PRETTY_PRINT);

        dd($resultado);
        return $resultado;
    }

    /**
     * Probar método responder
     */
    private function probarResponder($encuestaId, $slug, $resultado)
    {
        Log::info('📝 TESTING - Probando método responder', ['encuesta_id' => $encuestaId]);

        $encuesta = Encuesta::find($encuestaId);
        if (!$encuesta) {
            throw new Exception("Encuesta con ID {$encuestaId} no encontrada");
        }

        // Simular datos de respuesta
        $respuestasSimuladas = [];
        foreach ($encuesta->preguntas as $pregunta) {
            switch ($pregunta->tipo) {
                case 'respuesta_corta':
                    $respuestasSimuladas[$pregunta->id] = "Respuesta de prueba para pregunta {$pregunta->id}";
                    break;
                case 'seleccion_unica':
                    if ($pregunta->respuestas->count() > 0) {
                        $respuestasSimuladas[$pregunta->id] = $pregunta->respuestas->first()->id;
                    }
                    break;
                case 'escala_lineal':
                    $respuestasSimuladas[$pregunta->id] = rand(1, 5);
                    break;
            }
        }

        $resultado['detalles'] = "📝 Simulando respuestas:\n" .
                                "   - Encuesta ID: {$encuesta->id}\n" .
                                "   - Preguntas: " . count($respuestasSimuladas) . "\n" .
                                "   - Respuestas simuladas:\n" .
                                json_encode($respuestasSimuladas, JSON_PRETTY_PRINT);

        return $resultado;
    }

    /**
     * Probar método fin
     */
    private function probarFin($encuestaId, $slug, $resultado)
    {
        Log::info('🏁 TESTING - Probando método fin', ['slug' => $slug]);

        $encuesta = Encuesta::where('slug', $slug)->first();
        if (!$encuesta) {
            throw new Exception("Encuesta con slug '{$slug}' no encontrada");
        }

        $resultado['detalles'] = "🏁 Página de fin:\n" .
                                "   - Encuesta ID: {$encuesta->id}\n" .
                                "   - Título: {$encuesta->titulo}\n" .
                                "   - Slug: {$encuesta->slug}\n" .
                                "   - URL: " . url("/publica/{$slug}/fin");

        return $resultado;
    }

    /**
     * Probar debug completo
     */
    private function probarDebug($encuestaId, $slug, $resultado)
    {
        Log::info('🐛 TESTING - Debug completo', ['encuesta_id' => $encuestaId, 'slug' => $slug]);

        $debug = [];

        // 1. Verificar encuesta en BD
        $encuesta = Encuesta::find($encuestaId);
        if ($encuesta) {
            $debug['encuesta_bd'] = [
                'id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'slug' => $encuesta->slug,
                'estado' => $encuesta->estado,
                'habilitada' => $encuesta->habilitada,
                'empresa_id' => $encuesta->empresa_id,
                'preguntas_count' => $encuesta->preguntas()->count(),
                'fecha_inicio' => $encuesta->fecha_inicio,
                'fecha_fin' => $encuesta->fecha_fin
            ];
        } else {
            $debug['encuesta_bd'] = 'No encontrada';
        }

        // 2. Verificar por slug
        $encuestaPorSlug = Encuesta::where('slug', $slug)->first();
        if ($encuestaPorSlug) {
            $debug['encuesta_slug'] = [
                'id' => $encuestaPorSlug->id,
                'titulo' => $encuestaPorSlug->titulo,
                'slug' => $encuestaPorSlug->slug
            ];
        } else {
            $debug['encuesta_slug'] = 'No encontrada';
        }

        // 3. Verificar empresa
        if ($encuesta && $encuesta->empresa_id) {
            $empresa = DB::table('empresas_clientes')->where('id', $encuesta->empresa_id)->first();
            $debug['empresa'] = $empresa ? (array) $empresa : 'No encontrada';
        } else {
            $debug['empresa'] = 'Sin empresa asignada';
        }

        // 4. Verificar preguntas
        if ($encuesta) {
            $preguntas = $encuesta->preguntas()->with('respuestas')->get();
            $debug['preguntas'] = $preguntas->map(function($pregunta) {
                return [
                    'id' => $pregunta->id,
                    'texto' => $pregunta->texto,
                    'tipo' => $pregunta->tipo,
                    'obligatoria' => $pregunta->obligatoria,
                    'respuestas_count' => $pregunta->respuestas->count()
                ];
            })->toArray();
        } else {
            $debug['preguntas'] = 'Sin encuesta';
        }

        // 5. Verificar logs recientes
        $debug['logs_recientes'] = $this->obtenerLogsRecientes();

        $resultado['detalles'] = "🐛 DEBUG COMPLETO:\n" .
                                json_encode($debug, JSON_PRETTY_PRINT);

        return $resultado;
    }

    /**
     * Probar vista pública
     */
    private function probarVistaPublica($encuestaId, $slug, $resultado)
    {
        Log::info('👀 TESTING - Probando vista pública', ['encuesta_id' => $encuestaId, 'slug' => $slug]);

        $encuesta = Encuesta::find($encuestaId);
        if (!$encuesta) {
            throw new Exception("Encuesta con ID {$encuestaId} no encontrada");
        }

        // Generar URL para la vista pública
        $urlVista = route('testing.encuesta-publica-vista', ['encuesta_id' => $encuestaId]);

        $resultado['url_vista'] = $urlVista;
        $resultado['detalles'] = "👀 Vista pública preparada:\n" .
                                "   - Encuesta ID: {$encuesta->id}\n" .
                                "   - Título: {$encuesta->titulo}\n" .
                                "   - Slug: {$encuesta->slug}\n" .
                                "   - URL Vista: {$urlVista}\n" .
                                "   - Preguntas: " . $encuesta->preguntas()->count() . "\n" .
                                "   - Estado: {$encuesta->estado}\n" .
                                "   - Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No') . "\n\n" .
                                "🔍 La vista se abrirá en una nueva ventana sin token ni autenticación.";

        return $resultado;
    }

    /**
     * Mostrar vista pública de encuesta
     */
    public function mostrarVistaPublica($encuestaId)
    {
        try {
            Log::info('👀 TESTING - Mostrando vista pública', ['encuesta_id' => $encuestaId]);

            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])
                ->where('id', $encuestaId)
                ->first();

            if (!$encuesta) {
                return view('encuestas.publica', [
                    'encuesta' => null,
                    'error' => 'Encuesta no encontrada.'
                ]);
            }

            // Simular que la encuesta está disponible para la vista
            $encuesta->habilitada = true;
            $encuesta->estado = 'publicada';

            Log::info('✅ TESTING - Vista pública renderizada', [
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'preguntas_count' => $encuesta->preguntas->count()
            ]);

            return view('encuestas.publica', compact('encuesta'));

        } catch (Exception $e) {
            Log::error('❌ TESTING - Error mostrando vista pública', [
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);

            return view('encuestas.publica', [
                'encuesta' => null,
                'error' => 'Error al cargar la encuesta: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Obtener logs recientes
     */
    private function obtenerLogsRecientes()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $logs = file($logFile);
                $logsRecientes = array_slice($logs, -50); // Últimas 50 líneas
                return implode('', $logsRecientes);
            }
            return 'Archivo de log no encontrado';
        } catch (Exception $e) {
            return 'Error leyendo logs: ' . $e->getMessage();
        }
    }

    /**
     * Contar logs recientes
     */
    private function contarLogsRecientes()
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $logs = file($logFile);
                return count(array_filter($logs, function($line) {
                    return strpos($line, 'ENCUESTA PÚBLICA') !== false;
                }));
            }
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Obtener logs de encuesta pública
     */
    public function obtenerLogs()
    {
        try {
            $logs = $this->obtenerLogsRecientes();
            return response()->json(['logs' => $logs]);
        } catch (Exception $e) {
            return response()->json(['logs' => 'Error: ' . $e->getMessage()]);
        }
    }
}
