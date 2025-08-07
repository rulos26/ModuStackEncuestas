<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empleado;
use App\Models\EmpresasCliente;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Exception;

class ProbarEnvioMasivo extends Command
{
    protected $signature = 'probar:envio-masivo {--encuesta_id=} {--simular}';
    protected $description = 'Prueba el módulo de envío masivo de encuestas';

    public function handle()
    {
        $this->info('🧪 PROBANDO MÓDULO DE ENVÍO MASIVO DE ENCUESTAS');
        $this->line('');

        // 1. Verificar encuestas disponibles
        $this->verificarEncuestas();

        // 2. Verificar empleados
        $this->verificarEmpleados();

        // 3. Probar funcionalidades
        $this->probarFuncionalidades();

        $this->info('✅ PRUEBA COMPLETADA');
    }

    private function verificarEncuestas()
    {
        $this->info('📋 1. Verificando encuestas disponibles...');

        $encuestas = Encuesta::whereIn('estado', ['publicada', 'enviada'])
            ->with('empresa')
            ->get();

        $this->info("   📈 Total encuestas disponibles: {$encuestas->count()}");

        if ($encuestas->count() > 0) {
            foreach ($encuestas as $encuesta) {
                $empresaNombre = $encuesta->empresa ? $encuesta->empresa->nombre : 'Sin empresa';
                $this->line("      • ID: {$encuesta->id} | Título: {$encuesta->titulo} | Empresa: {$empresaNombre} | Estado: {$encuesta->estado}");
            }
        } else {
            $this->warn("   ⚠️ No hay encuestas publicadas o enviadas disponibles");
        }
    }

    private function verificarEmpleados()
    {
        $this->info('👥 2. Verificando empleados...');

        $empresas = EmpresasCliente::with('empleados')->get();
        $this->info("   📈 Total empresas: {$empresas->count()}");

        foreach ($empresas as $empresa) {
            $empleados = $empresa->empleados;
            $empleadosConEmail = $empleados->whereNotNull('correo_electronico')
                ->where('correo_electronico', '!=', '')
                ->filter(function($empleado) {
                    return filter_var($empleado->correo_electronico, FILTER_VALIDATE_EMAIL);
                });

            $this->line("      • Empresa: {$empresa->nombre} | Total empleados: {$empleados->count()} | Con email válido: {$empleadosConEmail->count()}");
        }
    }

    private function probarFuncionalidades()
    {
        $this->info('🔧 3. Probando funcionalidades...');

        // Probar controlador
        $controller = new EnvioMasivoEncuestasController();

        // Probar validación de configuración
        $this->info("   📧 Probando validación de configuración SMTP...");
        try {
            $configuracion = $controller->validarConfiguracion();
            $this->line("      ✅ Configuración válida: " . ($configuracion->getData()->valido ? 'SÍ' : 'NO'));

            if (!$configuracion->getData()->valido) {
                foreach ($configuracion->getData()->errores as $error) {
                    $this->line("         ❌ {$error}");
                }
            }
        } catch (Exception $e) {
            $this->line("      ❌ Error validando configuración: {$e->getMessage()}");
        }

        // Probar generación de links
        $this->info("   🔗 Probando generación de links públicos...");
        $encuesta = Encuesta::whereIn('estado', ['publicada', 'enviada'])->first();

        if ($encuesta) {
            try {
                $link = $controller->generarLinkPublico($encuesta);
                $this->line("      ✅ Link generado: {$link}");
            } catch (Exception $e) {
                $this->line("      ❌ Error generando link: {$e->getMessage()}");
            }
        } else {
            $this->line("      ⚠️ No hay encuestas disponibles para probar");
        }

        // Probar generación de cuerpo de correo
        $this->info("   📝 Probando generación de cuerpo de correo...");
        if ($encuesta) {
            $empleado = Empleado::whereNotNull('correo_electronico')->first();

            if ($empleado) {
                try {
                    $cuerpo = $controller->generarcuerpoCorreo($empleado, $encuesta, 'http://ejemplo.com/encuesta');
                    $this->line("      ✅ Cuerpo generado correctamente");
                    $this->line("      📄 Longitud: " . strlen($cuerpo) . " caracteres");
                } catch (Exception $e) {
                    $this->line("      ❌ Error generando cuerpo: {$e->getMessage()}");
                }
            } else {
                $this->line("      ⚠️ No hay empleados con email para probar");
            }
        }
    }
}
