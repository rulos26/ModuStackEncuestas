<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Models\Encuesta;
use App\Models\BloqueEnvio;
use App\Models\SentMail;
use Exception;

class DiagnosticarDashboardSeguimiento extends Command
{
    protected $signature = 'dashboard:diagnosticar {encuesta_id} {--debug}';
    protected $description = 'Diagnostica problemas específicos del dashboard de seguimiento';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $debug = $this->option('debug');

        $this->info("🔍 DIAGNÓSTICO DEL DASHBOARD DE SEGUIMIENTO");
        $this->line('');

        try {
            // 1. Verificar encuesta
            $this->verificarEncuesta($encuestaId);

            // 2. Verificar rutas
            $this->verificarRutas();

            // 3. Verificar modelos
            $this->verificarModelos();

            // 4. Verificar tablas
            $this->verificarTablas();

            // 5. Verificar vista
            $this->verificarVista();

            // 6. Verificar controlador
            $this->verificarControlador();

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante el diagnóstico: " . $e->getMessage());

            if ($debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }

    private function verificarEncuesta($encuestaId)
    {
        $this->info("📋 VERIFICANDO ENCUESTA:");

        $encuesta = Encuesta::find($encuestaId);

        if (!$encuesta) {
            $this->error("   ❌ Encuesta con ID {$encuestaId} no encontrada");
            return;
        }

        $this->line("   ✅ Encuesta encontrada: '{$encuesta->titulo}'");
        $this->line("   - Estado: {$encuesta->estado}");
        $this->line("   - Preguntas: {$encuesta->preguntas()->count()}");
        $this->line("   - Envío masivo: " . ($encuesta->envio_masivo_activado ? 'Activado' : 'Desactivado'));
        $this->line('');
    }

    private function verificarRutas()
    {
        $this->info("🛣️  VERIFICANDO RUTAS:");

        $rutas = [
            'encuestas.seguimiento.dashboard' => 'Dashboard de seguimiento',
            'encuestas.seguimiento.actualizar' => 'Actualizar datos',
            'encuestas.seguimiento.pausar' => 'Pausar envío',
            'encuestas.seguimiento.reanudar' => 'Reanudar envío',
            'encuestas.seguimiento.cancelar' => 'Cancelar envío'
        ];

        foreach ($rutas as $ruta => $descripcion) {
            try {
                $url = route($ruta, ['encuesta' => 1]);
                $this->line("   ✅ {$descripcion}: {$ruta}");
            } catch (Exception $e) {
                $this->error("   ❌ {$descripcion}: {$ruta} - " . $e->getMessage());
            }
        }
        $this->line('');
    }

    private function verificarModelos()
    {
        $this->info("📦 VERIFICANDO MODELOS:");

        // Verificar BloqueEnvio
        try {
            $bloqueEnvio = new BloqueEnvio();
            $this->line("   ✅ BloqueEnvio: OK");
        } catch (Exception $e) {
            $this->error("   ❌ BloqueEnvio: " . $e->getMessage());
        }

        // Verificar SentMail
        try {
            $sentMail = new SentMail();
            $this->line("   ✅ SentMail: OK");
        } catch (Exception $e) {
            $this->error("   ❌ SentMail: " . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarTablas()
    {
        $this->info("🗄️  VERIFICANDO TABLAS:");

        $tablas = ['bloques_envio', 'sent_mails'];

        foreach ($tablas as $tabla) {
            try {
                $existe = DB::getSchemaBuilder()->hasTable($tabla);
                if ($existe) {
                    $this->line("   ✅ Tabla {$tabla}: Existe");

                    // Contar registros
                    $count = DB::table($tabla)->count();
                    $this->line("      - Registros: {$count}");
                } else {
                    $this->error("   ❌ Tabla {$tabla}: No existe");
                }
            } catch (Exception $e) {
                $this->error("   ❌ Error verificando tabla {$tabla}: " . $e->getMessage());
            }
        }
        $this->line('');
    }

    private function verificarVista()
    {
        $this->info("👁️  VERIFICANDO VISTA:");

        $rutaVista = resource_path('views/encuestas/seguimiento/dashboard.blade.php');

        if (file_exists($rutaVista)) {
            $this->line("   ✅ Vista dashboard.blade.php: Existe");

            $tamaño = filesize($rutaVista);
            $this->line("      - Tamaño: " . number_format($tamaño) . " bytes");
        } else {
            $this->error("   ❌ Vista dashboard.blade.php: No existe");
            $this->line("      - Ruta esperada: {$rutaVista}");
        }
        $this->line('');
    }

    private function verificarControlador()
    {
        $this->info("🎮 VERIFICANDO CONTROLADOR:");

        $rutaControlador = app_path('Http/Controllers/EncuestaSeguimientoController.php');

        if (file_exists($rutaControlador)) {
            $this->line("   ✅ EncuestaSeguimientoController: Existe");

            // Verificar métodos
            $metodos = ['dashboard', 'actualizarDatos', 'pausarEnvio', 'reanudarEnvio', 'cancelarEnvio'];

            foreach ($metodos as $metodo) {
                try {
                    $reflection = new \ReflectionClass('App\Http\Controllers\EncuestaSeguimientoController');
                    if ($reflection->hasMethod($metodo)) {
                        $this->line("      ✅ Método {$metodo}: Existe");
                    } else {
                        $this->error("      ❌ Método {$metodo}: No existe");
                    }
                } catch (Exception $e) {
                    $this->error("      ❌ Error verificando método {$metodo}: " . $e->getMessage());
                }
            }
        } else {
            $this->error("   ❌ EncuestaSeguimientoController: No existe");
        }
        $this->line('');
    }
}
