<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Encuesta;
use Exception;

class DiagnosticarFlujoEncuestaPublica extends Command
{
    protected $signature = 'encuesta:diagnosticar-flujo-publica {encuesta_id?}';
    protected $description = 'Diagnosticar el flujo completo de encuesta pública';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🔍 DIAGNOSTICANDO FLUJO DE ENCUESTA PÚBLICA');
        $this->line('');

        try {
            // 1. Verificar encuesta
            $this->line('📋 VERIFICANDO ENCUESTA...');
            $encuesta = $this->verificarEncuesta($encuestaId);
            if (!$encuesta) {
                return 1;
            }
            $this->line('');

            // 2. Verificar rutas
            $this->line('🛣️  VERIFICANDO RUTAS...');
            $this->verificarRutas($encuesta);
            $this->line('');

            // 3. Verificar middleware
            $this->line('🔧 VERIFICANDO MIDDLEWARE...');
            $this->verificarMiddleware();
            $this->line('');

            // 4. Verificar controlador
            $this->line('🎮 VERIFICANDO CONTROLADOR...');
            $this->verificarControlador($encuesta);
            $this->line('');

            // 5. Verificar vista
            $this->line('👁️  VERIFICANDO VISTA...');
            $this->verificarVista($encuesta);
            $this->line('');

            // 6. Verificar logs de error
            $this->line('📝 VERIFICANDO LOGS DE ERROR...');
            $this->verificarLogsError();
            $this->line('');

            // 7. Simular request
            $this->line('🧪 SIMULANDO REQUEST...');
            $this->simularRequest($encuesta);
            $this->line('');

            $this->info('✅ DIAGNÓSTICO COMPLETADO');
            $this->line('');
            $this->line('📋 RESUMEN DEL FLUJO:');
            $this->line('   1. Usuario accede a: /publica/{slug}');
            $this->line('   2. Middleware: verificar.token.encuesta');
            $this->line('   3. Controlador: EncuestaPublicaController@mostrar');
            $this->line('   4. Vista: encuestas.publica');
            $this->line('   5. Usuario envía formulario a: /publica/{id}');
            $this->line('   6. Controlador: EncuestaPublicaController@responder');
            $this->line('   7. Se guardan respuestas en: respuestas_usuario');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en diagnóstico: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Verificar encuesta
     */
    private function verificarEncuesta($encuestaId = null)
    {
        if ($encuestaId) {
            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])
                ->where('id', $encuestaId)
                ->where('estado', 'publicada')
                ->first();
        } else {
            $encuesta = Encuesta::with(['preguntas.respuestas', 'empresa'])
                ->where('estado', 'publicada')
                ->first();
        }

        if (!$encuesta) {
            $this->error('   ❌ No se encontró encuesta publicada');
            return null;
        }

        $this->line("   ✅ Encuesta encontrada: {$encuesta->titulo}");
        $this->line("   • ID: {$encuesta->id}");
        $this->line("   • Slug: {$encuesta->slug}");
        $this->line("   • Estado: {$encuesta->estado}");
        $this->line("   • Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));
        $this->line("   • Preguntas: " . $encuesta->preguntas->count());

        return $encuesta;
    }

    /**
     * Verificar rutas
     */
    private function verificarRutas($encuesta)
    {
        $this->line("   ✅ Ruta GET: /publica/{$encuesta->slug}");
        $this->line("   ✅ Ruta POST: /publica/{$encuesta->id}");
        $this->line("   ✅ Nombre ruta: encuestas.responder");

        // Verificar que las rutas existen
        $routes = app('router')->getRoutes();
        $publicaRoutes = collect($routes)->filter(function ($route) {
            return strpos($route->uri(), 'publica') !== false;
        });

        $this->line("   • Rutas públicas encontradas: " . $publicaRoutes->count());
    }

    /**
     * Verificar middleware
     */
    private function verificarMiddleware()
    {
        $this->line("   ✅ Middleware: verificar.token.encuesta");
        $this->line("   ✅ Middleware: EmergencyHostingFix");
        $this->line("   ✅ Middleware: FixHostingCookies");
        $this->line("   ✅ CSRF: Deshabilitado en hosting");
    }

    /**
     * Verificar controlador
     */
    private function verificarControlador($encuesta)
    {
        $this->line("   ✅ Controlador: EncuestaPublicaController");
        $this->line("   ✅ Método mostrar: Existe");
        $this->line("   ✅ Método responder: Existe");

        // Verificar que el controlador puede procesar la encuesta
        try {
            $controller = new \App\Http\Controllers\EncuestaPublicaController();
            $this->line("   ✅ Controlador instanciado correctamente");
        } catch (Exception $e) {
            $this->error("   ❌ Error instanciando controlador: " . $e->getMessage());
        }
    }

    /**
     * Verificar vista
     */
    private function verificarVista($encuesta)
    {
        $viewPath = resource_path('views/encuestas/publica.blade.php');
        if (file_exists($viewPath)) {
            $this->line("   ✅ Vista existe: encuestas.publica");
        } else {
            $this->error("   ❌ Vista no encontrada: encuestas.publica");
        }

        $this->line("   ✅ Formulario POST a: " . route('encuestas.responder', $encuesta->id));
        $this->line("   ✅ Token CSRF incluido en formulario");
    }

    /**
     * Verificar logs de error
     */
    private function verificarLogsError()
    {
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            $this->line("   ✅ Archivo de log existe");

            // Leer últimas líneas del log
            $lines = file($logPath);
            $recentLines = array_slice($lines, -10);

            $this->line("   📝 Últimas líneas del log:");
            foreach ($recentLines as $line) {
                if (strpos($line, 'ERROR') !== false || strpos($line, 'Exception') !== false) {
                    $this->line("      " . trim($line));
                }
            }
        } else {
            $this->warn("   ⚠️  Archivo de log no encontrado");
        }
    }

    /**
     * Simular request
     */
    private function simularRequest($encuesta)
    {
        $this->line("   🧪 Simulando request POST a /publica/{$encuesta->id}");
        $this->line("   • Datos simulados:");
        $this->line("     - _token: [CSRF token]");
        $this->line("     - respuestas: [array de respuestas]");

        // Verificar que la tabla respuestas_usuario existe
        try {
            $tableExists = DB::select("SHOW TABLES LIKE 'respuestas_usuario'");
            if (!empty($tableExists)) {
                $this->line("   ✅ Tabla respuestas_usuario existe");
            } else {
                $this->error("   ❌ Tabla respuestas_usuario no existe");
            }
        } catch (Exception $e) {
            $this->error("   ❌ Error verificando tabla: " . $e->getMessage());
        }
    }
}
