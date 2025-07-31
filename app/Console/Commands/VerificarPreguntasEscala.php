<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Models\Pregunta;
use Illuminate\Console\Command;
use Exception;

class VerificarPreguntasEscala extends Command
{
    protected $signature = 'preguntas:verificar-escala {encuesta_id?}';
    protected $description = 'Verificar y diagnosticar problemas con preguntas de escala';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🔍 VERIFICANDO PREGUNTAS DE ESCALA');
        $this->line('');

        try {
            if ($encuestaId) {
                // Verificar encuesta específica
                $encuesta = Encuesta::with(['preguntas'])->find($encuestaId);
                if (!$encuesta) {
                    $this->error('❌ Encuesta no encontrada con ID: ' . $encuestaId);
                    return 1;
                }
                $preguntas = $encuesta->preguntas;
            } else {
                // Verificar todas las preguntas de escala
                $preguntas = Pregunta::where('tipo', 'escala_lineal')->get();
            }

            $this->line('📊 ESTADÍSTICAS:');
            $this->line('   - Total preguntas encontradas: ' . $preguntas->count());
            $this->line('   - Preguntas de escala: ' . $preguntas->where('tipo', 'escala_lineal')->count());
            $this->line('');

            if ($preguntas->count() == 0) {
                $this->warn('⚠️  No se encontraron preguntas para verificar.');
                return 0;
            }

            $this->line('❓ ANÁLISIS DE PREGUNTAS:');
            $this->line('');

            foreach ($preguntas as $pregunta) {
                $this->line('   📝 Pregunta ID: ' . $pregunta->id);
                $this->line('      - Texto: ' . $pregunta->texto);
                $this->line('      - Tipo: ' . $pregunta->tipo);
                $this->line('      - Obligatoria: ' . ($pregunta->obligatoria ? 'Sí' : 'No'));

                if ($pregunta->tipo === 'escala_lineal') {
                    $this->line('      - Escala mínima: ' . ($pregunta->escala_min ?? 'NULL'));
                    $this->line('      - Escala máxima: ' . ($pregunta->escala_max ?? 'NULL'));
                    $this->line('      - Etiqueta mínima: ' . ($pregunta->escala_etiqueta_min ?? 'NULL'));
                    $this->line('      - Etiqueta máxima: ' . ($pregunta->escala_etiqueta_max ?? 'NULL'));

                    // Verificar si la escala es válida
                    if (!$pregunta->escala_max || $pregunta->escala_max <= 0) {
                        $this->error('         ❌ PROBLEMA: Escala máxima no definida o inválida');
                    } elseif ($pregunta->escala_max > 10) {
                        $this->warn('         ⚠️  ADVERTENCIA: Escala muy alta (' . $pregunta->escala_max . ')');
                    } else {
                        $this->line('         ✅ Escala válida: ' . ($pregunta->escala_min ?? 1) . ' a ' . $pregunta->escala_max);
                    }
                }

                $this->line('');
            }

            // Mostrar resumen de problemas
            $problemas = $preguntas->where('tipo', 'escala_lineal')->filter(function($p) {
                return !$p->escala_max || $p->escala_max <= 0;
            });

            if ($problemas->count() > 0) {
                $this->error('🚨 PROBLEMAS ENCONTRADOS:');
                $this->line('');

                foreach ($problemas as $pregunta) {
                    $this->line('   ❌ Pregunta ID ' . $pregunta->id . ': ' . $pregunta->texto);
                    $this->line('      - Escala máxima: ' . ($pregunta->escala_max ?? 'NULL'));
                }

                $this->line('');
                $this->line('🔧 SOLUCIONES RECOMENDADAS:');
                $this->line('   1. Verificar que las preguntas de escala tengan escala_max definida');
                $this->line('   2. Usar el comando: php artisan preguntas:corregir-escala');
                $this->line('   3. Recrear las preguntas problemáticas');
            } else {
                $this->info('✅ No se encontraron problemas con las escalas');
            }

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error verificando escalas: ' . $e->getMessage());
            return 1;
        }
    }
}
