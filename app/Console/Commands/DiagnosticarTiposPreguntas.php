<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Pregunta;
use App\Models\Encuesta;
use Exception;

class DiagnosticarTiposPreguntas extends Command
{
    protected $signature = 'preguntas:diagnosticar-tipos {--encuesta_id=} {--debug}';
    protected $description = 'Diagnostica la configuración de tipos de preguntas y sus necesidades';

    public function handle()
    {
        $encuestaId = $this->option('encuesta_id');
        $debug = $this->option('debug');

        $this->info("🔍 DIAGNÓSTICO DE TIPOS DE PREGUNTAS");
        $this->line('');

        try {
            // 1. Mostrar todos los tipos disponibles
            $this->info("📋 TIPOS DE PREGUNTAS DISPONIBLES:");
            $tipos = Pregunta::getTiposDisponibles();

            foreach ($tipos as $tipo => $config) {
                $necesitaRespuestas = $config['necesita_respuestas'] ? 'Sí' : 'No';
                $necesitaOpciones = $config['necesita_opciones'] ? 'Sí' : 'No';
                $permiteLogica = $config['necesita_opciones'] ? 'Sí' : 'No';

                $this->line("   📝 {$config['nombre']} ({$tipo})");
                $this->line("      Descripción: {$config['descripción']}");
                $this->line("      Necesita respuestas: {$necesitaRespuestas}");
                $this->line("      Necesita opciones: {$necesitaOpciones}");
                $this->line("      Permite lógica: {$permiteLogica}");
                $this->line("");
            }

            // 2. Si se especifica una encuesta, analizar sus preguntas
            if ($encuestaId) {
                $encuesta = Encuesta::find($encuestaId);
                if (!$encuesta) {
                    $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                    return 1;
                }

                $this->info("📊 ANÁLISIS DE ENCUESTA: '{$encuesta->titulo}' (ID: {$encuestaId})");
                $this->line('');

                $preguntas = $encuesta->preguntas()->with('respuestas')->get();

                if ($preguntas->isEmpty()) {
                    $this->warn("⚠️  No hay preguntas en esta encuesta");
                    return 0;
                }

                // Agrupar preguntas por tipo
                $preguntasPorTipo = $preguntas->groupBy('tipo');

                foreach ($preguntasPorTipo as $tipo => $preguntasDelTipo) {
                    $config = $tipos[$tipo] ?? null;
                    if (!$config) {
                        $this->warn("⚠️  Tipo '{$tipo}' no encontrado en configuración");
                        continue;
                    }

                    $this->info("📝 TIPO: {$config['nombre']} ({$tipo})");
                    $this->line("   Cantidad: {$preguntasDelTipo->count()} preguntas");
                    $this->line("   Necesita respuestas: " . ($config['necesita_respuestas'] ? 'Sí' : 'No'));
                    $this->line("   Permite lógica: " . ($config['necesita_opciones'] ? 'Sí' : 'No'));

                    // Analizar cada pregunta
                    foreach ($preguntasDelTipo as $pregunta) {
                        $this->line("   └─ Pregunta {$pregunta->orden}: {$pregunta->texto}");

                        if ($pregunta->necesitaRespuestas()) {
                            $respuestasCount = $pregunta->respuestas->count();
                            $this->line("      Respuestas configuradas: {$respuestasCount}");

                            if ($respuestasCount === 0) {
                                $this->warn("      ⚠️  NO TIENE RESPUESTAS CONFIGURADAS");
                            }
                        } else {
                            $this->line("      ✅ No necesita respuestas (tipo de entrada libre)");
                        }

                        if ($pregunta->permiteLogica()) {
                            $this->line("      ✅ Permite configuración de lógica");
                        } else {
                            $this->line("      ℹ️  No permite lógica (entrada libre)");
                        }
                    }
                    $this->line('');
                }

                // 3. Resumen de problemas
                $this->info("🔍 RESUMEN DE PROBLEMAS:");

                $problemas = [];

                // Preguntas que necesitan respuestas pero no las tienen
                $preguntasSinRespuestas = $preguntas->filter(function($pregunta) {
                    return $pregunta->necesitaRespuestas() && $pregunta->respuestas->isEmpty();
                });

                if ($preguntasSinRespuestas->isNotEmpty()) {
                    $problemas[] = "Preguntas que necesitan respuestas pero no las tienen: {$preguntasSinRespuestas->count()}";
                    foreach ($preguntasSinRespuestas as $pregunta) {
                        $this->warn("   ⚠️  Pregunta {$pregunta->orden}: {$pregunta->texto} ({$pregunta->getNombreTipo()})");
                    }
                }

                // Preguntas que no necesitan respuestas pero las tienen
                $preguntasConRespuestasInnecesarias = $preguntas->filter(function($pregunta) {
                    return !$pregunta->necesitaRespuestas() && $pregunta->respuestas->isNotEmpty();
                });

                if ($preguntasConRespuestasInnecesarias->isNotEmpty()) {
                    $problemas[] = "Preguntas con respuestas innecesarias: {$preguntasConRespuestasInnecesarias->count()}";
                    foreach ($preguntasConRespuestasInnecesarias as $pregunta) {
                        $this->warn("   ⚠️  Pregunta {$pregunta->orden}: {$pregunta->texto} ({$pregunta->getNombreTipo()}) - Tiene {$pregunta->respuestas->count()} respuestas innecesarias");
                    }
                }

                if (empty($problemas)) {
                    $this->info("   ✅ No se encontraron problemas de configuración");
                } else {
                    $this->line('');
                    $this->info("💡 RECOMENDACIONES:");
                    foreach ($problemas as $problema) {
                        $this->line("   ➡️  {$problema}");
                    }
                }

                $this->line('');
            }

            // 4. Debug mode - información adicional
            if ($debug) {
                $this->info("🔧 INFORMACIÓN DE DEBUG:");
                $this->line("   Total de tipos configurados: " . count($tipos));
                $this->line("   Tipos que necesitan respuestas: " . collect($tipos)->where('necesita_respuestas', true)->count());
                $this->line("   Tipos que permiten lógica: " . collect($tipos)->where('necesita_opciones', true)->count());
                $this->line('');
            }

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante el diagnóstico: " . $e->getMessage());

            if ($debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }
}
