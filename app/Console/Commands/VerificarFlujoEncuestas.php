<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use Illuminate\Console\Command;

class VerificarFlujoEncuestas extends Command
{
    protected $signature = 'encuestas:verificar-flujo {--encuesta_id=}';
    protected $description = 'Verifica el flujo completo de configuración de encuestas';

    public function handle()
    {
        $encuestaId = $this->option('encuesta_id');

        $this->info("=== VERIFICACIÓN DEL FLUJO DE CONFIGURACIÓN DE ENCUESTAS ===");

        if ($encuestaId) {
            $this->verificarEncuestaEspecifica($encuestaId);
        } else {
            $this->verificarTodasLasEncuestas();
        }

        return 0;
    }

    /**
     * Verificar una encuesta específica
     */
    private function verificarEncuestaEspecifica(int $encuestaId)
    {
        $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])->find($encuestaId);

        if (!$encuesta) {
            $this->error("Encuesta con ID {$encuestaId} no encontrada.");
            return;
        }

        $this->info("📋 ENCUESTA: {$encuesta->titulo} (ID: {$encuesta->id})");
        $this->info("Empresa: {$encuesta->empresa->nombre_legal}");
        $this->info("Estado: {$encuesta->estado}");
        $this->info("Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));

        // Verificar progreso
        $progreso = $encuesta->obtenerProgresoConfiguracion();
        $this->info("\n📊 PROGRESO DE CONFIGURACIÓN:");
        $this->info("Completados: {$progreso['completados']}/{$progreso['total']}");
        $this->info("Porcentaje: {$progreso['porcentaje']}%");

        // Verificar cada paso
        $this->info("\n🔍 VERIFICACIÓN DE PASOS:");
        foreach ($progreso['pasos'] as $clave => $paso) {
            $icono = $paso['completado'] ? '✅' : '❌';
            $estado = $paso['completado'] ? 'COMPLETADO' : 'PENDIENTE';
            $cantidad = isset($paso['cantidad']) ? " ({$paso['cantidad']})" : '';

            $this->info("   {$icono} {$paso['nombre']}{$cantidad}: {$estado}");
        }

        // Verificar estadísticas
        $stats = $encuesta->obtenerEstadisticasConfiguracion();
        $this->info("\n📈 ESTADÍSTICAS DETALLADAS:");
        $this->info("   • Total preguntas: {$stats['total_preguntas']}");
        $this->info("   • Preguntas obligatorias: {$stats['preguntas_obligatorias']}");
        $this->info("   • Preguntas opcionales: {$stats['preguntas_opcionales']}");
        $this->info("   • Preguntas con respuestas: {$stats['preguntas_con_respuestas']}");
        $this->info("   • Preguntas sin respuestas: {$stats['preguntas_sin_respuestas']}");
        $this->info("   • Preguntas con lógica: {$stats['preguntas_con_logica']}");
        $this->info("   • Completamente configurada: " . ($stats['completada'] ? 'Sí' : 'No'));

        // Verificar problemas
        $this->verificarProblemas($encuesta, $progreso, $stats);
    }

    /**
     * Verificar todas las encuestas
     */
    private function verificarTodasLasEncuestas()
    {
        $encuestas = Encuesta::with(['preguntas.respuestas', 'empresa'])->get();

        $this->info("📊 ESTADÍSTICAS GENERALES:");
        $this->info("Total encuestas: {$encuestas->count()}");

        $completamenteConfiguradas = 0;
        $enProgreso = 0;
        $sinIniciar = 0;

        foreach ($encuestas as $encuesta) {
            $progreso = $encuesta->obtenerProgresoConfiguracion();

            if ($progreso['completados'] === $progreso['total']) {
                $completamenteConfiguradas++;
            } elseif ($progreso['completados'] > 0) {
                $enProgreso++;
            } else {
                $sinIniciar++;
            }
        }

        $this->info("   • Completamente configuradas: {$completamenteConfiguradas}");
        $this->info("   • En progreso: {$enProgreso}");
        $this->info("   • Sin iniciar: {$sinIniciar}");

        // Mostrar encuestas con problemas
        $this->info("\n⚠️ ENCUESTAS CON PROBLEMAS:");
        $encuestasConProblemas = 0;

        foreach ($encuestas as $encuesta) {
            $progreso = $encuesta->obtenerProgresoConfiguracion();
            $stats = $encuesta->obtenerEstadisticasConfiguracion();

            $problemas = [];

            if ($stats['total_preguntas'] === 0) {
                $problemas[] = 'Sin preguntas';
            }

            if ($stats['preguntas_sin_respuestas'] > 0) {
                $problemas[] = "{$stats['preguntas_sin_respuestas']} preguntas sin respuestas";
            }

            if ($stats['preguntas_con_logica'] === 0 && $stats['total_preguntas'] > 0) {
                $problemas[] = 'Sin lógica configurada';
            }

            if (!empty($problemas)) {
                $encuestasConProblemas++;
                $this->warn("   • {$encuesta->titulo} (ID: {$encuesta->id}): " . implode(', ', $problemas));
            }
        }

        if ($encuestasConProblemas === 0) {
            $this->info("   ✅ No se encontraron encuestas con problemas");
        }
    }

    /**
     * Verificar problemas específicos
     */
    private function verificarProblemas(Encuesta $encuesta, array $progreso, array $stats)
    {
        $this->info("\n🔍 VERIFICACIÓN DE PROBLEMAS:");

        $problemas = [];

        // Verificar si tiene título y empresa
        if (empty($encuesta->titulo)) {
            $problemas[] = 'Sin título';
        }
        if (empty($encuesta->empresa_id)) {
            $problemas[] = 'Sin empresa asignada';
        }

        // Verificar preguntas
        if ($stats['total_preguntas'] === 0) {
            $problemas[] = 'Sin preguntas configuradas';
        }

        // Verificar respuestas
        if ($stats['preguntas_sin_respuestas'] > 0) {
            $problemas[] = "{$stats['preguntas_sin_respuestas']} preguntas sin respuestas";
        }

        // Verificar lógica
        if ($stats['preguntas_con_logica'] === 0 && $stats['total_preguntas'] > 0) {
            $problemas[] = 'Sin lógica configurada';
        }

        // Verificar estado
        if ($encuesta->estado === 'borrador' && $progreso['completados'] === $progreso['total']) {
            $problemas[] = 'Completamente configurada pero en estado borrador';
        }

        if (empty($problemas)) {
            $this->info("   ✅ No se encontraron problemas");
        } else {
            foreach ($problemas as $problema) {
                $this->warn("   ⚠️ {$problema}");
            }
        }

        // Recomendaciones
        if (!empty($problemas)) {
            $this->info("\n💡 RECOMENDACIONES:");

            if (in_array('Sin preguntas configuradas', $problemas)) {
                $this->info("   • Agregar preguntas a la encuesta");
            }

            if (in_array('Sin respuestas', $problemas)) {
                $this->info("   • Configurar respuestas para preguntas de selección");
            }

            if (in_array('Sin lógica configurada', $problemas)) {
                $this->info("   • Configurar lógica de saltos entre preguntas");
            }

            if (in_array('en estado borrador', $problemas)) {
                $this->info("   • Cambiar el estado de la encuesta a 'enviada' o 'publicada'");
            }
        }
    }
}
