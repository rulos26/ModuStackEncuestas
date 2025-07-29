<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Exception;

class ProbarConfiguracionEnvio extends Command
{
    protected $signature = 'envio:probar-configuracion {encuesta_id} {--debug}';
    protected $description = 'Prueba la configuración de envío de una encuesta específica';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $debug = $this->option('debug');

        $this->info("🔍 PROBANDO CONFIGURACIÓN DE ENVÍO PARA ENCUESTA ID: {$encuestaId}");
        $this->line('');

        try {
            // 1. Verificar que la encuesta existe
            $encuesta = Encuesta::find($encuestaId);
            if (!$encuesta) {
                $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                return 1;
            }

            $this->info("✅ Encuesta encontrada: '{$encuesta->titulo}'");
            $this->line('');

            // 2. Verificar estado actual
            $this->info("📊 ESTADO ACTUAL:");
            $this->line("   Estado: {$encuesta->estado}");
            $this->line("   Envío por correo: " . ($encuesta->enviar_por_correo ? 'Sí' : 'No'));
            $this->line("   Envío masivo activado: " . ($encuesta->envio_masivo_activado ? 'Sí' : 'No'));
            $this->line("   Validación completada: " . ($encuesta->validacion_completada ? 'Sí' : 'No'));
            $this->line('');

            // 3. Verificar preguntas
            $totalPreguntas = $encuesta->preguntas()->count();
            $this->info("❓ PREGUNTAS:");
            $this->line("   Total de preguntas: {$totalPreguntas}");

            if ($totalPreguntas > 0) {
                $preguntasConRespuestas = $encuesta->preguntas()->necesitaRespuestas()->whereHas('respuestas')->count();
                $totalPreguntasNecesitanRespuestas = $encuesta->preguntas()->necesitaRespuestas()->count();
                $this->line("   Preguntas que necesitan respuestas: {$totalPreguntasNecesitanRespuestas}");
                $this->line("   Preguntas con respuestas configuradas: {$preguntasConRespuestas}");
            }
            $this->line('');

            // 4. Verificar si puede avanzar a envío
            $this->info("🚀 VERIFICACIÓN DE ENVÍO:");
            $puedeAvanzar = $encuesta->puedeAvanzarA('envio');
            $this->line("   ¿Puede avanzar a envío?: " . ($puedeAvanzar ? 'Sí' : 'No'));

            if (!$puedeAvanzar) {
                $this->warn("   ⚠️  No puede avanzar a envío. Verificando condiciones...");

                // Verificar condiciones específicas
                $tienePreguntas = $encuesta->preguntas()->count() > 0;
                $estadoCorrecto = $encuesta->estado === 'borrador';

                $this->line("   Tiene preguntas: " . ($tienePreguntas ? 'Sí' : 'No'));
                $this->line("   Estado correcto (borrador): " . ($estadoCorrecto ? 'Sí' : 'No'));
            }
            $this->line('');

            // 5. Verificar si puede enviarse masivamente
            $this->info("📧 VERIFICACIÓN DE ENVÍO MASIVO:");
            $puedeEnviarseMasivamente = $encuesta->puedeEnviarseMasivamente();
            $this->line("   ¿Puede enviarse masivamente?: " . ($puedeEnviarseMasivamente ? 'Sí' : 'No'));

            if (!$puedeEnviarseMasivamente) {
                $this->warn("   ⚠️  No puede enviarse masivamente. Verificando condiciones...");

                $condiciones = [
                    'enviar_por_correo' => $encuesta->enviar_por_correo,
                    'envio_masivo_activado' => $encuesta->envio_masivo_activado,
                    'estado_borrador' => $encuesta->estado === 'borrador',
                    'validacion_completada' => $encuesta->validacion_completada
                ];

                foreach ($condiciones as $condicion => $cumplida) {
                    $this->line("   {$condicion}: " . ($cumplida ? 'Sí' : 'No'));
                }
            }
            $this->line('');

            // 6. Progreso de configuración
            $this->info("📋 PROGRESO DE CONFIGURACIÓN:");
            $progreso = $encuesta->obtenerProgresoConfiguracion();
            $this->line("   Porcentaje completado: {$progreso['porcentaje']}%");
            $this->line("   Pasos completados: {$progreso['completados']}/{$progreso['total']}");

            if ($progreso['siguiente_paso']) {
                $this->line("   Siguiente paso: {$progreso['siguiente_paso']}");
            } else {
                $this->line("   ✅ Todos los pasos completados");
            }
            $this->line('');

            // 7. Recomendaciones
            $this->info("💡 RECOMENDACIONES:");

            if (!$puedeAvanzar) {
                if (!$tienePreguntas) {
                    $this->line("   ➡️  Agregar preguntas a la encuesta");
                }
                if (!$estadoCorrecto) {
                    $this->line("   ➡️  La encuesta debe estar en estado 'borrador'");
                }
            }

            if (!$puedeEnviarseMasivamente) {
                $this->line("   ➡️  Configurar envío por correo");
                $this->line("   ➡️  Activar envío masivo");
                $this->line("   ➡️  Completar validación");
            }

            if ($puedeAvanzar && $puedeEnviarseMasivamente) {
                $this->info("   ✅ La encuesta está lista para configurar envío");
            }
            $this->line('');

            // 8. Debug mode - información adicional
            if ($debug) {
                $this->info("🔧 INFORMACIÓN DE DEBUG:");
                $this->line("   ID: {$encuesta->id}");
                $this->line("   Título: {$encuesta->titulo}");
                $this->line("   Empresa ID: {$encuesta->empresa_id}");
                $this->line("   Usuario ID: {$encuesta->user_id}");
                $this->line("   Creada: {$encuesta->created_at}");
                $this->line("   Actualizada: {$encuesta->updated_at}");
                $this->line('');
            }

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante la prueba: " . $e->getMessage());

            if ($debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }
}
