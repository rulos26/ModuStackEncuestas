<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Exception;

class VerificarRutasRespuestas extends Command
{
    protected $signature = 'verificar:rutas-respuestas';
    protected $description = 'Verificar que las rutas de respuestas estén configuradas correctamente';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO RUTAS DE RESPUESTAS');
        $this->line('');

        try {
            // Obtener todas las rutas
            $routes = Route::getRoutes();

            $this->line('📋 Rutas relacionadas con respuestas:');
            $this->line('');

            $encontradas = false;

            foreach ($routes as $route) {
                $uri = $route->uri();
                $methods = $route->methods();
                $name = $route->getName();

                if (strpos($uri, 'respuestas') !== false ||
                    strpos($name ?? '', 'respuestas') !== false) {

                    $encontradas = true;
                    $method = implode('|', array_filter($methods, function($m) {
                        return $m !== 'HEAD';
                    }));

                    $this->line("   • {$method} {$uri}");
                    if ($name) {
                        $this->line("     Nombre: {$name}");
                    }
                    $this->line('');
                }
            }

            if (!$encontradas) {
                $this->warn('⚠️  No se encontraron rutas relacionadas con respuestas');
            }

            // Verificar rutas específicas
            $this->line('🔍 Verificando rutas específicas:');

            $rutasEspecificas = [
                'encuestas.respuestas.obtener' => 'GET encuestas/{pregunta}/respuestas/obtener',
                'encuestas.respuestas.editar' => 'POST encuestas/{pregunta}/respuestas/editar'
            ];

            foreach ($rutasEspecificas as $nombre => $descripcion) {
                if (Route::has($nombre)) {
                    $this->line("   ✅ {$nombre} - {$descripcion}");
                } else {
                    $this->error("   ❌ {$nombre} - NO ENCONTRADA");
                }
            }

            $this->line('');
            $this->info('✅ Verificación completada');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la verificación: ' . $e->getMessage());
            return 1;
        }
    }
}
