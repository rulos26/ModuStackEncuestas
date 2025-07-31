<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Exception;

class DiagnosticarErrorEncuestaPublica extends Command
{
    protected $signature = 'encuesta:diagnosticar-error-publica {encuesta_id?}';
    protected $description = 'Diagnosticar errores en encuestas públicas y verificar logs';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🔍 DIAGNOSTICANDO ERROR EN ENCUESTA PÚBLICA');
        $this->line('');

        try {
            // 1. Verificar logs de Laravel
            $this->line('📋 REVISANDO LOGS DE LARAVEL...');
            $logPath = storage_path('logs/laravel.log');

            if (File::exists($logPath)) {
                $logContent = File::get($logPath);
                $lines = explode("\n", $logContent);
                $recentLines = array_slice($lines, -50); // Últimas 50 líneas

                $this->line('   Últimas líneas del log:');
                foreach ($recentLines as $line) {
                    if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
                        $this->error('   ' . $line);
                    }
                }
            } else {
                $this->warn('   ⚠️  Archivo de log no encontrado');
            }
            $this->line('');

            // 2. Verificar encuesta específica si se proporciona
            if ($encuestaId) {
                $this->line('📝 VERIFICANDO ENCUESTA ESPECÍFICA...');
                $encuesta = Encuesta::with(['preguntas.respuestas'])->find($encuestaId);

                if (!$encuesta) {
                    $this->error('   ❌ Encuesta no encontrada con ID: ' . $encuestaId);
                } else {
                    $this->line('   ✅ Encuesta encontrada: ' . $encuesta->titulo);
                    $this->line('   - ID: ' . $encuesta->id);
                    $this->line('   - Estado: ' . $encuesta->estado);
                    $this->line('   - Habilitada: ' . ($encuesta->habilitada ? 'Sí' : 'No'));
                    $this->line('   - Preguntas: ' . $encuesta->preguntas->count());

                    // Verificar preguntas obligatorias
                    $obligatorias = $encuesta->preguntas()->where('obligatoria', true)->get();
                    $this->line('   - Preguntas obligatorias: ' . $obligatorias->count());

                    foreach ($obligatorias as $pregunta) {
                        $this->line('     • ID ' . $pregunta->id . ': ' . $pregunta->texto . ' (' . $pregunta->tipo . ')');
                    }
                }
                $this->line('');
            }

            // 3. Verificar configuración de base de datos
            $this->line('🗄️  VERIFICANDO CONFIGURACIÓN DE BD...');
            try {
                $encuestas = Encuesta::where('estado', 'publicada')->count();
                $this->line('   ✅ Conexión a BD: OK');
                $this->line('   - Encuestas publicadas: ' . $encuestas);

                $preguntas = \App\Models\Pregunta::count();
                $this->line('   - Total preguntas: ' . $preguntas);

                $respuestas = \App\Models\Respuesta::count();
                $this->line('   - Total respuestas: ' . $respuestas);

            } catch (Exception $e) {
                $this->error('   ❌ Error de BD: ' . $e->getMessage());
            }
            $this->line('');

            // 4. Verificar middleware y rutas
            $this->line('🛣️  VERIFICANDO RUTAS Y MIDDLEWARE...');
            try {
                $routes = app('router')->getRoutes();
                $publicaRoutes = collect($routes)->filter(function($route) {
                    return strpos($route->uri, 'publica') !== false;
                });

                $this->line('   ✅ Rutas públicas encontradas: ' . $publicaRoutes->count());
                foreach ($publicaRoutes as $route) {
                    $this->line('     • ' . $route->methods[0] . ' ' . $route->uri);
                }

            } catch (Exception $e) {
                $this->error('   ❌ Error verificando rutas: ' . $e->getMessage());
            }
            $this->line('');

            // 5. Verificar permisos de archivos
            $this->line('📁 VERIFICANDO PERMISOS DE ARCHIVOS...');
            $paths = [
                storage_path('logs'),
                storage_path('framework/sessions'),
                storage_path('framework/cache'),
                storage_path('framework/views')
            ];

            foreach ($paths as $path) {
                if (File::exists($path)) {
                    $writable = is_writable($path);
                    $status = $writable ? '✅' : '❌';
                    $this->line("   {$status} {$path}");
                } else {
                    $this->warn("   ⚠️  No existe: {$path}");
                }
            }
            $this->line('');

            // 6. Recomendaciones
            $this->line('💡 RECOMENDACIONES:');
            $this->line('   1. Verificar que la encuesta esté en estado "publicada"');
            $this->line('   2. Verificar que la encuesta esté habilitada');
            $this->line('   3. Verificar que las preguntas obligatorias tengan respuestas válidas');
            $this->line('   4. Revisar los logs de error específicos');
            $this->line('   5. Verificar permisos de archivos y directorios');
            $this->line('');

            $this->info('✅ Diagnóstico completado');
            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error durante el diagnóstico: ' . $e->getMessage());
            return 1;
        }
    }
}
