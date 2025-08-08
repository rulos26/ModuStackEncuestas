<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empleado;
use App\Models\EmpresasCliente;
use App\Models\ConfiguracionEnvio;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Exception;

class VerificarSistemaCompleto extends Command
{
    protected $signature = 'verificar:sistema-completo {--detallado}';
    protected $description = 'Verificación completa del sistema de encuestas y envío masivo';

    public function handle()
    {
        $this->info('🔍 VERIFICACIÓN COMPLETA DEL SISTEMA DE ENCUESTAS');
        $this->line('');

        $detallado = $this->option('detallado');

        // 1. Verificar base de datos
        $this->verificarBaseDatos($detallado);

        // 2. Verificar módulos
        $this->verificarModulos($detallado);

        // 3. Verificar configuración
        $this->verificarConfiguracion($detallado);

        // 4. Verificar rutas
        $this->verificarRutas($detallado);

        // 5. Resumen final
        $this->resumenFinal();

        $this->info('✅ VERIFICACIÓN COMPLETA FINALIZADA');
        return 0;
    }

    private function verificarBaseDatos($detallado = false)
    {
        $this->info('📊 1. Verificando Base de Datos...');

        try {
            // Empresas
            $empresas = EmpresasCliente::count();
            $this->info("   📈 Empresas clientes: {$empresas}");

            // Empleados
            $empleados = Empleado::count();
            $empleadosConEmail = Empleado::whereNotNull('correo_electronico')
                ->where('correo_electronico', '!=', '')
                ->count();
            $this->info("   👥 Empleados: {$empleados} (Con email: {$empleadosConEmail})");

            // Encuestas
            $encuestas = Encuesta::count();
            $encuestasPublicadas = Encuesta::where('estado', 'publicada')->count();
            $encuestasEnviadas = Encuesta::where('estado', 'enviada')->count();
            $this->info("   📋 Encuestas: {$encuestas} (Publicadas: {$encuestasPublicadas}, Enviadas: {$encuestasEnviadas})");

            // Configuraciones
            $configuraciones = ConfiguracionEnvio::count();
            $this->info("   ⚙️ Configuraciones de envío: {$configuraciones}");

            if ($detallado) {
                $this->line('');
                $this->info('   📋 Detalle de encuestas disponibles para envío:');
                $encuestasEnvio = Encuesta::whereIn('estado', ['publicada', 'enviada'])
                    ->with('empresa')
                    ->get();

                foreach ($encuestasEnvio as $encuesta) {
                    $empresa = $encuesta->empresa ? $encuesta->empresa->nombre : 'Sin empresa';
                    $this->line("      • ID {$encuesta->id}: {$encuesta->titulo} ({$empresa}) - {$encuesta->estado}");
                }
            }

        } catch (Exception $e) {
            $this->error("   ❌ Error verificando base de datos: {$e->getMessage()}");
        }
    }

    private function verificarModulos($detallado = false)
    {
        $this->info('🧪 2. Verificando Módulos...');

        // Módulo de Envío Masivo
        try {
            $controller = new EnvioMasivoEncuestasController();
            $this->info("   ✅ Módulo de Envío Masivo: Operativo");

            if ($detallado) {
                // Probar funciones principales
                $encuesta = Encuesta::whereIn('estado', ['publicada', 'enviada'])->first();
                if ($encuesta) {
                    $link = $controller->generarLinkPublico($encuesta);
                    $this->line("      • Generación de links: ✅");

                    $empleado = Empleado::whereNotNull('correo_electronico')->first();
                    if ($empleado) {
                        $cuerpo = $controller->generarcuerpoCorreo($empleado, $encuesta, $link);
                        $this->line("      • Generación de correos: ✅");
                    }
                }
            }
        } catch (Exception $e) {
            $this->error("   ❌ Módulo de Envío Masivo: Error - {$e->getMessage()}");
        }

        // Verificar comandos de prueba
        try {
            $this->line("   🔧 Comandos de prueba disponibles:");
            $this->line("      • php artisan test:envio-masivo-completo");
            $this->line("      • php artisan probar:envio-masivo");
            $this->line("      • php artisan verificar:relaciones-empresa");
        } catch (Exception $e) {
            $this->error("   ❌ Error verificando comandos: {$e->getMessage()}");
        }
    }

