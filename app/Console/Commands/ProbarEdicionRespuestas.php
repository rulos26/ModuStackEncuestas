<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Illuminate\Support\Facades\DB;
use Exception;

class ProbarEdicionRespuestas extends Command
{
    protected $signature = 'probar:edicion-respuestas {encuesta_id?}';
    protected $description = 'Probar la funcionalidad de edición de respuestas';

    public function handle()
    {
        $this->info('🧪 PROBANDO EDICIÓN DE RESPUESTAS');
        $this->line('');

        try {
            $encuestaId = $this->argument('encuesta_id');

            if ($encuestaId) {
                $encuesta = Encuesta::find($encuestaId);
                if (!$encuesta) {
                    $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                    return 1;
                }
                $this->probarEncuestaEspecifica($encuesta);
            } else {
                $this->probarTodasLasEncuestas();
            }

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Probar una encuesta específica
     */
    private function probarEncuestaEspecifica($encuesta)
    {
        $this->line("📋 PROBANDO ENCUESTA ESPECÍFICA:");
        $this->line("   • ID: {$encuesta->id}");
        $this->line("   • Título: {$encuesta->titulo}");
        $this->line("   • Estado: {$encuesta->estado}");
        $this->line('');

        $preguntas = $encuesta->preguntas()->necesitaRespuestas()->with('respuestas')->get();

        if ($preguntas->isEmpty()) {
            $this->warn('⚠️  No hay preguntas que necesiten respuestas');
            return;
        }

        $this->line("📊 Preguntas con respuestas: {$preguntas->count()}");
        $this->line('');

        foreach ($preguntas as $pregunta) {
            $this->probarPregunta($pregunta);
        }
    }

    /**
     * Probar todas las encuestas
     */
    private function probarTodasLasEncuestas()
    {
        $this->line('📋 PROBANDO TODAS LAS ENCUESTAS');
        $this->line('');

        $encuestas = Encuesta::with(['preguntas.respuestas'])->get();

        if ($encuestas->isEmpty()) {
            $this->warn('⚠️  No hay encuestas en la base de datos');
            return;
        }

        $this->line("📊 Total de encuestas: {$encuestas->count()}");
        $this->line('');

        foreach ($encuestas as $encuesta) {
            $preguntasConRespuestas = $encuesta->preguntas()->necesitaRespuestas()->with('respuestas')->get();

            if ($preguntasConRespuestas->isNotEmpty()) {
                $this->line("🔍 Encuesta ID {$encuesta->id}: {$encuesta->titulo}");
                $this->line("   📊 Preguntas con respuestas: {$preguntasConRespuestas->count()}");

                foreach ($preguntasConRespuestas as $pregunta) {
                    $this->line("      • Pregunta {$pregunta->id}: {$pregunta->texto} ({$pregunta->respuestas->count()} respuestas)");
                }
                $this->line('');
            }
        }
    }

    /**
     * Probar una pregunta específica
     */
    private function probarPregunta($pregunta)
    {
        $this->line("🔍 Pregunta ID {$pregunta->id}: {$pregunta->texto}");
        $this->line("   📊 Respuestas actuales: {$pregunta->respuestas->count()}");

        if ($pregunta->respuestas->isNotEmpty()) {
            foreach ($pregunta->respuestas as $respuesta) {
                $this->line("      • ID {$respuesta->id}: {$respuesta->texto} (orden: {$respuesta->orden})");
            }
        }

        // Simular edición de respuestas
        $this->simularEdicionRespuestas($pregunta);
        $this->line('');
    }

    /**
     * Simular edición de respuestas
     */
    private function simularEdicionRespuestas($pregunta)
    {
        $this->line("   🧪 Simulando edición de respuestas...");

        try {
            DB::beginTransaction();

            // Simular datos de edición
            $datosEdicion = [
                [
                    'id' => $pregunta->respuestas->first()?->id,
                    'texto' => 'Respuesta modificada - ' . now()->format('H:i:s'),
                    'orden' => 1
                ],
                [
                    'texto' => 'Nueva respuesta - ' . now()->format('H:i:s'),
                    'orden' => 2
                ]
            ];

            $respuestasActualizadas = 0;
            $respuestasCreadas = 0;

            // Procesar edición
            foreach ($datosEdicion as $index => $respuestaData) {
                if (isset($respuestaData['id']) && !empty($respuestaData['id'])) {
                    // Actualizar respuesta existente
                    $respuesta = Respuesta::find($respuestaData['id']);
                    if ($respuesta && $respuesta->pregunta_id == $pregunta->id) {
                        $respuesta->update([
                            'texto' => $respuestaData['texto'],
                            'orden' => $respuestaData['orden'] ?? $index + 1
                        ]);
                        $respuestasActualizadas++;
                        $this->line("      ✅ Respuesta actualizada: {$respuestaData['texto']}");
                    }
                } else {
                    // Crear nueva respuesta
                    $nuevaRespuesta = Respuesta::create([
                        'pregunta_id' => $pregunta->id,
                        'texto' => $respuestaData['texto'],
                        'orden' => $respuestaData['orden'] ?? $index + 1
                    ]);
                    $respuestasCreadas++;
                    $this->line("      ✅ Nueva respuesta creada: {$respuestaData['texto']}");
                }
            }

            DB::commit();

            $this->line("   ✅ Simulación completada:");
            $this->line("      • Respuestas actualizadas: {$respuestasActualizadas}");
            $this->line("      • Respuestas creadas: {$respuestasCreadas}");

        } catch (Exception $e) {
            DB::rollBack();
            $this->error("   ❌ Error en simulación: " . $e->getMessage());
        }
    }
}
