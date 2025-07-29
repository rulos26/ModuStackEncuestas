<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Encuesta;
use Exception;

class ForzarValidacionesEncuesta extends Command
{
    protected $signature = 'encuesta:forzar-validaciones {encuesta_id} {--debug}';
    protected $description = 'Fuerza las validaciones de una encuesta para desarrollo';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $debug = $this->option('debug');

        $this->info("🔧 FORZANDO VALIDACIONES DE ENCUESTA");
        $this->line('');

        try {
            $encuesta = Encuesta::find($encuestaId);

            if (!$encuesta) {
                $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                return 1;
            }

            $this->info("📋 ENCUESTA: '{$encuesta->titulo}' (ID: {$encuestaId})");
            $this->line('');

            // 1. Mostrar estado actual
            $this->mostrarEstadoActual($encuesta);

            // 2. Forzar validaciones
            $this->forzarValidaciones($encuesta);

            // 3. Mostrar estado después
            $this->mostrarEstadoDespues($encuesta);

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante el proceso: " . $e->getMessage());

            if ($debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }

    private function mostrarEstadoActual($encuesta)
    {
        $this->info("📊 ESTADO ACTUAL:");
        $this->line("   Estado: {$encuesta->estado}");
        $this->line("   Enviar por correo: " . ($encuesta->enviar_por_correo ? 'Sí' : 'No'));
        $this->line("   Envío masivo activado: " . ($encuesta->envio_masivo_activado ? 'Sí' : 'No'));
        $this->line("   Validación completada: " . ($encuesta->validacion_completada ? 'Sí' : 'No'));
        $this->line("   Total preguntas: {$encuesta->preguntas()->count()}");
        $this->line("   ¿Puede enviarse masivamente?: " . ($encuesta->puedeEnviarseMasivamente() ? 'Sí' : 'No'));
        $this->line('');
    }

    private function forzarValidaciones($encuesta)
    {
        $this->info("🔧 FORZANDO VALIDACIONES:");

        // Forzar valores para desarrollo
        $encuesta->enviar_por_correo = true;
        $encuesta->envio_masivo_activado = true;
        $encuesta->validacion_completada = true;
        $encuesta->estado = 'borrador';

        // Guardar cambios
        $encuesta->save();

        $this->line("   ✅ enviar_por_correo = true");
        $this->line("   ✅ envio_masivo_activado = true");
        $this->line("   ✅ validacion_completada = true");
        $this->line("   ✅ estado = 'borrador'");
        $this->line('');
    }

    private function mostrarEstadoDespues($encuesta)
    {
        $this->info("📊 ESTADO DESPUÉS:");
        $this->line("   Estado: {$encuesta->estado}");
        $this->line("   Enviar por correo: " . ($encuesta->enviar_por_correo ? 'Sí' : 'No'));
        $this->line("   Envío masivo activado: " . ($encuesta->envio_masivo_activado ? 'Sí' : 'No'));
        $this->line("   Validación completada: " . ($encuesta->validacion_completada ? 'Sí' : 'No'));
        $this->line("   ¿Puede enviarse masivamente?: " . ($encuesta->puedeEnviarseMasivamente() ? 'Sí' : 'No'));
        $this->line('');

        if ($encuesta->puedeEnviarseMasivamente()) {
            $this->info("🎉 ¡La encuesta ahora está lista para envío masivo!");
        } else {
            $this->warn("⚠️  La encuesta aún no está lista. Verificar condiciones adicionales.");
        }
    }
}
