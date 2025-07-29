<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Encuesta;
use App\Models\Pregunta;
use Exception;

class DiagnosticarProgresoEncuesta extends Command
{
    protected $signature = 'encuesta:diagnosticar-progreso {encuesta_id} {--debug}';
    protected $description = 'Diagnostica el progreso de configuración de una encuesta';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $debug = $this->option('debug');

        $this->info("🔍 DIAGNÓSTICO DE PROGRESO DE ENCUESTA");
        $this->line('');

        try {
            $encuesta = Encuesta::with(['preguntas.respuestas', 'preguntas.logica'])->find($encuestaId);

            if (!$encuesta) {
                $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                return 1;
            }

            $this->info("📋 ENCUESTA: '{$encuesta->titulo}' (ID: {$encuestaId})");
            $this->line('');

            // 1. Análisis de preguntas
            $this->analizarPreguntas($encuesta);

            // 2. Análisis de progreso
            $this->analizarProgreso($encuesta);

            // 3. Verificar lógica de pasos
            $this->verificarLogicaPasos($encuesta);

            // 4. Recomendaciones
            $this->mostrarRecomendaciones($encuesta);

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

    private function analizarPreguntas($encuesta)
    {
        $this->info("❓ ANÁLISIS DE PREGUNTAS:");

        $preguntas = $encuesta->preguntas;
        $totalPreguntas = $preguntas->count();

        $this->line("   Total de preguntas: {$totalPreguntas}");

        if ($totalPreguntas === 0) {
            $this->warn("   ⚠️  No hay preguntas en la encuesta");
            return;
        }

        // Agrupar por tipo
        $preguntasPorTipo = $preguntas->groupBy('tipo');

        foreach ($preguntasPorTipo as $tipo => $preguntasDelTipo) {
            $config = Pregunta::getTiposDisponibles()[$tipo] ?? null;
            $nombreTipo = $config['nombre'] ?? $tipo;

            $this->line("   📝 {$nombreTipo}: {$preguntasDelTipo->count()} preguntas");

            foreach ($preguntasDelTipo as $pregunta) {
                $necesitaRespuestas = $pregunta->necesitaRespuestas() ? 'Sí' : 'No';
                $permiteLogica = $pregunta->permiteLogica() ? 'Sí' : 'No';
                $respuestasCount = $pregunta->respuestas->count();

                $this->line("      └─ Pregunta {$pregunta->orden}: {$pregunta->texto}");
                $this->line("         Necesita respuestas: {$necesitaRespuestas}");
                $this->line("         Permite lógica: {$permiteLogica}");
                $this->line("         Respuestas configuradas: {$respuestasCount}");
            }
        }

        $this->line('');
    }

    private function analizarProgreso($encuesta)
    {
        $this->info("📊 ANÁLISIS DE PROGRESO:");

        $progreso = $encuesta->obtenerProgresoConfiguracion();

        $this->line("   Porcentaje completado: {$progreso['porcentaje']}%");
        $this->line("   Pasos completados: {$progreso['completados']}/{$progreso['total']}");

        if ($progreso['siguiente_paso']) {
            $this->line("   Siguiente paso: {$progreso['siguiente_paso']}");
        } else {
            $this->line("   ✅ Todos los pasos completados");
        }

        $this->line('');

        // Detalle de cada paso
        foreach ($progreso['pasos'] as $clave => $paso) {
            $estado = $paso['completado'] ? '✅ Completado' : '⏳ Pendiente';
            $necesario = isset($paso['necesario']) ? ($paso['necesario'] ? 'Sí' : 'No') : 'N/A';

            $this->line("   {$paso['nombre']}: {$estado} (Necesario: {$necesario})");

            if (isset($paso['mensaje'])) {
                $this->line("      ℹ️  {$paso['mensaje']}");
            }
        }

        $this->line('');
    }

    private function verificarLogicaPasos($encuesta)
    {
        $this->info("🔍 VERIFICACIÓN DE LÓGICA DE PASOS:");

        // Verificar si necesita configurar lógica
        $necesitaLogica = $encuesta->necesitaConfigurarLogica();
        $todasTextoLibre = $encuesta->todasLasPreguntasSonTextoLibre();

        $this->line("   ¿Necesita configurar lógica?: " . ($necesitaLogica ? 'Sí' : 'No'));
        $this->line("   ¿Todas las preguntas son texto libre?: " . ($todasTextoLibre ? 'Sí' : 'No'));

        if ($todasTextoLibre && $necesitaLogica) {
            $this->warn("   ⚠️  INCONSISTENCIA: Todas las preguntas son texto libre pero necesita lógica");
        } elseif (!$todasTextoLibre && !$necesitaLogica) {
            $this->warn("   ⚠️  INCONSISTENCIA: Hay preguntas de selección pero no necesita lógica");
        } else {
            $this->info("   ✅ Lógica de pasos correcta");
        }

        $this->line('');
    }

    private function mostrarRecomendaciones($encuesta)
    {
        $this->info("💡 RECOMENDACIONES:");

        $progreso = $encuesta->obtenerProgresoConfiguracion();
        $necesitaLogica = $encuesta->necesitaConfigurarLogica();
        $todasTextoLibre = $encuesta->todasLasPreguntasSonTextoLibre();

        if ($progreso['siguiente_paso'] === 'configurar_logica' && !$necesitaLogica) {
            $this->line("   ➡️  El paso 'Configurar Lógica' no es necesario para preguntas de texto libre");
            $this->line("   ➡️  Puedes saltar directamente a 'Configurar Envío'");
        }

        if ($todasTextoLibre) {
            $this->line("   ➡️  Todas las preguntas son de texto libre");
            $this->line("   ➡️  No necesitas configurar respuestas ni lógica");
        }

        if ($progreso['porcentaje'] === 100) {
            $this->line("   ➡️  La encuesta está completamente configurada");
            $this->line("   ➡️  Puedes proceder con el envío masivo");
        }

        $this->line('');
    }
}
