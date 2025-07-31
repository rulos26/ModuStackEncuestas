<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class VerificarRespuestasEncuesta extends Command
{
    protected $signature = 'encuesta:verificar-respuestas {encuesta_id}';
    protected $description = 'Verificar las respuestas guardadas de una encuesta específica';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🔍 VERIFICANDO RESPUESTAS DE ENCUESTA');
        $this->line('');

        try {
            // Buscar encuesta
            $encuesta = Encuesta::with(['preguntas.respuestas'])->find($encuestaId);

            if (!$encuesta) {
                $this->error('❌ Encuesta no encontrada con ID: ' . $encuestaId);
                return 1;
            }

            $this->line('📝 Encuesta: ' . $encuesta->titulo);
            $this->line('   - ID: ' . $encuesta->id);
            $this->line('   - Estado: ' . $encuesta->estado);
            $this->line('   - Preguntas: ' . $encuesta->preguntas->count());
            $this->line('');

            // Contar respuestas totales
            $totalRespuestas = DB::table('respuestas_usuario')
                ->where('encuesta_id', $encuestaId)
                ->count();

            $this->line('📊 ESTADÍSTICAS GENERALES:');
            $this->line('   - Total de respuestas guardadas: ' . $totalRespuestas);
            $this->line('');

            if ($totalRespuestas == 0) {
                $this->warn('⚠️  No hay respuestas guardadas para esta encuesta.');
                return 0;
            }

            // Agrupar respuestas por IP (usuarios únicos)
            $usuariosUnicos = DB::table('respuestas_usuario')
                ->where('encuesta_id', $encuestaId)
                ->distinct()
                ->count('ip_address');

            $this->line('👥 Usuarios únicos (por IP): ' . $usuariosUnicos);
            $this->line('');

            // Mostrar respuestas por pregunta
            $this->line('❓ RESPUESTAS POR PREGUNTA:');
            $this->line('');

            foreach ($encuesta->preguntas as $pregunta) {
                $respuestasPregunta = DB::table('respuestas_usuario')
                    ->where('encuesta_id', $encuestaId)
                    ->where('pregunta_id', $pregunta->id)
                    ->get();

                $this->line('   ' . $pregunta->id . '. ' . $pregunta->texto . ' (' . $pregunta->tipo . ')');
                $this->line('      - Respuestas recibidas: ' . $respuestasPregunta->count());

                if ($respuestasPregunta->count() > 0) {
                    $this->line('      - Detalles:');

                    foreach ($respuestasPregunta->take(5) as $respuesta) {
                        if ($respuesta->respuesta_id) {
                            // Respuesta de selección
                            $opcion = DB::table('respuestas')->where('id', $respuesta->respuesta_id)->first();
                            $texto = $opcion ? $opcion->texto : 'Opción no encontrada';
                            $this->line('         • Selección: ' . $texto);
                        } elseif ($respuesta->respuesta_texto) {
                            // Respuesta de texto
                            $texto = strlen($respuesta->respuesta_texto) > 50
                                ? substr($respuesta->respuesta_texto, 0, 50) . '...'
                                : $respuesta->respuesta_texto;
                            $this->line('         • Texto: "' . $texto . '"');
                        } else {
                            $this->line('         • Sin respuesta');
                        }
                    }

                    if ($respuestasPregunta->count() > 5) {
                        $this->line('         ... y ' . ($respuestasPregunta->count() - 5) . ' respuestas más');
                    }
                }
                $this->line('');
            }

            // Mostrar últimas respuestas
            $this->line('🕒 ÚLTIMAS RESPUESTAS RECIBIDAS:');
            $this->line('');

            $ultimasRespuestas = DB::table('respuestas_usuario')
                ->where('encuesta_id', $encuestaId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            foreach ($ultimasRespuestas as $respuesta) {
                $pregunta = $encuesta->preguntas->where('id', $respuesta->pregunta_id)->first();
                $preguntaTexto = $pregunta ? $pregunta->texto : 'Pregunta no encontrada';

                $this->line('   📅 ' . $respuesta->created_at);
                $this->line('      Pregunta: ' . $preguntaTexto);

                if ($respuesta->respuesta_id) {
                    $opcion = DB::table('respuestas')->where('id', $respuesta->respuesta_id)->first();
                    $texto = $opcion ? $opcion->texto : 'Opción no encontrada';
                    $this->line('      Respuesta: ' . $texto);
                } elseif ($respuesta->respuesta_texto) {
                    $texto = strlen($respuesta->respuesta_texto) > 100
                        ? substr($respuesta->respuesta_texto, 0, 100) . '...'
                        : $respuesta->respuesta_texto;
                    $this->line('      Respuesta: "' . $texto . '"');
                } else {
                    $this->line('      Respuesta: Sin respuesta');
                }

                $this->line('      IP: ' . $respuesta->ip_address);
                $this->line('');
            }

            $this->info('✅ Verificación completada exitosamente!');
            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error verificando respuestas: ' . $e->getMessage());
            return 1;
        }
    }
}
