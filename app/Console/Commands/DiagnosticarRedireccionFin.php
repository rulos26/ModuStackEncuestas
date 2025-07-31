<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Models\Encuesta;
use Exception;

class DiagnosticarRedireccionFin extends Command
{
    protected $signature = 'diagnosticar:redireccion-fin {slug?}';
    protected $description = 'Diagnosticar la redirección a la vista de fin de encuesta';

    public function handle()
    {
        $this->info('🔍 DIAGNOSTICANDO REDIRECCIÓN A FIN DE ENCUESTA');
        $this->line('');

        try {
            // 1. Verificar rutas
            $this->line('🛣️  VERIFICANDO RUTAS...');
            $this->verificarRutas();
            $this->line('');

            // 2. Verificar encuestas disponibles
            $this->line('📋 VERIFICANDO ENCUESTAS DISPONIBLES...');
            $this->verificarEncuestas();
            $this->line('');

            // 3. Probar redirección
            $slug = $this->argument('slug');
            if ($slug) {
                $this->line("🧪 PROBANDO REDIRECCIÓN CON SLUG: {$slug}");
                $this->probarRedireccion($slug);
            } else {
                $this->line('💡 Para probar redirección específica: php artisan diagnosticar:redireccion-fin {slug}');
            }

            $this->line('');
            $this->info('✅ Diagnóstico completado');

        } catch (Exception $e) {
            $this->error('❌ Error en diagnóstico: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Verificar rutas disponibles
     */
    private function verificarRutas(): void
    {
        $routes = Route::getRoutes();
        $encuestaRoutes = [];

        foreach ($routes as $route) {
            if (str_contains($route->getName(), 'encuestas.')) {
                $encuestaRoutes[] = [
                    'name' => $route->getName(),
                    'uri' => $route->uri(),
                    'methods' => $route->methods()
                ];
            }
        }

        if (empty($encuestaRoutes)) {
            $this->warn('   ⚠️  No se encontraron rutas de encuestas');
            return;
        }

        $this->line('   ✅ Rutas de encuestas encontradas:');
        foreach ($encuestaRoutes as $route) {
            $methods = implode('|', $route['methods']);
            $this->line("      • {$methods} {$route['uri']} -> {$route['name']}");
        }

        // Verificar específicamente la ruta de fin
        $finRoute = collect($encuestaRoutes)->firstWhere('name', 'encuestas.fin');
        if ($finRoute) {
            $this->line('   ✅ Ruta encuestas.fin encontrada');
        } else {
            $this->error('   ❌ Ruta encuestas.fin NO encontrada');
        }
    }

    /**
     * Verificar encuestas disponibles
     */
    private function verificarEncuestas(): void
    {
        $encuestas = Encuesta::select('id', 'titulo', 'slug', 'estado', 'habilitada')
            ->get();

        if ($encuestas->isEmpty()) {
            $this->warn('   ⚠️  No hay encuestas en la base de datos');
            return;
        }

        $this->line("   📊 Total encuestas: {$encuestas->count()}");

        $publicadas = $encuestas->where('estado', 'publicada')->where('habilitada', true);
        $this->line("   ✅ Encuestas publicadas y habilitadas: {$publicadas->count()}");

        if ($publicadas->isNotEmpty()) {
            $this->line('   📋 Encuestas disponibles para fin:');
            foreach ($publicadas as $encuesta) {
                $this->line("      • {$encuesta->titulo} (slug: {$encuesta->slug})");
            }
        } else {
            $this->warn('   ⚠️  No hay encuestas publicadas y habilitadas');
        }

        // Mostrar todas las encuestas
        $this->line('   📋 Todas las encuestas:');
        foreach ($encuestas as $encuesta) {
            $status = $encuesta->habilitada ? '✅' : '❌';
            $this->line("      • {$status} {$encuesta->titulo} (estado: {$encuesta->estado}, slug: {$encuesta->slug})");
        }
    }

    /**
     * Probar redirección específica
     */
    private function probarRedireccion(string $slug): void
    {
        $this->line("   🔍 Buscando encuesta con slug: {$slug}");

        $encuesta = Encuesta::where('slug', $slug)->first();

        if (!$encuesta) {
            $this->error("   ❌ Encuesta con slug '{$slug}' no encontrada");
            return;
        }

        $this->line("   ✅ Encuesta encontrada: {$encuesta->titulo}");
        $this->line("   📊 Estado: {$encuesta->estado}, Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));

        if ($encuesta->estado !== 'publicada' || !$encuesta->habilitada) {
            $this->warn("   ⚠️  La encuesta no está publicada o habilitada");
            $this->line("   💡 Para que funcione la redirección, la encuesta debe estar:");
            $this->line("      • Estado: publicada");
            $this->line("      • Habilitada: true");
        } else {
            $this->line("   ✅ La encuesta cumple los requisitos para la redirección");

            // Simular la URL de redirección
            $url = route('encuestas.fin', $slug);
            $this->line("   🔗 URL de redirección: {$url}");

            // Verificar que la ruta existe
            try {
                $route = Route::getRoutes()->getByName('encuestas.fin');
                if ($route) {
                    $this->line("   ✅ Ruta encuestas.fin existe y es accesible");
                } else {
                    $this->error("   ❌ Ruta encuestas.fin no existe");
                }
            } catch (Exception $e) {
                $this->error("   ❌ Error verificando ruta: " . $e->getMessage());
            }
        }
    }
}
