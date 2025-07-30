<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use Illuminate\Console\Command;
use Exception;

class PublicarEncuesta extends Command
{
    protected $signature = 'encuesta:publicar {encuesta_id} {--email=test@example.com} {--horas=24}';
    protected $description = 'Publicar una encuesta y generar enlace de acceso';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $email = $this->option('email');
        $horas = $this->option('horas');

        $this->info('🌐 PUBLICANDO ENCUESTA');
        $this->line('');

        try {
            // Buscar encuesta
            $encuesta = Encuesta::with(['preguntas', 'empresa'])->find($encuestaId);

            if (!$encuesta) {
                $this->error('❌ Encuesta no encontrada con ID: ' . $encuestaId);
                return 1;
            }

            $this->line('📝 Encuesta encontrada:');
            $this->line('   - ID: ' . $encuesta->id);
            $this->line('   - Título: ' . $encuesta->titulo);
            $this->line('   - Estado actual: ' . $encuesta->estado);
            $this->line('   - Preguntas: ' . $encuesta->preguntas->count());
            $this->line('');

            // Verificar que tenga preguntas
            if ($encuesta->preguntas->count() == 0) {
                $this->error('❌ La encuesta no tiene preguntas. Agrega preguntas antes de publicar.');
                return 1;
            }

            // Publicar encuesta
            $this->line('🔓 Publicando encuesta...');
            $encuesta->update([
                'estado' => 'publicada',
                'habilitada' => true
            ]);

            $this->line('   ✅ Estado actualizado a: publicada');
            $this->line('   ✅ Habilitada: sí');
            $this->line('');

            // Generar token de acceso
            $this->line('🔑 Generando token de acceso...');
            $token = $encuesta->generarTokenParaDestinatario($email, $horas);
            $enlace = $token->obtenerEnlace();

            $this->line('   ✅ Token generado exitosamente');
            $this->line('   📧 Email: ' . $email);
            $this->line('   ⏰ Validez: ' . $horas . ' horas');
            $this->line('');

            // Mostrar enlace
            $this->line('🔗 ENLACE DE ACCESO:');
            $this->line('   ' . $enlace);
            $this->line('');

            // Información adicional
            $this->line('📊 INFORMACIÓN ADICIONAL:');
            $this->line('   - Slug: ' . $encuesta->slug);
            $this->line('   - Fecha inicio: ' . ($encuesta->fecha_inicio ? $encuesta->fecha_inicio->format('d/m/Y') : 'No definida'));
            $this->line('   - Fecha fin: ' . ($encuesta->fecha_fin ? $encuesta->fecha_fin->format('d/m/Y') : 'No definida'));
            $this->line('   - Empresa: ' . ($encuesta->empresa ? $encuesta->empresa->nombre : 'No asignada'));
            $this->line('');

            $this->info('🎉 ¡Encuesta publicada exitosamente!');
            $this->line('📱 El enlace está listo para ser compartido con los participantes.');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error publicando encuesta: ' . $e->getMessage());
            return 1;
        }
    }
}
