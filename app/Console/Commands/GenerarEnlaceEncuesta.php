<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerarEnlaceEncuesta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encuesta:generar-enlace {id : ID de la encuesta}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera el enlace público de una encuesta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar si STDIN está disponible (para entornos web)
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }

        $encuestaId = $this->argument('id');

        $this->info('🔗 GENERANDO ENLACE PÚBLICO DE ENCUESTA');
        $this->line('');

        try {
            // Verificar conexión a la base de datos
            $this->line('🔍 Verificando conexión a la base de datos...');
            try {
                DB::connection()->getPdo();
                $this->line('   ✅ Conexión a la base de datos establecida');
            } catch (\Exception $e) {
                $this->warn('   ⚠️  No se pudo conectar a la base de datos: ' . $e->getMessage());
                $this->line('   📝 Generando enlace manual...');
            }

            // Buscar la encuesta
            $encuesta = null;
            if (class_exists('\App\Models\Encuesta')) {
                try {
                    $encuesta = \App\Models\Encuesta::find($encuestaId);
                } catch (\Exception $e) {
                    $this->warn('   ⚠️  Error al buscar encuesta en BD: ' . $e->getMessage());
                }
            }

            if ($encuesta) {
                $this->line('   ✅ Encuesta encontrada: ' . $encuesta->titulo);
            } else {
                $this->warn('   ⚠️  Encuesta no encontrada en BD, generando enlace manual');
            }

            $this->line('');

            // Generar enlaces
            $this->line('🔗 ENLACES GENERADOS:');
            $this->line('');

            // Enlace usando route() (requiere BD)
            if ($encuesta) {
                try {
                    $enlaceRoute = route('encuestas.publica', ['token' => 'TOKEN_PLACEHOLDER']);
                    $this->line('📱 Enlace con route():');
                    $this->line('   ' . $enlaceRoute);
                    $this->line('');
                } catch (\Exception $e) {
                    $this->warn('   ⚠️  Error generando enlace con route(): ' . $e->getMessage());
                }
            }

            // Enlace manual (no requiere BD)
            $baseUrl = config('app.url', 'https://rulossoluciones.com/modustackencuestas');
            $enlaceManual = $baseUrl . '/publica/encuesta/' . $encuestaId;

            $this->line('🌐 Enlace manual (recomendado):');
            $this->line('   ' . $enlaceManual);
            $this->line('');

            // Enlace de pruebas
            $enlacePruebas = $baseUrl . '/testing/encuesta-publica/vista/' . $encuestaId;
            $this->line('🧪 Enlace de pruebas:');
            $this->line('   ' . $enlacePruebas);
            $this->line('');

            $this->info('✅ Enlaces generados exitosamente');
            $this->line('');
            $this->line('💡 RECOMENDACIONES:');
            $this->line('   • Usa el enlace manual para acceso directo');
            $this->line('   • Usa el enlace de pruebas para verificar funcionamiento');
            $this->line('   • Verifica que la encuesta esté publicada y habilitada');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error generando enlace: ' . $e->getMessage());
            return 1;
        }
    }
}
