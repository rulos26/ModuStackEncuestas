<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class CargaMasivaEncuestasController extends Controller
{
    /**
     * Mostrar la vista inicial de carga masiva
     */
    public function index()
    {
        $encuestas = Encuesta::orderBy('titulo')->get();
        return view('carga-masiva.index', compact('encuestas'));
    }

    /**
     * Procesar archivo de preguntas
     */
    public function procesarPreguntas(Request $request)
    {
        try {
            $request->validate([
                'encuesta_id' => 'required|exists:encuestas,id',
                'archivo_preguntas' => 'required|file|mimes:txt|max:2048',
                'modo_asignacion' => 'required|in:manual,automatico'
            ]);

            $encuesta = Encuesta::findOrFail($request->encuesta_id);
            $archivo = $request->file('archivo_preguntas');
            $modoAsignacion = $request->modo_asignacion;

            // Leer y procesar el archivo
            $preguntas = $this->leerArchivoPreguntas($archivo);

            if (empty($preguntas)) {
                return back()->withErrors(['archivo_preguntas' => 'El archivo está vacío o no contiene preguntas válidas.']);
            }

            // Guardar preguntas en caché temporal
            $cacheKey = "preguntas_temp_{$encuesta->id}_" . time();
            Cache::put($cacheKey, [
                'encuesta_id' => $encuesta->id,
                'preguntas' => $preguntas,
                'modo_asignacion' => $modoAsignacion,
                'timestamp' => now()
            ], 3600); // 1 hora

            if ($modoAsignacion === 'automatico') {
                // Procesar automáticamente con IA
                $preguntasConTipos = $this->asignarTiposAutomaticamente($preguntas);

                // Guardar en caché para confirmación
                Cache::put($cacheKey, [
                    'encuesta_id' => $encuesta->id,
                    'preguntas' => $preguntasConTipos,
                    'modo_asignacion' => $modoAsignacion,
                    'timestamp' => now()
                ], 3600);

                return redirect()->route('carga-masiva.confirmar-preguntas', ['cache_key' => $cacheKey]);
            } else {
                // Modo manual - mostrar wizard
                return redirect()->route('carga-masiva.wizard-preguntas', ['cache_key' => $cacheKey]);
            }

        } catch (Exception $e) {
            Log::error('Error procesando preguntas', [
                'error' => $e->getMessage(),
                'encuesta_id' => $request->encuesta_id ?? 'N/A'
            ]);

            return back()->withErrors(['error' => 'Error procesando el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar wizard para asignación manual de tipos
     */
    public function wizardPreguntas(Request $request)
    {
        $cacheKey = $request->cache_key;
        $datos = Cache::get($cacheKey);

        if (!$datos) {
            return redirect()->route('carga-masiva.index')
                ->withErrors(['error' => 'Sesión de carga expirada. Por favor, inténtalo de nuevo.']);
        }

        $preguntaIndex = $request->get('pregunta', 0);
        $preguntas = $datos['preguntas'];
        $totalPreguntas = count($preguntas);

        if ($preguntaIndex >= $totalPreguntas) {
            // Finalizar wizard
            return redirect()->route('carga-masiva.confirmar-preguntas', ['cache_key' => $cacheKey]);
        }

        $preguntaActual = $preguntas[$preguntaIndex];
        $tiposDisponibles = $this->obtenerTiposDisponibles();
        $controller = $this;

        return view('carga-masiva.wizard-preguntas', compact(
            'cacheKey',
            'preguntaActual',
            'preguntaIndex',
            'totalPreguntas',
            'tiposDisponibles',
            'controller'
        ));
    }

    /**
     * Guardar tipo de pregunta en wizard
     */
    public function guardarTipoPregunta(Request $request)
    {
        $request->validate([
            'cache_key' => 'required',
            'pregunta_index' => 'required|integer|min:0',
            'tipo_pregunta' => 'required|string'
        ]);

        $cacheKey = $request->cache_key;
        $datos = Cache::get($cacheKey);

        if (!$datos) {
            return redirect()->route('carga-masiva.index')
                ->withErrors(['error' => 'Sesión de carga expirada.']);
        }

        // Actualizar tipo de pregunta
        $preguntaIndex = $request->pregunta_index;
        $datos['preguntas'][$preguntaIndex]['tipo'] = $request->tipo_pregunta;

        Cache::put($cacheKey, $datos, 3600);

        // Redirigir a siguiente pregunta o confirmación
        $siguienteIndex = $preguntaIndex + 1;
        if ($siguienteIndex >= count($datos['preguntas'])) {
            return redirect()->route('carga-masiva.confirmar-preguntas', ['cache_key' => $cacheKey]);
        }

        return redirect()->route('carga-masiva.wizard-preguntas', [
            'cache_key' => $cacheKey,
            'pregunta' => $siguienteIndex
        ]);
    }

    /**
     * Mostrar confirmación de preguntas
     */
    public function confirmarPreguntas(Request $request)
    {
        $cacheKey = $request->cache_key;
        $datos = Cache::get($cacheKey);

        if (!$datos) {
            return redirect()->route('carga-masiva.index')
                ->withErrors(['error' => 'Sesión de carga expirada.']);
        }

        $encuesta = Encuesta::findOrFail($datos['encuesta_id']);
        $preguntas = $datos['preguntas'];
        $controller = $this;

        // Detectar preguntas problemáticas (sin tipo asignado)
        $preguntasProblematicas = [];
        foreach ($preguntas as $index => $pregunta) {
            if (empty($pregunta['tipo']) || $pregunta['tipo'] === null) {
                $preguntasProblematicas[] = $index;
            }
        }

        // Si hay preguntas problemáticas, redirigir al wizard
        if (!empty($preguntasProblematicas)) {
            return redirect()->route('carga-masiva.wizard-preguntas', [
                'cache_key' => $cacheKey,
                'pregunta' => $preguntasProblematicas[0],
                'correccion_automatica' => true
            ])->with('warning', 'Se detectaron preguntas sin tipo asignado. Por favor, asigna los tipos manualmente.');
        }

        return view('carga-masiva.confirmar-preguntas', compact('cacheKey', 'encuesta', 'preguntas', 'controller'));
    }

    /**
     * Guardar preguntas en la base de datos
     */
    public function guardarPreguntas(Request $request)
    {
        $request->validate([
            'cache_key' => 'required'
        ]);

        $cacheKey = $request->cache_key;
        $datos = Cache::get($cacheKey);

        if (!$datos) {
            return redirect()->route('carga-masiva.index')
                ->withErrors(['error' => 'Sesión de carga expirada.']);
        }

        try {
            $encuesta = Encuesta::findOrFail($datos['encuesta_id']);
            $preguntasGuardadas = 0;
            $errores = [];

            foreach ($datos['preguntas'] as $index => $preguntaData) {
                try {
                    Pregunta::create([
                        'encuesta_id' => $encuesta->id,
                        'texto' => $preguntaData['texto'],
                        'tipo' => $preguntaData['tipo'] ?? 'texto_corto',
                        'orden' => $index + 1,
                        'requerida' => true,
                        'estado' => 'activa'
                    ]);
                    $preguntasGuardadas++;
                } catch (Exception $e) {
                    $errores[] = "Pregunta " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            // Limpiar caché
            Cache::forget($cacheKey);

            return redirect()->route('carga-masiva.cargar-respuestas', [
                'encuesta_id' => $encuesta->id,
                'preguntas_guardadas' => $preguntasGuardadas,
                'errores' => $errores
            ]);

        } catch (Exception $e) {
            Log::error('Error guardando preguntas', [
                'error' => $e->getMessage(),
                'encuesta_id' => $datos['encuesta_id']
            ]);

            return back()->withErrors(['error' => 'Error guardando preguntas: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar formulario para cargar respuestas
     */
    public function cargarRespuestas(Request $request)
    {
        $encuesta = Encuesta::findOrFail($request->encuesta_id);
        $preguntas = $encuesta->preguntas()->orderBy('orden')->get();
        $controller = $this;

        return view('carga-masiva.cargar-respuestas', compact('encuesta', 'preguntas', 'controller'));
    }

    /**
     * Procesar archivo de respuestas
     */
    public function procesarRespuestas(Request $request)
    {
        try {
            $request->validate([
                'encuesta_id' => 'required|exists:encuestas,id',
                'archivo_respuestas' => 'required|file|mimes:txt|max:2048'
            ]);

            $encuesta = Encuesta::findOrFail($request->encuesta_id);
            $archivo = $request->file('archivo_respuestas');

            // Leer y procesar respuestas
            $respuestas = $this->leerArchivoRespuestas($archivo);
            $preguntas = $encuesta->preguntas()->orderBy('orden')->get();

            // Detectar incompatibilidades antes de procesar
            $incompatibilidades = $this->detectarIncompatibilidades($respuestas, $preguntas);

            if (!empty($incompatibilidades)) {
                // Crear caché temporal para corrección
                $cacheKey = "correccion_temp_{$encuesta->id}_" . time();
                Cache::put($cacheKey, [
                    'encuesta_id' => $encuesta->id,
                    'preguntas' => $preguntas->toArray(),
                    'respuestas' => $respuestas,
                    'incompatibilidades' => $incompatibilidades,
                    'timestamp' => now()
                ], 3600);

                return redirect()->route('carga-masiva.corregir-incompatibilidades', [
                    'cache_key' => $cacheKey
                ])->with('warning', 'Se detectaron incompatibilidades entre preguntas y respuestas. Por favor, ajusta los tipos de pregunta.');
            }

            // Validar y asociar respuestas
            $resultado = $this->procesarRespuestasConPreguntas($respuestas, $preguntas);

            return view('carga-masiva.resumen-final', compact('encuesta', 'resultado'));

        } catch (Exception $e) {
            Log::error('Error procesando respuestas', [
                'error' => $e->getMessage(),
                'encuesta_id' => $request->encuesta_id ?? 'N/A'
            ]);

            return back()->withErrors(['error' => 'Error procesando respuestas: ' . $e->getMessage()]);
        }
    }

    /**
     * Leer archivo de preguntas
     */
    private function leerArchivoPreguntas($archivo)
    {
        $contenido = file_get_contents($archivo->getRealPath());
        $lineas = explode("\n", trim($contenido));

        $preguntas = [];
        foreach ($lineas as $index => $linea) {
            $linea = trim($linea);
            if (!empty($linea)) {
                $preguntas[] = [
                    'texto' => $linea,
                    'tipo' => null, // Se asignará después
                    'orden' => $index + 1
                ];
            }
        }

        return $preguntas;
    }

    /**
     * Leer archivo de respuestas
     */
    private function leerArchivoRespuestas($archivo)
    {
        $contenido = file_get_contents($archivo->getRealPath());
        $lineas = explode("\n", trim($contenido));

        $respuestas = [];
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if (!empty($linea)) {
                // Buscar patrón R_X: contenido
                if (preg_match('/^R_(\d+):\s*(.+)$/', $linea, $matches)) {
                    $numeroPregunta = (int)$matches[1];
                    $contenido = trim($matches[2]);

                    $respuestas[] = [
                        'numero_pregunta' => $numeroPregunta,
                        'contenido' => $contenido
                    ];
                }
            }
        }

        return $respuestas;
    }

    /**
     * Asignar tipos automáticamente usando IA
     */
    private function asignarTiposAutomaticamente($preguntas)
    {
        foreach ($preguntas as &$pregunta) {
            $pregunta['tipo'] = $this->predecirTipoPregunta($pregunta['texto']);
        }

        return $preguntas;
    }

    /**
     * Predecir tipo de pregunta usando IA
     */
    private function predecirTipoPregunta($texto)
    {
        // Palabras clave para diferentes tipos de preguntas
        $palabrasClave = [
            'texto_corto' => ['nombre', 'email', 'teléfono', 'dirección', 'edad', 'fecha'],
            'parrafo' => ['describe', 'explica', 'comenta', 'opinión', 'sugerencias', 'observaciones'],
            'seleccion_unica' => ['selecciona', 'elige', 'marca', 'cuál', 'qué opción'],
            'casilla' => ['marca', 'selecciona todas', 'múltiples', 'varias opciones'],
            'lista_desplegable' => ['selecciona de la lista', 'elige de', 'opciones disponibles'],
            'escala' => ['escala', 'del 1 al', 'nivel', 'grado', 'puntuación'],
            'cuadricula' => ['tabla', 'matriz', 'cuadrícula', 'evaluar']
        ];

        $textoLower = strtolower($texto);

        foreach ($palabrasClave as $tipo => $claves) {
            foreach ($claves as $clave) {
                if (strpos($textoLower, $clave) !== false) {
                    return $tipo;
                }
            }
        }

        // Análisis más avanzado
        if (preg_match('/\?$/', $texto)) {
            // Pregunta directa
            if (preg_match('/(sí|no|si|no)\?/i', $texto)) {
                return 'seleccion_unica';
            }
            return 'texto_corto';
        }

        // Por defecto
        return 'texto_corto';
    }

    /**
     * Obtener tipos de preguntas disponibles
     */
    public function obtenerTiposDisponibles()
    {
        return [
            'texto_corto' => 'Texto corto',
            'parrafo' => 'Párrafo',
            'seleccion_unica' => 'Selección única',
            'casilla' => 'Casilla de verificación',
            'lista_desplegable' => 'Lista desplegable',
            'escala' => 'Escala',
            'cuadricula' => 'Cuadrícula'
        ];
    }

    /**
     * Procesar respuestas con preguntas
     */
    private function procesarRespuestasConPreguntas($respuestas, $preguntas)
    {
        $resultado = [
            'guardadas' => 0,
            'errores' => [],
            'sin_pregunta' => []
        ];

        foreach ($respuestas as $respuesta) {
            $numeroPregunta = $respuesta['numero_pregunta'];
            $pregunta = $preguntas->where('orden', $numeroPregunta)->first();

            if (!$pregunta) {
                $resultado['sin_pregunta'][] = "R_{$numeroPregunta}: No existe la pregunta correspondiente";
                continue;
            }

            try {
                // Determinar tipo de respuesta
                $tipoRespuesta = $this->determinarTipoRespuesta($respuesta['contenido']);

                // Validar compatibilidad
                if (!$this->validarCompatibilidad($pregunta->tipo, $tipoRespuesta)) {
                    $resultado['errores'][] = "R_{$numeroPregunta}: Tipo incompatible con pregunta";
                    continue;
                }

                // Guardar respuesta
                Respuesta::create([
                    'pregunta_id' => $pregunta->id,
                    'texto' => $respuesta['contenido'],
                    'tipo' => $tipoRespuesta,
                    'orden' => $resultado['guardadas'] + 1,
                    'es_correcta' => false,
                    'estado' => 'activa'
                ]);

                $resultado['guardadas']++;

            } catch (Exception $e) {
                $resultado['errores'][] = "R_{$numeroPregunta}: " . $e->getMessage();
            }
        }

        return $resultado;
    }

    /**
     * Determinar tipo de respuesta
     */
    private function determinarTipoRespuesta($contenido)
    {
        $contenido = trim($contenido);

        // Si contiene comas o saltos de línea, es múltiple
        if (strpos($contenido, ',') !== false || strpos($contenido, "\n") !== false) {
            return 'opciones_multiples';
        }

        // Si es muy corto, probablemente es texto
        if (strlen($contenido) <= 100) {
            return 'texto';
        }

        // Por defecto
        return 'texto';
    }

    /**
     * Detectar incompatibilidades entre preguntas y respuestas
     */
    private function detectarIncompatibilidades($respuestas, $preguntas)
    {
        $incompatibilidades = [];

        foreach ($respuestas as $respuesta) {
            $numeroPregunta = $respuesta['numero_pregunta'];
            $pregunta = $preguntas->where('orden', $numeroPregunta)->first();

            if ($pregunta) {
                $tipoRespuesta = $this->determinarTipoRespuesta($respuesta['contenido']);
                $esCompatible = $this->validarCompatibilidad($pregunta->tipo, $tipoRespuesta);

                if (!$esCompatible) {
                    $incompatibilidades[] = [
                        'numero_pregunta' => $numeroPregunta,
                        'pregunta_texto' => $pregunta->texto,
                        'pregunta_tipo' => $pregunta->tipo,
                        'respuesta_contenido' => $respuesta['contenido'],
                        'respuesta_tipo' => $tipoRespuesta,
                        'sugerencia_tipo' => $this->sugerirTipoPregunta($respuesta['contenido'])
                    ];
                }
            }
        }

        return $incompatibilidades;
    }

    /**
     * Sugerir tipo de pregunta basado en el contenido de la respuesta
     */
    private function sugerirTipoPregunta($contenido)
    {
        $contenido = trim($contenido);

        // Si contiene comas, probablemente es múltiple
        if (strpos($contenido, ',') !== false) {
            return 'casilla';
        }

        // Si es numérico, podría ser escala
        if (is_numeric($contenido)) {
            return 'escala';
        }

        // Si es muy corto, texto corto
        if (strlen($contenido) <= 50) {
            return 'texto_corto';
        }

        // Por defecto, texto corto
        return 'texto_corto';
    }

    /**
     * Validar compatibilidad entre tipo de pregunta y respuesta
     */
    private function validarCompatibilidad($tipoPregunta, $tipoRespuesta)
    {
        $compatibilidades = [
            'texto_corto' => ['texto'],
            'parrafo' => ['texto'],
            'seleccion_unica' => ['opciones_multiples'],
            'casilla' => ['opciones_multiples'],
            'lista_desplegable' => ['opciones_multiples'],
            'escala' => ['opciones_multiples'],
            'cuadricula' => ['opciones_multiples']
        ];

        return in_array($tipoRespuesta, $compatibilidades[$tipoPregunta] ?? []);
    }

    /**
     * Mostrar vista para corregir incompatibilidades
     */
    public function corregirIncompatibilidades(Request $request)
    {
        $cacheKey = $request->cache_key;
        $datos = Cache::get($cacheKey);

        if (!$datos) {
            return redirect()->route('carga-masiva.index')
                ->withErrors(['error' => 'Sesión de corrección expirada.']);
        }

        $encuesta = Encuesta::findOrFail($datos['encuesta_id']);
        $incompatibilidades = $datos['incompatibilidades'];
        $controller = $this;

        return view('carga-masiva.corregir-incompatibilidades', compact('cacheKey', 'encuesta', 'incompatibilidades', 'controller'));
    }

    /**
     * Guardar corrección de incompatibilidad
     */
    public function guardarCorreccionIncompatibilidad(Request $request)
    {
        $request->validate([
            'cache_key' => 'required',
            'numero_pregunta' => 'required|integer',
            'nuevo_tipo' => 'required|string'
        ]);

        $cacheKey = $request->cache_key;
        $datos = Cache::get($cacheKey);

        if (!$datos) {
            return redirect()->route('carga-masiva.index')
                ->withErrors(['error' => 'Sesión de corrección expirada.']);
        }

        // Actualizar tipo de pregunta en la base de datos
        $numeroPregunta = $request->numero_pregunta;
        $nuevoTipo = $request->nuevo_tipo;

        $pregunta = Pregunta::where('encuesta_id', $datos['encuesta_id'])
            ->where('orden', $numeroPregunta)
            ->first();

        if ($pregunta) {
            $pregunta->update(['tipo' => $nuevoTipo]);
        }

        // Remover la incompatibilidad corregida
        $datos['incompatibilidades'] = array_filter($datos['incompatibilidades'], function($inc) use ($numeroPregunta) {
            return $inc['numero_pregunta'] != $numeroPregunta;
        });

        Cache::put($cacheKey, $datos, 3600);

        // Si no hay más incompatibilidades, procesar respuestas
        if (empty($datos['incompatibilidades'])) {
            $encuesta = Encuesta::findOrFail($datos['encuesta_id']);
            $preguntas = $encuesta->preguntas()->orderBy('orden')->get();
            $resultado = $this->procesarRespuestasConPreguntas($datos['respuestas'], $preguntas);

            return view('carga-masiva.resumen-final', compact('encuesta', 'resultado'))
                ->with('success', 'Todas las incompatibilidades han sido corregidas y las respuestas han sido procesadas.');
        }

        return redirect()->route('carga-masiva.corregir-incompatibilidades', ['cache_key' => $cacheKey])
            ->with('success', 'Tipo de pregunta corregido exitosamente.');
    }

    /**
     * Obtener nombre del tipo de pregunta
     */
    public function getTypeName($tipo)
    {
        $tipos = [
            'texto_corto' => 'Texto corto',
            'parrafo' => 'Párrafo',
            'seleccion_unica' => 'Selección única',
            'casilla' => 'Casilla de verificación',
            'lista_desplegable' => 'Lista desplegable',
            'escala' => 'Escala',
            'cuadricula' => 'Cuadrícula'
        ];

        return $tipos[$tipo] ?? 'Desconocido';
    }

    /**
     * Obtener color para el badge del tipo
     */
    public function getBadgeColorForType($tipo)
    {
        $colores = [
            'texto_corto' => 'primary',
            'parrafo' => 'info',
            'seleccion_unica' => 'success',
            'casilla' => 'warning',
            'lista_desplegable' => 'secondary',
            'escala' => 'danger',
            'cuadricula' => 'dark'
        ];

        return $colores[$tipo] ?? 'secondary';
    }

    /**
     * Obtener color para el icono del tipo
     */
    public function getColorForType($tipo)
    {
        $colores = [
            'texto_corto' => 'primary',
            'parrafo' => 'info',
            'seleccion_unica' => 'success',
            'casilla' => 'warning',
            'lista_desplegable' => 'secondary',
            'escala' => 'danger',
            'cuadricula' => 'dark'
        ];

        return $colores[$tipo] ?? 'secondary';
    }

    /**
     * Obtener icono para el tipo
     */
    public function getIconForType($tipo)
    {
        $iconos = [
            'texto_corto' => 'font',
            'parrafo' => 'paragraph',
            'seleccion_unica' => 'dot-circle',
            'casilla' => 'check-square',
            'lista_desplegable' => 'list',
            'escala' => 'star',
            'cuadricula' => 'table'
        ];

        return $iconos[$tipo] ?? 'question';
    }

    /**
     * Obtener descripción del tipo
     */
    public function getDescriptionForType($tipo)
    {
        $descripciones = [
            'texto_corto' => 'Ideal para nombres, emails, teléfonos y respuestas cortas.',
            'parrafo' => 'Perfecto para comentarios, opiniones y respuestas largas.',
            'seleccion_unica' => 'El usuario debe elegir una sola opción.',
            'casilla' => 'El usuario puede seleccionar múltiples opciones.',
            'lista_desplegable' => 'Muestra opciones en un menú desplegable.',
            'escala' => 'Permite calificar en una escala numérica.',
            'cuadricula' => 'Matriz de opciones para evaluar múltiples criterios.'
        ];

        return $descripciones[$tipo] ?? 'Descripción no disponible.';
    }
}
