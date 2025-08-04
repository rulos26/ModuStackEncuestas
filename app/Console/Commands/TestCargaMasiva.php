<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class TestCargaMasiva extends Command
{
    protected $signature = 'test:carga-masiva {encuesta_id?} {--create-files}';
    protected $description = 'Probar el módulo de carga masiva de encuestas';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $createFiles = $this->option('create-files');

        if (!$encuestaId) {
            $encuesta = Encuesta::first();
            if (!$encuesta) {
                $this->error('No hay encuestas disponibles para probar');
                return 1;
            }
            $encuestaId = $encuesta->id;
        }

        $encuesta = Encuesta::find($encuestaId);
        if (!$encuesta) {
            $this->error("Encuesta con ID {$encuestaId} no encontrada");
            return 1;
        }

        $this->info("=== PRUEBA DE CARGA MASIVA ===");
        $this->info("Encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");
        $this->line('');

        // Crear archivos de prueba si se solicita
        if ($createFiles) {
            $this->createTestFiles($encuesta);
        }

        // Probar funcionalidades
        $this->testRoutes();
        $this->testDataProcessing($encuesta);
        $this->testFileReading();
        $this->testTypePrediction();

        $this->info('✅ Pruebas completadas');
        return 0;
    }

    private function testRoutes()
    {
        $this->info('🔗 PROBANDO RUTAS:');

        $routes = [
            'carga-masiva.index' => 'Página principal',
            'carga-masiva.procesar-preguntas' => 'Procesar preguntas',
            'carga-masiva.wizard-preguntas' => 'Wizard de preguntas',
            'carga-masiva.guardar-tipo-pregunta' => 'Guardar tipo pregunta',
            'carga-masiva.confirmar-preguntas' => 'Confirmar preguntas',
            'carga-masiva.guardar-preguntas' => 'Guardar preguntas',
            'carga-masiva.cargar-respuestas' => 'Cargar respuestas',
            'carga-masiva.procesar-respuestas' => 'Procesar respuestas'
        ];

        foreach ($routes as $routeName => $description) {
            try {
                $url = route($routeName);
                $this->line("  ✅ {$description}: {$url}");
            } catch (\Exception $e) {
                $this->error("  ❌ {$description}: Error - {$e->getMessage()}");
            }
        }

        $this->line('');
    }

    private function testDataProcessing($encuesta)
    {
        $this->info('📊 PROBANDO PROCESAMIENTO DE DATOS:');

        // Simular datos de preguntas
        $preguntasTest = [
            ['texto' => '¿Cuál es tu nombre completo?', 'tipo' => 'texto_corto'],
            ['texto' => '¿Cuál es tu edad?', 'tipo' => 'texto_corto'],
            ['texto' => '¿Cuál es tu profesión?', 'tipo' => 'texto_corto'],
            ['texto' => '¿Cuál es tu nivel de satisfacción?', 'tipo' => 'escala'],
            ['texto' => '¿Qué servicios utilizas?', 'tipo' => 'casilla']
        ];

        $this->line("  📝 Preguntas de prueba: " . count($preguntasTest));

        // Simular datos de respuestas
        $respuestasTest = [
            ['numero_pregunta' => 1, 'contenido' => 'Juan Pérez'],
            ['numero_pregunta' => 2, 'contenido' => '25'],
            ['numero_pregunta' => 3, 'contenido' => 'Ingeniero'],
            ['numero_pregunta' => 4, 'contenido' => '8'],
            ['numero_pregunta' => 5, 'contenido' => 'Servicio A, Servicio B']
        ];

        $this->line("  📝 Respuestas de prueba: " . count($respuestasTest));

        // Probar validaciones
        $this->line("  ✅ Validaciones de compatibilidad:");
        $controller = new \App\Http\Controllers\CargaMasivaEncuestasController();

        foreach ($preguntasTest as $index => $pregunta) {
            $respuesta = $respuestasTest[$index] ?? null;
            if ($respuesta) {
                // Simular determinación de tipo de respuesta
                $tipoRespuesta = $this->determinarTipoRespuesta($respuesta['contenido']);
                $compatible = $this->validarCompatibilidad($pregunta['tipo'], $tipoRespuesta);
                $status = $compatible ? '✅' : '❌';
                $this->line("    {$status} Pregunta " . ($index + 1) . ": {$pregunta['tipo']} → {$tipoRespuesta}");
            }
        }

        $this->line('');
    }

    private function testFileReading()
    {
        $this->info('📁 PROBANDO LECTURA DE ARCHIVOS:');

        // Simular contenido de archivo de preguntas
        $contenidoPreguntas = "¿Cuál es tu nombre completo?\n¿Cuál es tu edad?\n¿Cuál es tu profesión?\n¿Cuál es tu nivel de satisfacción?";

        // Simular contenido de archivo de respuestas
        $contenidoRespuestas = "R_1: Juan Pérez\nR_2: 25\nR_3: Ingeniero\nR_4: 8";

        $this->line("  📄 Archivo preguntas (simulado):");
        $this->line("    " . str_replace("\n", "\n    ", $contenidoPreguntas));

        $this->line("  📄 Archivo respuestas (simulado):");
        $this->line("    " . str_replace("\n", "\n    ", $contenidoRespuestas));

        // Probar parsing
        $lineasPreguntas = explode("\n", trim($contenidoPreguntas));
        $preguntas = [];
        foreach ($lineasPreguntas as $index => $linea) {
            $linea = trim($linea);
            if (!empty($linea)) {
                $preguntas[] = [
                    'texto' => $linea,
                    'tipo' => null,
                    'orden' => $index + 1
                ];
            }
        }

        $this->line("  ✅ Preguntas extraídas: " . count($preguntas));

        $lineasRespuestas = explode("\n", trim($contenidoRespuestas));
        $respuestas = [];
        foreach ($lineasRespuestas as $linea) {
            $linea = trim($linea);
            if (!empty($linea)) {
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

        $this->line("  ✅ Respuestas extraídas: " . count($respuestas));

        $this->line('');
    }

    private function testTypePrediction()
    {
        $this->info('🤖 PROBANDO PREDICCIÓN DE TIPOS:');

        $preguntasTest = [
            '¿Cuál es tu nombre completo?' => 'texto_corto',
            '¿Cuál es tu email?' => 'texto_corto',
            '¿Cuál es tu teléfono?' => 'texto_corto',
            '¿Describe tu experiencia con el servicio?' => 'parrafo',
            '¿Cuál es tu opinión sobre la atención?' => 'parrafo',
            '¿Selecciona tu género?' => 'seleccion_unica',
            '¿Cuál opción prefieres?' => 'seleccion_unica',
            '¿Marca todas las opciones que apliquen?' => 'casilla',
            '¿Selecciona de la lista?' => 'lista_desplegable',
            '¿En una escala del 1 al 10?' => 'escala',
            '¿Evalúa en la siguiente tabla?' => 'cuadricula'
        ];

        $aciertos = 0;
        $total = count($preguntasTest);

        foreach ($preguntasTest as $pregunta => $tipoEsperado) {
            $tipoPredicho = $this->predecirTipoPregunta($pregunta);
            $correcto = $tipoPredicho === $tipoEsperado;
            $status = $correcto ? '✅' : '❌';

            if ($correcto) $aciertos++;

            $this->line("  {$status} \"{$pregunta}\"");
            $this->line("    Esperado: {$tipoEsperado}, Predicho: {$tipoPredicho}");
        }

        $precision = round(($aciertos / $total) * 100, 1);
        $this->line("  📊 Precisión: {$aciertos}/{$total} ({$precision}%)");

        $this->line('');
    }

    private function createTestFiles($encuesta)
    {
        $this->info('📝 CREANDO ARCHIVOS DE PRUEBA:');

        // Crear archivo de preguntas
        $preguntas = [
            '¿Cuál es tu nombre completo?',
            '¿Cuál es tu edad?',
            '¿Cuál es tu profesión?',
            '¿Cuál es tu nivel de satisfacción?',
            '¿Qué servicios utilizas?',
            '¿Cuál es tu opinión sobre el servicio?',
            '¿Selecciona tu género?',
            '¿En una escala del 1 al 10, califica la atención?'
        ];

        $contenidoPreguntas = implode("\n", $preguntas);
        $rutaPreguntas = storage_path('app/test_preguntas.txt');
        file_put_contents($rutaPreguntas, $contenidoPreguntas);

        $this->line("  ✅ Archivo de preguntas creado: {$rutaPreguntas}");

        // Crear archivo de respuestas
        $respuestas = [
            'R_1: Juan Pérez',
            'R_2: 25',
            'R_3: Ingeniero',
            'R_4: 8',
            'R_5: Servicio A, Servicio B',
            'R_6: Muy satisfecho con el servicio recibido',
            'R_7: Masculino',
            'R_8: 9'
        ];

        $contenidoRespuestas = implode("\n", $respuestas);
        $rutaRespuestas = storage_path('app/test_respuestas.txt');
        file_put_contents($rutaRespuestas, $contenidoRespuestas);

        $this->line("  ✅ Archivo de respuestas creado: {$rutaRespuestas}");

        $this->line('');
    }

    /**
     * Determinar tipo de respuesta (simulado)
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
     * Predecir tipo de pregunta usando IA (simulado)
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
}
