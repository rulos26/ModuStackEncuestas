<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Encuesta;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DiagnosticarEliminacionMasiva extends Command
{
    protected $signature = 'diagnosticar:eliminacion-masiva';
    protected $description = 'Diagnosticar problemas con el botón de eliminación masiva';

    public function handle()
    {
        $this->info('🔍 DIAGNOSTICANDO BOTÓN DE ELIMINACIÓN MASIVA');
        $this->line('');

        try {
            // 1. Verificar rutas
            $this->line('1️⃣ VERIFICANDO RUTAS...');
            $this->verificarRutas();
            $this->line('');

            // 2. Verificar controlador
            $this->line('2️⃣ VERIFICANDO CONTROLADOR...');
            $this->verificarControlador();
            $this->line('');

            // 3. Verificar vista
            $this->line('3️⃣ VERIFICANDO VISTA...');
            $this->verificarVista();
            $this->line('');

            // 4. Verificar permisos
            $this->line('4️⃣ VERIFICANDO PERMISOS...');
            $this->verificarPermisos();
            $this->line('');

            // 5. Verificar datos
            $this->line('5️⃣ VERIFICANDO DATOS...');
            $this->verificarDatos();
            $this->line('');

            $this->info('✅ DIAGNÓSTICO COMPLETADO');
            $this->line('');
            $this->line('💡 RECOMENDACIONES:');
            $this->line('   • Si hay errores en las rutas, ejecuta: php artisan route:clear');
            $this->line('   • Si hay errores en la vista, verifica que el archivo existe');
            $this->line('   • Si hay errores de permisos, asigna el rol correcto al usuario');
            $this->line('   • Si no hay datos, crea algunas encuestas de prueba');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error en diagnóstico: ' . $e->getMessage());
            return 1;
        }
    }

    private function verificarRutas()
    {
        try {
            $rutas = [
                'encuestas.eliminacion-masiva',
                'encuestas.confirmar-eliminacion-masiva',
                'encuestas.ejecutar-eliminacion-masiva'
            ];

            foreach ($rutas as $ruta) {
                try {
                    $url = route($ruta);
                    $this->line("   ✅ {$ruta}: {$url}");
                } catch (\Exception $e) {
                    $this->error("   ❌ {$ruta}: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando rutas: " . $e->getMessage());
        }
    }

    private function verificarControlador()
    {
        try {
            $controller = new \App\Http\Controllers\EncuestaController();
            $this->line("   ✅ Controlador creado exitosamente");

            // Verificar que el método existe
            if (method_exists($controller, 'eliminacionMasiva')) {
                $this->line("   ✅ Método eliminacionMasiva existe");
            } else {
                $this->error("   ❌ Método eliminacionMasiva no existe");
            }

            if (method_exists($controller, 'confirmarEliminacionMasiva')) {
                $this->line("   ✅ Método confirmarEliminacionMasiva existe");
            } else {
                $this->error("   ❌ Método confirmarEliminacionMasiva no existe");
            }

            if (method_exists($controller, 'ejecutarEliminacionMasiva')) {
                $this->line("   ✅ Método ejecutarEliminacionMasiva existe");
            } else {
                $this->error("   ❌ Método ejecutarEliminacionMasiva no existe");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando controlador: " . $e->getMessage());
        }
    }

    private function verificarVista()
    {
        $vistas = [
            'encuestas.eliminacion-masiva',
            'encuestas.confirmar-eliminacion-masiva'
        ];

        foreach ($vistas as $vista) {
            $path = resource_path('views/' . str_replace('.', '/', $vista) . '.blade.php');
            if (file_exists($path)) {
                $this->line("   ✅ Vista {$vista}: " . basename($path));
            } else {
                $this->error("   ❌ Vista {$vista}: No encontrada en " . $path);
            }
        }
    }

    private function verificarPermisos()
    {
        try {
            $users = User::with('roles')->take(5)->get();

            if ($users->isEmpty()) {
                $this->warn("   ⚠️  No hay usuarios en el sistema");
                return;
            }

            $this->line("   📋 Verificando permisos de usuarios:");
            foreach ($users as $user) {
                $roles = $user->roles->pluck('name')->toArray();
                $this->line("      • Usuario {$user->id} ({$user->name}): " . implode(', ', $roles ?: ['Sin roles']));
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando permisos: " . $e->getMessage());
        }
    }

    private function verificarDatos()
    {
        try {
            $encuestasCount = Encuesta::count();
            $this->line("   📊 Total de encuestas: {$encuestasCount}");

            if ($encuestasCount > 0) {
                $encuestas = Encuesta::with(['empresa', 'user'])->take(3)->get();
                $this->line("   📋 Primeras encuestas:");
                foreach ($encuestas as $encuesta) {
                    $this->line("      • ID {$encuesta->id}: {$encuesta->titulo} (Estado: {$encuesta->estado})");
                }
            } else {
                $this->warn("   ⚠️  No hay encuestas en el sistema");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando datos: " . $e->getMessage());
        }
    }
}
