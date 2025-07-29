<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use Exception;

class DiagnosticarEstadoEncuesta extends Command
{
    protected $signature = 'encuesta:diagnosticar-estado {encuesta_id} {--debug}';
    protected $description = 'Diagnostica el estado de una encuesta y por qué no puede enviarse masivamente';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🔍 DIAGNÓSTICO DE ESTADO DE ENCUESTA');
        $this->info('====================================');
        $this->info("📋 Encuesta ID: {$encuestaId}");

        try {
            $encuesta = Encuesta::with(['preguntas', 'preguntas.respuestas'])->find($encuestaId);

            if (!$encuesta) {
                $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                return 1;
            }

            $this->mostrarInformacionBasica($encuesta);
            $this->verificarCondicionesEnvioMasivo($encuesta);
            $this->verificarPreguntasYRespuestas($encuesta);
            $this->verificarConfiguracionCompleta($encuesta);
            $this->mostrarRecomendaciones($encuesta);

        } catch (\Exception $e) {
            $this->error('❌ Error durante el diagnóstico: ' . $e->getMessage());
            if ($this->option('debug')) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }
            return 1;
        }

        return 0;
    }

    private function mostrarInformacionBasica($encuesta)
    {
        $this->info("\n📊 INFORMACIÓN BÁSICA:");
        $this->info("   📝 Título: {$encuesta->titulo}");
        $this->info("   🏢 Empresa: " . ($encuesta->empresa ? $encuesta->empresa->nombre : 'No asignada'));
        $this->info("   👤 Propietario: " . ($encuesta->user ? $encuesta->user->name : 'No asignado'));
        $this->info("   📅 Estado: {$encuesta->estado}");
        $this->info("   ✅ Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));
        $this->info("   📧 Enviar por correo: " . ($encuesta->enviar_por_correo ? 'Sí' : 'No'));
        $this->info("   🚀 Envío masivo activado: " . ($encuesta->envio_masivo_activado ? 'Sí' : 'No'));
        $this->info("   ✅ Validación completada: " . ($encuesta->validacion_completada ? 'Sí' : 'No'));
        $this->info("   📊 Número de encuestas: {$encuesta->numero_encuestas}");
    }

    private function verificarCondicionesEnvioMasivo($encuesta)
    {
        $this->info("\n🔍 VERIFICANDO CONDICIONES PARA ENVÍO MASIVO:");

        $condiciones = [
            'enviar_por_correo' => $encuesta->enviar_por_correo,
            'envio_masivo_activado' => $encuesta->envio_masivo_activado,
            'estado_borrador' => $encuesta->estado === 'borrador',
            'validacion_completada' => $encuesta->validacion_completada
        ];

        $todasCumplidas = true;
        foreach ($condiciones as $condicion => $cumplida) {
            $icono = $cumplida ? '✅' : '❌';
            $estado = $cumplida ? 'CUMPLIDA' : 'NO CUMPLIDA';
            $this->info("   {$icono} {$condicion}: {$estado}");

            if (!$cumplida) {
                $todasCumplidas = false;
            }
        }

        if ($todasCumplidas) {
            $this->info("   🎉 ¡Todas las condiciones están cumplidas!");
        } else {
            $this->error("   ⚠️  Faltan condiciones para envío masivo");
        }

        // Verificar método puedeEnviarseMasivamente
        $puedeEnviarse = $encuesta->puedeEnviarseMasivamente();
        $this->info("   🔍 Resultado de puedeEnviarseMasivamente(): " . ($puedeEnviarse ? 'true' : 'false'));
    }

    private function verificarPreguntasYRespuestas($encuesta)
    {
        $this->info("\n❓ VERIFICANDO PREGUNTAS Y RESPUESTAS:");

        $preguntas = $encuesta->preguntas;
        $totalPreguntas = $preguntas->count();

        $this->info("   📝 Total de preguntas: {$totalPreguntas}");

        if ($totalPreguntas == 0) {
            $this->error("   ❌ No hay preguntas en la encuesta");
            return;
        }

        $preguntasConRespuestas = 0;
        $preguntasSinRespuestas = 0;

        foreach ($preguntas as $pregunta) {
            $tieneRespuestas = $pregunta->respuestas->count() > 0;
            $necesitaRespuestas = $pregunta->necesitaRespuestas();

            if ($necesitaRespuestas) {
                if ($tieneRespuestas) {
                    $preguntasConRespuestas++;
                    $this->info("   ✅ Pregunta {$pregunta->orden}: '{$pregunta->texto}' - Tiene respuestas");
                } else {
                    $preguntasSinRespuestas++;
                    $this->error("   ❌ Pregunta {$pregunta->orden}: '{$pregunta->texto}' - SIN RESPUESTAS");
                }
            } else {
                $this->info("   ℹ️  Pregunta {$pregunta->orden}: '{$pregunta->texto}' - No necesita respuestas");
            }
        }

        $this->info("   📊 Resumen:");
        $this->info("      - Preguntas con respuestas: {$preguntasConRespuestas}");
        $this->info("      - Preguntas sin respuestas: {$preguntasSinRespuestas}");

        if ($preguntasSinRespuestas > 0) {
            $this->error("   ⚠️  Hay preguntas que necesitan respuestas");
        }
    }

    private function verificarConfiguracionCompleta($encuesta)
    {
        $this->info("\n⚙️ VERIFICANDO CONFIGURACIÓN COMPLETA:");

        // Verificar método estaCompletamenteConfigurada
        if (method_exists($encuesta, 'estaCompletamenteConfigurada')) {
            $completamenteConfigurada = $encuesta->estaCompletamenteConfigurada();
            $this->info("   🔍 estaCompletamenteConfigurada(): " . ($completamenteConfigurada ? 'true' : 'false'));
        }

        // Verificar método obtenerProgresoConfiguracion
        if (method_exists($encuesta, 'obtenerProgresoConfiguracion')) {
            $progreso = $encuesta->obtenerProgresoConfiguracion();
            $this->info("   📊 Progreso de configuración:");
            foreach ($progreso as $paso => $completado) {
                $icono = $completado ? '✅' : '❌';
                $this->info("      {$icono} {$paso}");
            }
        }

        // Verificar método validarIntegridad
        if (method_exists($encuesta, 'validarIntegridad')) {
            $errores = $encuesta->validarIntegridad();
            if (empty($errores)) {
                $this->info("   ✅ Validación de integridad: Sin errores");
            } else {
                $this->error("   ❌ Errores de integridad:");
                foreach ($errores as $error) {
                    $this->error("      - {$error}");
                }
            }
        }
    }

    private function mostrarRecomendaciones($encuesta)
    {
        $this->info("\n💡 RECOMENDACIONES:");

        // Verificar cada condición
        if (!$encuesta->enviar_por_correo) {
            $this->info("   📧 Para habilitar envío por correo:");
            $this->info("      - Ve a la configuración de la encuesta");
            $this->info("      - Marca 'Enviar por correo'");
        }

        if (!$encuesta->envio_masivo_activado) {
            $this->info("   🚀 Para activar envío masivo:");
            $this->info("      - Ve a la configuración de la encuesta");
            $this->info("      - Marca 'Envío masivo activado'");
        }

        if ($encuesta->estado !== 'borrador') {
            $this->info("   📝 Para cambiar estado a borrador:");
            $this->info("      - Ve a la configuración de la encuesta");
            $this->info("      - Cambia estado a 'borrador'");
        }

        if (!$encuesta->validacion_completada) {
            $this->info("   ✅ Para completar validación:");
            $this->info("      - Asegúrate de tener preguntas");
            $this->info("      - Asegúrate de tener respuestas donde sea necesario");
            $this->info("      - Ejecuta validación automática");
        }

        $this->info("\n🔧 COMANDOS ÚTILES:");
        $this->info("   - Para verificar otra encuesta:");
        $this->info("     php artisan encuesta:diagnosticar-estado {otro_id}");
        $this->info("");
        $this->info("   - Para verificar desde el módulo visual:");
        $this->info("     Diagnósticos → Herramientas del Sistema → Pruebas del Sistema");
        $this->info("     Selecciona: 'Diagnosticar Estado de Encuesta'");
    }
}
