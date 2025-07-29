<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Encuesta;
use App\Models\SentMail;
use App\Models\BloqueEnvio;
use Exception;

class ProbarDashboardSeguimiento extends Command
{
    protected $signature = 'dashboard:probar-seguimiento {encuesta_id} {--debug}';
    protected $description = 'Prueba el dashboard de seguimiento de una encuesta';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $debug = $this->option('debug');

        $this->info("🔍 PROBANDO DASHBOARD DE SEGUIMIENTO");
        $this->line('');

        try {
            $encuesta = Encuesta::with(['bloquesEnvio', 'preguntas'])->find($encuestaId);

            if (!$encuesta) {
                $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                return 1;
            }

            $this->info("📋 ENCUESTA: '{$encuesta->titulo}' (ID: {$encuestaId})");
            $this->line('');

            // 1. Verificar estado actual
            $this->verificarEstadoActual($encuesta);

            // 2. Verificar bloques de envío
            $this->verificarBloquesEnvio($encuesta);

            // 3. Verificar correos enviados
            $this->verificarCorreosEnviados($encuesta);

            // 4. Simular datos de prueba
            if ($debug) {
                $this->simularDatosPrueba($encuesta);
            }

            // 5. Mostrar estadísticas finales
            $this->mostrarEstadisticasFinales($encuesta);

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

    private function verificarEstadoActual($encuesta)
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

    private function verificarBloquesEnvio($encuesta)
    {
        $this->info("📦 BLOQUES DE ENVÍO:");

        $bloques = $encuesta->obtenerBloquesEnvio();

        if ($bloques->isEmpty()) {
            $this->warn("   ⚠️  No hay bloques de envío configurados");
            $this->line("   💡 Sugerencia: Ejecuta 'encuesta:forzar-validaciones {$encuesta->id}' para configurar");
        } else {
            $this->line("   Total bloques: {$bloques->count()}");

            $estados = $bloques->groupBy('estado');
            foreach ($estados as $estado => $bloquesEstado) {
                $this->line("   - {$estado}: {$bloquesEstado->count()} bloques");
            }
        }
        $this->line('');
    }

    private function verificarCorreosEnviados($encuesta)
    {
        $this->info("📧 CORREOS ENVIADOS:");

        $correosEnviados = SentMail::where('encuesta_id', $encuesta->id)->count();
        $correosError = SentMail::where('encuesta_id', $encuesta->id)
            ->where('status', 'error')
            ->count();

        $this->line("   Total correos: {$correosEnviados}");
        $this->line("   Correos con error: {$correosError}");

        if ($correosEnviados === 0) {
            $this->warn("   ⚠️  No hay correos enviados registrados");
            $this->line("   💡 Sugerencia: Verifica la configuración de envío");
        }
        $this->line('');
    }

    private function simularDatosPrueba($encuesta)
    {
        $this->info("🧪 SIMULANDO DATOS DE PRUEBA:");

        // Crear bloques de prueba si no existen
        if ($encuesta->obtenerBloquesEnvio()->isEmpty()) {
            $encuesta->crearBloquesEnvio(5);
            $this->line("   ✅ Bloques de envío creados");
        }

        // Simular algunos correos enviados
        $correosExistentes = SentMail::where('encuesta_id', $encuesta->id)->count();
        if ($correosExistentes === 0) {
            for ($i = 1; $i <= 5; $i++) {
                SentMail::create([
                    'encuesta_id' => $encuesta->id,
                    'to' => "usuario{$i}@ejemplo.com",
                    'subject' => "Encuesta: {$encuesta->titulo}",
                    'body' => "Contenido del correo de prueba",
                    'status' => 'sent',
                    'created_at' => now()->subMinutes($i * 2)
                ]);
            }
            $this->line("   ✅ 5 correos de prueba creados");
        }

        $this->line('');
    }

    private function mostrarEstadisticasFinales($encuesta)
    {
        $this->info("📈 ESTADÍSTICAS FINALES:");

        $bloques = $encuesta->obtenerBloquesEnvio();
        $correosEnviados = SentMail::where('encuesta_id', $encuesta->id)->count();

        $totalBloques = $bloques->count();
        $bloquesEnviados = $bloques->where('estado', 'enviado')->count();
        $progresoPorcentaje = $totalBloques > 0 ? round(($bloquesEnviados / $totalBloques) * 100, 2) : 0;

        $this->line("   Total bloques: {$totalBloques}");
        $this->line("   Bloques enviados: {$bloquesEnviados}");
        $this->line("   Progreso: {$progresoPorcentaje}%");
        $this->line("   Correos enviados: {$correosEnviados}");
        $this->line('');

        if ($progresoPorcentaje > 0) {
            $this->info("🎉 ¡El dashboard está funcionando correctamente!");
        } else {
            $this->warn("⚠️  El dashboard no muestra progreso. Verifica la configuración.");
        }
    }
}
