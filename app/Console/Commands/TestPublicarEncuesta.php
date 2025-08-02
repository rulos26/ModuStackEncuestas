<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\TokenEncuesta;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TestPublicarEncuesta extends Command
{
    protected $signature = 'test:publicar-encuesta {encuesta_id}';
    protected $description = 'Probar la publicación de encuestas';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('=== PRUEBA DE PUBLICACIÓN DE ENCUESTA ===');
        $this->info("Encuesta ID: {$encuestaId}");
        $this->line('');

        try {
            // Verificar conexión a base de datos
            $this->info("🔍 Verificando conexión a base de datos...");

            try {
                $encuesta = Encuesta::findOrFail($encuestaId);
                $this->info("✅ Conexión exitosa");
                $this->info("✅ Encuesta encontrada: {$encuesta->titulo}");
            } catch (Exception $e) {
                $this->error("❌ Error de conexión: " . $e->getMessage());
                $this->info("💡 Verifica la configuración de base de datos en .env");
                return;
            }

            $this->line('');

            // Verificar estado actual
            $this->info("📊 Estado actual: {$encuesta->estado}");
            $this->info("📊 Envío por correo: " . ($encuesta->enviar_por_correo ? 'Sí' : 'No'));
            $this->info("📊 Encuesta pública: " . ($encuesta->encuesta_publica ? 'Sí' : 'No'));
            $this->line('');

            // Verificar preguntas
            $totalPreguntas = $encuesta->preguntas->count();
            $this->info("📝 Total de preguntas: {$totalPreguntas}");

            if ($totalPreguntas === 0) {
                $this->warn("⚠️ La encuesta no tiene preguntas");
            } else {
                $this->info("✅ La encuesta tiene preguntas");
            }

            // Simular publicación
            $this->info("🚀 Simulando publicación...");

            // Generar token de prueba
            $token = Str::random(64);
            $this->info("🔑 Token generado: {$token}");

            // Generar enlace
            $enlace = route('encuestas.publica', $encuesta->slug) . '?token=' . $token;
            $this->info("🔗 Enlace generado: {$enlace}");

            $this->line('');
            $this->info("📋 RESUMEN:");
            $this->info("  • Encuesta: {$encuesta->titulo}");
            $this->info("  • Estado: {$encuesta->estado}");
            $this->info("  • Preguntas: {$totalPreguntas}");
            $this->info("  • Token: {$token}");
            $this->info("  • Enlace: {$enlace}");

            $this->line('');
            $this->info("💡 Para publicar realmente, ejecuta:");
            $this->info("  php artisan encuesta:publicar-y-generar-enlace {$encuestaId}");

            $this->info('🎉 ¡PRUEBA COMPLETADA!');

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
        }
    }
}
