<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class IAService
{
    private string $apiUrl = 'https://api-inference.huggingface.co/models/';
    private string $model = 'microsoft/DialoGPT-medium'; // Modelo gratuito para análisis de texto
    private ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');
    }

    /**
     * Analizar respuestas de encuesta y sugerir visualizaciones
     */
    public function analizarRespuestas(array $datosEncuesta): array
    {
        try {
            Log::info('🤖 Iniciando análisis con IA', [
                'encuesta_id' => $datosEncuesta['encuesta_id'] ?? 'N/A',
                'total_preguntas' => count($datosEncuesta['preguntas'] ?? [])
            ]);

            $resultados = [];

            foreach ($datosEncuesta['preguntas'] as $pregunta) {
                $analisis = $this->analizarPregunta($pregunta);
                $resultados[] = $analisis;
            }

            Log::info('✅ Análisis con IA completado', [
                'total_analisis' => count($resultados)
            ]);

            return $resultados;

        } catch (Exception $e) {
            Log::error('❌ Error en análisis con IA', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Analizar una pregunta específica
     */
    private function analizarPregunta(array $pregunta): array
    {
        $prompt = $this->generarPrompt($pregunta);

        try {
            $respuesta = $this->consultarIA($prompt);
            return $this->procesarRespuestaIA($respuesta, $pregunta);
        } catch (Exception $e) {
            Log::warning('⚠️ Error consultando IA, usando análisis local', [
                'pregunta_id' => $pregunta['id'],
                'error' => $e->getMessage()
            ]);

            return $this->analisisLocal($pregunta);
        }
    }

    /**
     * Generar prompt para la IA
     */
    private function generarPrompt(array $pregunta): string
    {
        $tipo = $pregunta['tipo'];
        $texto = $pregunta['texto'];
        $respuestas = $pregunta['respuestas'] ?? [];

        $prompt = "Analiza esta pregunta de encuesta y sugiere la mejor visualización:\n\n";
        $prompt .= "Pregunta: {$texto}\n";
        $prompt .= "Tipo: {$tipo}\n";
        $prompt .= "Respuestas: " . json_encode($respuestas) . "\n\n";
        $prompt .= "Responde en formato JSON con:\n";
        $prompt .= "- tipo_grafico: (barras, pastel, lineas, dispersion, area, radar, histograma, boxplot)\n";
        $prompt .= "- analisis: (breve análisis textual)\n";
        $prompt .= "- configuracion: (configuración específica del gráfico)\n";

        return $prompt;
    }

    /**
     * Consultar API de IA
     */
    private function consultarIA(string $prompt): string
    {
        if (!$this->apiKey) {
            throw new Exception('API key no configurada');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . $this->model, [
            'inputs' => $prompt,
            'parameters' => [
                'max_length' => 500,
                'temperature' => 0.7
            ]
        ]);

        if (!$response->successful()) {
            throw new Exception('Error en API de IA: ' . $response->body());
        }

        $data = $response->json();
        return $data[0]['generated_text'] ?? '';
    }

    /**
     * Procesar respuesta de la IA
     */
    private function procesarRespuestaIA(string $respuesta, array $pregunta): array
    {
        try {
            // Intentar extraer JSON de la respuesta
            $jsonStart = strpos($respuesta, '{');
            $jsonEnd = strrpos($respuesta, '}');

            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($respuesta, $jsonStart, $jsonEnd - $jsonStart + 1);
                $data = json_decode($jsonString, true);

                if ($data) {
                    return [
                        'pregunta_id' => $pregunta['id'],
                        'tipo_grafico' => $data['tipo_grafico'] ?? 'barras',
                        'analisis_ia' => $data['analisis'] ?? 'Análisis generado por IA',
                        'configuracion_grafico' => $data['configuracion'] ?? [],
                        'datos_procesados' => $this->procesarDatos($pregunta),
                        'estado' => 'completado'
                    ];
                }
            }

            // Si no se puede parsear JSON, usar análisis local
            return $this->analisisLocal($pregunta);

        } catch (Exception $e) {
            Log::warning('⚠️ Error procesando respuesta de IA', [
                'error' => $e->getMessage(),
                'respuesta' => $respuesta
            ]);

            return $this->analisisLocal($pregunta);
        }
    }

    /**
     * Análisis local cuando la IA no está disponible
     */
    private function analisisLocal(array $pregunta): array
    {
        $tipo = $pregunta['tipo'];
        $respuestas = $pregunta['respuestas'] ?? [];

        // Lógica local para determinar tipo de gráfico
        $tipoGrafico = $this->determinarTipoGrafico($tipo, $respuestas);
        $analisis = $this->generarAnalisisLocal($tipo, $respuestas);

        return [
            'pregunta_id' => $pregunta['id'],
            'tipo_grafico' => $tipoGrafico,
            'analisis_ia' => $analisis,
            'configuracion_grafico' => $this->generarConfiguracionLocal($tipoGrafico),
            'datos_procesados' => $this->procesarDatos($pregunta),
            'estado' => 'completado'
        ];
    }

    /**
     * Determinar tipo de gráfico basado en tipo de pregunta y respuestas
     */
    private function determinarTipoGrafico(string $tipo, array $respuestas): string
    {
        $totalRespuestas = count($respuestas);

        switch ($tipo) {
            case 'seleccion_unica':
            case 'lista_desplegable':
                return $totalRespuestas <= 5 ? 'pastel' : 'barras';

            case 'casillas_verificacion':
                return 'barras';

            case 'escala_lineal':
                return 'histograma';

            case 'respuesta_corta':
            case 'parrafo':
                return 'barras'; // Para análisis de frecuencia de palabras

            case 'fecha':
                return 'lineas';

            case 'hora':
                return 'area';

            default:
                return 'barras';
        }
    }

    /**
     * Generar análisis local
     */
    private function generarAnalisisLocal(string $tipo, array $respuestas): string
    {
        $total = count($respuestas);

        if ($total === 0) {
            return "No hay respuestas disponibles para esta pregunta.";
        }

        switch ($tipo) {
            case 'seleccion_unica':
            case 'lista_desplegable':
                $frecuencias = array_count_values($respuestas);
                $masComun = array_search(max($frecuencias), $frecuencias);
                return "De {$total} respuestas, la opción más seleccionada fue '{$masComun}' con " .
                       round(($frecuencias[$masComun] / $total) * 100, 1) . "% de las respuestas.";

            case 'escala_lineal':
                $promedio = array_sum($respuestas) / $total;
                return "El promedio de las respuestas en la escala es {$promedio} de {$total} respuestas.";

            case 'casillas_verificacion':
                $totalSelecciones = array_sum($respuestas);
                return "Se registraron {$totalSelecciones} selecciones en total de {$total} respuestas.";

            default:
                return "Se analizaron {$total} respuestas para esta pregunta.";
        }
    }

    /**
     * Generar configuración local del gráfico
     */
    private function generarConfiguracionLocal(string $tipoGrafico): array
    {
        $configuraciones = [
            'barras' => [
                'type' => 'bar',
                'options' => [
                    'responsive' => true,
                    'scales' => [
                        'y' => ['beginAtZero' => true]
                    ]
                ]
            ],
            'pastel' => [
                'type' => 'pie',
                'options' => [
                    'responsive' => true,
                    'plugins' => [
                        'legend' => ['position' => 'bottom']
                    ]
                ]
            ],
            'lineas' => [
                'type' => 'line',
                'options' => [
                    'responsive' => true,
                    'scales' => [
                        'y' => ['beginAtZero' => true]
                    ]
                ]
            ],
            'histograma' => [
                'type' => 'bar',
                'options' => [
                    'responsive' => true,
                    'scales' => [
                        'y' => ['beginAtZero' => true]
                    ]
                ]
            ]
        ];

        return $configuraciones[$tipoGrafico] ?? $configuraciones['barras'];
    }

    /**
     * Procesar datos para el gráfico
     */
    private function procesarDatos(array $pregunta): array
    {
        $respuestas = $pregunta['respuestas'] ?? [];
        $tipo = $pregunta['tipo'];

        switch ($tipo) {
            case 'seleccion_unica':
            case 'lista_desplegable':
            case 'casillas_verificacion':
                $frecuencias = array_count_values($respuestas);
                return [
                    'labels' => array_keys($frecuencias),
                    'datasets' => [
                        [
                            'label' => 'Frecuencia',
                            'data' => array_values($frecuencias),
                            'backgroundColor' => $this->generarColores(count($frecuencias))
                        ]
                    ]
                ];

            case 'escala_lineal':
                return [
                    'labels' => ['Rango de valores'],
                    'datasets' => [
                        [
                            'label' => 'Distribución',
                            'data' => $respuestas,
                            'backgroundColor' => 'rgba(54, 162, 235, 0.5)'
                        ]
                    ]
                ];

            default:
                return [
                    'labels' => ['Respuestas'],
                    'datasets' => [
                        [
                            'label' => 'Cantidad',
                            'data' => [count($respuestas)],
                            'backgroundColor' => 'rgba(75, 192, 192, 0.5)'
                        ]
                    ]
                ];
        }
    }

    /**
     * Generar colores para gráficos
     */
    private function generarColores(int $cantidad): array
    {
        $colores = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)',
            'rgba(83, 102, 255, 0.8)'
        ];

        $resultado = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $resultado[] = $colores[$i % count($colores)];
        }

        return $resultado;
    }
}
