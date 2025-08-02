<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\TokenEncuesta;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PublicarEncuestaCommand extends Command
{
    protected $signature = 'encuesta:publicar-y-generar-enlace {encuesta_id}';
    protected $description = 'Publicar una encuesta y generar enlace de acceso';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('=== PUBLICAR ENCUESTA Y GENERAR ENLACE ===');
        $this->info("Encuesta ID: {$encuestaId}");
        $this->line('');

        try {
            $encuesta = Encuesta::findOrFail($encuestaId);
            $this->info("✅ Encuesta encontrada: {$encuesta->titulo}");
            $this->line('');

            // Verificar estado actual
            $this->info("📊 Estado actual: {$encuesta->estado}");
            $this->info("📊 Envío por correo: " . ($encuesta->enviar_por_correo ? 'Sí' : 'No'));
            $this->info("📊 Encuesta pública: " . ($encuesta->encuesta_publica ? 'Sí' : 'No'));
            $this->line('');

            // Verificar que la encuesta esté lista para publicar
            if ($encuesta->estado !== 'borrador') {
                $this->warn("⚠️ La encuesta ya no está en estado borrador");
                $this->info("Estado actual: {$encuesta->estado}");

                if ($this->confirm('¿Deseas continuar de todas formas?')) {
                    $this->info("Continuando con la publicación...");
                } else {
                    $this->info("Operación cancelada");
                    return;
                }
            }

            // Verificar que tenga preguntas
            $totalPreguntas = $encuesta->preguntas->count();
            $this->info("📝 Total de preguntas: {$totalPreguntas}");

            if ($totalPreguntas === 0) {
                $this->error("❌ La encuesta no tiene preguntas. No se puede publicar.");
                return;
            }

            // Verificar que las preguntas tengan respuestas
            $preguntasSinRespuestas = $encuesta->preguntas->filter(function ($pregunta) {
                return $pregunta->respuestas->count() === 0;
            });

            if ($preguntasSinRespuestas->count() > 0) {
                $this->warn("⚠️ Hay preguntas sin respuestas:");
                foreach ($preguntasSinRespuestas as $pregunta) {
                    $this->warn("  - {$pregunta->texto}");
                }

                if (!$this->confirm('¿Deseas continuar de todas formas?')) {
                    $this->info("Operación cancelada");
                    return;
                }
            }

            // Publicar la encuesta
            $this->info("🚀 Publicando encuesta...");

            $encuesta->update([
                'estado' => 'publicada',
                'fecha_publicacion' => now()
            ]);

            $this->info("✅ Encuesta publicada exitosamente");
            $this->line('');

            // Generar enlace de acceso
            $this->info("🔗 Generando enlace de acceso...");

            $token = $this->generarTokenAcceso($encuesta);
            $enlace = route('encuestas.publica', $encuesta->slug) . '?token=' . $token;

            $this->info("✅ Enlace generado exitosamente");
            $this->line('');

            // Mostrar información del enlace
            $this->info("📋 INFORMACIÓN DEL ENLACE:");
            $this->info("  Enlace completo: {$enlace}");
            $this->info("  Token: {$token}");
            $this->info("  Slug: {$encuesta->slug}");
            $this->info("  Fecha de expiración: " . now()->addDays(7)->format('d/m/Y H:i'));
            $this->line('');

            // Verificar configuración de envío
            if ($encuesta->enviar_por_correo) {
                $this->info("📧 CONFIGURACIÓN DE ENVÍO:");
                $this->info("  Envío por correo: Habilitado");
                $this->info("  Número de encuestas: {$encuesta->numero_encuestas}");
                $this->info("  Fecha inicio: " . ($encuesta->fecha_inicio ? $encuesta->fecha_inicio->format('d/m/Y H:i') : 'No definida'));
                $this->info("  Fecha fin: " . ($encuesta->fecha_fin ? $encuesta->fecha_fin->format('d/m/Y H:i') : 'No definida'));
                $this->line('');

                $this->info("💡 Para enviar correos, ve al dashboard de seguimiento:");
                $this->info("  " . route('encuestas.seguimiento.dashboard', $encuesta->id));
            } else {
                $this->info("📧 Envío por correo: Deshabilitado");
                $this->info("💡 La encuesta está disponible públicamente en el enlace generado");
            }

            // Log del evento
            Log::info('Encuesta publicada y enlace generado', [
                'encuesta_id' => $encuesta->id,
                'titulo' => $encuesta->titulo,
                'token' => $token,
                'enlace' => $enlace,
                'usuario' => auth()->id() ?? 'CLI'
            ]);

            $this->info('🎉 ¡PUBLICACIÓN COMPLETADA!');
            $this->info('La encuesta está ahora disponible públicamente');

        } catch (Exception $e) {
            $this->error('❌ Error en la publicación: ' . $e->getMessage());
            Log::error('Error publicando encuesta', [
                'encuesta_id' => $encuestaId,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function generarTokenAcceso(Encuesta $encuesta): string
    {
        $token = Str::random(64);

        TokenEncuesta::create([
            'encuesta_id' => $encuesta->id,
            'email_destinatario' => 'publico@encuesta.com', // Token público
            'token_acceso' => $token,
            'fecha_expiracion' => now()->addDays(7),
            'usado' => false,
        ]);

        return $token;
    }
}
