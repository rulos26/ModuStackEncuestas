<?php

namespace App\Console\Commands;

use App\Models\Pregunta;
use App\Models\Encuesta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProbarCreacionPregunta extends Command
{
    protected $signature = 'preguntas:probar-creacion {--encuesta_id=} {--tipo=respuesta_corta}';
    protected $description = 'Prueba la creación de preguntas específicamente';

    public function handle()
    {
        $this->info('🧪 PROBANDO CREACIÓN DE PREGUNTAS');
        $this->info('==================================');

        try {
            // Obtener encuesta
            $encuestaId = $this->option('encuesta_id');
            $encuesta = $this->obtenerEncuesta($encuestaId);

            // Probar diferentes tipos de preguntas
            $this->probarTiposPreguntas($encuesta);

            $this->info("\n🎉 PRUEBA COMPLETADA EXITOSAMENTE");
            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ ERROR DURANTE LA PRUEBA:");
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function obtenerEncuesta($encuestaId = null)
    {
        if ($encuestaId) {
            $encuesta = Encuesta::find($encuestaId);
            if (!$encuesta) {
                throw new \Exception("Encuesta con ID {$encuestaId} no encontrada.");
            }
        } else {
            $encuesta = Encuesta::first();
            if (!$encuesta) {
                throw new \Exception('No hay encuestas disponibles. Crea una encuesta primero.');
            }
        }

        $this->info("   🏢 Usando encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");
        return $encuesta;
    }

    private function probarTiposPreguntas($encuesta)
    {
        $tiposPrueba = [
            'respuesta_corta' => [
                'texto' => '¿Cuál es tu nombre completo?',
                'descripcion' => 'Ingresa tu nombre completo',
                'placeholder' => 'Ej: Juan Pérez',
                'obligatoria' => true
            ],
            'parrafo' => [
                'texto' => 'Describe tu experiencia laboral',
                'descripcion' => 'Cuéntanos sobre tu experiencia',
                'placeholder' => 'Describe tu experiencia...',
                'obligatoria' => false
            ],
            'seleccion_unica' => [
                'texto' => '¿Cuál es tu nivel de educación?',
                'descripcion' => 'Selecciona tu nivel educativo',
                'obligatoria' => true
            ],
            'escala_lineal' => [
                'texto' => '¿Qué tan satisfecho estás con el servicio?',
                'descripcion' => 'Evalúa del 1 al 10',
                'escala_min' => 1,
                'escala_max' => 10,
                'escala_etiqueta_min' => 'Muy insatisfecho',
                'escala_etiqueta_max' => 'Muy satisfecho',
                'obligatoria' => true
            ]
        ];

        foreach ($tiposPrueba as $tipo => $datos) {
            $this->info("\n🔧 Probando tipo: {$tipo}");
            $this->crearPreguntaPrueba($encuesta, $tipo, $datos);
        }
    }

    private function crearPreguntaPrueba($encuesta, $tipo, $datos)
    {
        try {
            // Calcular orden automático
            $orden = Pregunta::calcularOrdenAutomatico($encuesta->id);
            $this->info("   📊 Orden calculado: {$orden}");

            // Preparar datos base
            $datosPregunta = [
                'encuesta_id' => $encuesta->id,
                'texto' => $datos['texto'] . ' - ' . now()->format('H:i:s'),
                'descripcion' => $datos['descripcion'],
                'tipo' => $tipo,
                'orden' => $orden,
                'obligatoria' => $datos['obligatoria'] ?? false
            ];

            // Agregar campos específicos según el tipo
            if (isset($datos['placeholder'])) {
                $datosPregunta['placeholder'] = $datos['placeholder'];
            }

            if (isset($datos['escala_min'])) {
                $datosPregunta['escala_min'] = $datos['escala_min'];
                $datosPregunta['escala_max'] = $datos['escala_max'];
                $datosPregunta['escala_etiqueta_min'] = $datos['escala_etiqueta_min'];
                $datosPregunta['escala_etiqueta_max'] = $datos['escala_etiqueta_max'];
            }

            $this->info("   📝 Datos preparados para tipo: {$tipo}");

            // Crear pregunta
            DB::beginTransaction();

            $pregunta = Pregunta::create($datosPregunta);

            DB::commit();

            $this->info("   ✅ Pregunta creada exitosamente");
            $this->info("   🆔 ID: {$pregunta->id}");
            $this->info("   📊 Texto: {$pregunta->texto}");
            $this->info("   🎨 Tipo: {$pregunta->tipo}");
            $this->info("   📅 Orden: {$pregunta->orden}");
            $this->info("   ⚠️ Obligatoria: " . ($pregunta->obligatoria ? 'Sí' : 'No'));

            // Verificar métodos específicos
            $this->verificarMetodosEspecificos($pregunta);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("   ❌ Error creando pregunta tipo {$tipo}: " . $e->getMessage());

            // Log detallado del error
            Log::error('Error en prueba de creación de pregunta', [
                'tipo' => $tipo,
                'encuesta_id' => $encuesta->id,
                'datos' => $datosPregunta ?? [],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function verificarMetodosEspecificos($pregunta)
    {
        // Verificar método necesitaRespuestas
        if (method_exists($pregunta, 'necesitaRespuestas')) {
            $necesitaRespuestas = $pregunta->necesitaRespuestas();
            $this->info("   🔍 necesitaRespuestas(): " . ($necesitaRespuestas ? 'Sí' : 'No'));
        }

        // Verificar método necesitaOpciones
        if (method_exists($pregunta, 'necesitaOpciones')) {
            $necesitaOpciones = $pregunta->necesitaOpciones();
            $this->info("   🔍 necesitaOpciones(): " . ($necesitaOpciones ? 'Sí' : 'No'));
        }

        // Verificar método getNombreTipo
        if (method_exists($pregunta, 'getNombreTipo')) {
            $nombreTipo = $pregunta->getNombreTipo();
            $this->info("   🔍 getNombreTipo(): {$nombreTipo}");
        }

        // Verificar método getIconoTipo
        if (method_exists($pregunta, 'getIconoTipo')) {
            $iconoTipo = $pregunta->getIconoTipo();
            $this->info("   🔍 getIconoTipo(): {$iconoTipo}");
        }

        // Verificar relaciones
        $this->info("   🔗 Relaciones:");
        $this->info("      • encuesta: " . ($pregunta->encuesta ? '✅' : '❌'));
        $this->info("      • respuestas: " . $pregunta->respuestas->count() . " respuestas");
        $this->info("      • logica: " . $pregunta->logica->count() . " lógicas");
    }
}