    private function verificarConfiguracion($detallado = false)
    {
        $this->info('⚙️ 3. Verificando Configuración...');

        try {
            $controller = new EnvioMasivoEncuestasController();
            $configuracion = $controller->validarConfiguracion();
            $data = $configuracion->getData();

            if ($data->valido) {
                $this->info("   ✅ Configuración SMTP: Válida");
            } else {
                $this->warn("   ⚠️ Configuración SMTP: Requiere configuración");
                if ($detallado) {
                    foreach ($data->errores as $error) {
                        $this->line("      • {$error}");
                    }
                }
            }

            if (!empty($data->advertencias)) {
                foreach ($data->advertencias as $advertencia) {
                    $this->warn("   ⚠️ {$advertencia}");
                }
            }

        } catch (Exception $e) {
            $this->error("   ❌ Error verificando configuración: {$e->getMessage()}");
        }
    }

    private function verificarRutas($detallado = false)
    {
        $this->info('🛣️ 4. Verificando Rutas...');

        $rutas = [
            'envio-masivo.index' => 'Pantalla principal',
            'envio-masivo.enviar' => 'Procesar envío',
            'envio-masivo.estadisticas' => 'Estadísticas',
            'envio-masivo.vista-previa' => 'Vista previa',
            'envio-masivo.validar-configuracion' => 'Validar configuración',
            'envio-masivo.obtener-empleados' => 'Obtener empleados (AJAX)'
        ];

        try {
            foreach ($rutas as $nombre => $descripcion) {
                if ($this->routeExists($nombre)) {
                    $this->info("   ✅ {$descripcion}");
                    if ($detallado) {
                        $this->line("      • Ruta: " . route($nombre, [], false));
                    }
                } else {
                    $this->error("   ❌ {$descripcion}: No encontrada");
                }
            }
        } catch (Exception $e) {
            $this->error("   ❌ Error verificando rutas: {$e->getMessage()}");
        }
    }

    private function routeExists($name)
    {
        try {
            route($name);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function resumenFinal()
    {
        $this->info('📋 5. Resumen del Sistema...');

        $empresas = EmpresasCliente::count();
        $empleados = Empleado::whereNotNull('correo_electronico')
            ->where('correo_electronico', '!=', '')
            ->count();
        $encuestasDisponibles = Encuesta::whereIn('estado', ['publicada', 'enviada'])->count();

        $this->line("   🏢 Empresas activas: {$empresas}");
        $this->line("   👥 Empleados con email: {$empleados}");
        $this->line("   📋 Encuestas disponibles para envío: {$encuestasDisponibles}");
        $this->line('');

        if ($empresas > 0 && $empleados > 0 && $encuestasDisponibles > 0) {
            $this->info('   🎉 ¡Sistema listo para envío masivo de encuestas!');
            $this->line('');
            $this->info('   🚀 Para acceder al módulo:');
            $this->line('      • URL: /envio-masivo');
            $this->line('      • Menú: Gestión de Encuestas → Envío Masivo');
            $this->line('      • Servidor: https://rulossoluciones.com/modustack12/envio-masivo');
        } else {
            $this->warn('   ⚠️ Sistema requiere configuración adicional:');
            if ($empresas == 0) $this->line('      • Agregar empresas clientes');
            if ($empleados == 0) $this->line('      • Agregar empleados con emails válidos');
            if ($encuestasDisponibles == 0) $this->line('      • Crear y publicar encuestas');
        }
    }
}
