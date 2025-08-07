<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empleado;
use App\Models\EmpresasCliente;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Illuminate\Http\Request;
use Exception;

class TestEnvioMasivoCompleto extends Command
{
    protected $signature = 'test:envio-masivo-completo {--encuesta_id=} {--simular}';
    protected $description = 'Prueba completa del módulo de envío masivo de encuestas';

    public function handle()
    {
        $this->info('🧪 PRUEBA COMPLETA DEL MÓDULO DE ENVÍO MASIVO');
        $this->line('');

        $encuestaId = $this->option('encuesta_id');
        $simular = $this->option('simular');

        // 1. Verificar encuestas disponibles
        $encuesta = $this->seleccionarEncuesta($encuestaId);
        if (!$encuesta) {
            $this->error('❌ No se pudo seleccionar una encuesta válida');
            return 1;
        }

        // 2. Verificar empleados
        $empleados = $this->verificarEmpleados($encuesta);
        if ($empleados->isEmpty()) {
            $this->error('❌ No hay empleados con emails válidos para esta encuesta');
            return 1;
        }

        // 3. Probar funcionalidades del controlador
        $this->probarControlador($encuesta, $empleados);

        // 4. Simular envío (si se solicita)
        if ($simular) {
            $this->simularEnvio($encuesta, $empleados);
        }

        $this->info('✅ PRUEBA COMPLETA FINALIZADA');
        return 0;
    }

    private function seleccionarEncuesta($encuestaId = null)
    {
        $this->info('📋 1. Seleccionando encuesta...');

        if ($encuestaId) {
            $encuesta = Encuesta::whereIn('estado', ['publicada', 'enviada'])
                ->with('empresa')
                ->find($encuestaId);
        } else {
            $encuesta = Encuesta::whereIn('estado', ['publicada', 'enviada'])
                ->with('empresa')
                ->first();
        }

        if (!$encuesta) {
            $this->error('   ❌ No hay encuestas publicadas o enviadas disponibles');
            return null;
        }

        $empresaNombre = $encuesta->empresa ? $encuesta->empresa->nombre : 'Sin empresa';
        $this->info("   ✅ Encuesta seleccionada: ID {$encuesta->id} - {$encuesta->titulo}");
        $this->line("      • Empresa: {$empresaNombre}");
        $this->line("      • Estado: {$encuesta->estado}");
        $this->line("      • Preguntas: {$encuesta->preguntas->count()}");

        return $encuesta;
    }

    private function verificarEmpleados($encuesta)
    {
        $this->info('👥 2. Verificando empleados...');

        $empresa = $encuesta->empresa;
        if (!$empresa) {
            $this->error('   ❌ La encuesta no está asociada a una empresa');
            return collect();
        }

        $empleados = Empleado::where('empresa_id', $empresa->id)
            ->whereNotNull('correo_electronico')
            ->where('correo_electronico', '!=', '')
            ->get();

        $empleadosValidos = $empleados->filter(function($empleado) {
            return filter_var($empleado->correo_electronico, FILTER_VALIDATE_EMAIL);
        });

        $this->info("   📈 Total empleados: {$empleados->count()}");
        $this->info("   ✅ Con email válido: {$empleadosValidos->count()}");

        foreach ($empleadosValidos as $empleado) {
            $this->line("      • {$empleado->nombre} - {$empleado->correo_electronico}");
        }

        return $empleadosValidos;
    }

    private function probarControlador($encuesta, $empleados)
    {
        $this->info('🔧 3. Probando controlador...');

        $controller = new EnvioMasivoEncuestasController();

        // Probar generación de link
        try {
            $link = $controller->generarLinkPublico($encuesta);
            $this->info("   ✅ Link generado: {$link}");
        } catch (Exception $e) {
            $this->error("   ❌ Error generando link: {$e->getMessage()}");
        }

        // Probar generación de cuerpo de correo
        try {
            $empleado = $empleados->first();
            $cuerpo = $controller->generarcuerpoCorreo($empleado, $encuesta, $link);
            $this->info("   ✅ Cuerpo de correo generado");
            $this->line("      • Longitud: " . strlen($cuerpo) . " caracteres");
        } catch (Exception $e) {
            $this->error("   ❌ Error generando cuerpo: {$e->getMessage()}");
        }

        // Probar validación de configuración
        try {
            $configuracion = $controller->validarConfiguracion();
            $data = $configuracion->getData();
            $this->info("   ✅ Configuración validada");
            $this->line("      • Válida: " . ($data->valido ? 'SÍ' : 'NO'));
            
            if (!$data->valido) {
                foreach ($data->errores as $error) {
                    $this->line("      • Error: {$error}");
                }
            }
        } catch (Exception $e) {
            $this->error("   ❌ Error validando configuración: {$e->getMessage()}");
        }
    }

    private function simularEnvio($encuesta, $empleados)
    {
        $this->info('📧 4. Simulando envío...');

        $controller = new EnvioMasivoEncuestasController();
        $link = $controller->generarLinkPublico($encuesta);

        $resultado = [
            'enviados' => [],
            'fallidos' => [],
            'total' => $empleados->count(),
            'exitosos' => 0,
            'fallidos' => 0
        ];

        foreach ($empleados as $empleado) {
            try {
                // Simular envío (sin enviar realmente)
                $this->line("   📤 Simulando envío a: {$empleado->nombre} ({$empleado->correo_electronico})");
                
                $resultado['enviados'][] = [
                    'email' => $empleado->correo_electronico,
                    'empleado' => $empleado->nombre
                ];
                $resultado['exitosos']++;

            } catch (Exception $e) {
                $this->line("   ❌ Error simulado: {$e->getMessage()}");
                $resultado['fallidos'][] = [
                    'email' => $empleado->correo_electronico,
                    'empleado' => $empleado->nombre,
                    'error' => $e->getMessage()
                ];
                $resultado['fallidos']++;
            }
        }

        // Mostrar resumen
        $this->info("   📊 Resumen del envío simulado:");
        $this->line("      • Total: {$resultado['total']}");
        $this->line("      • Exitosos: {$resultado['exitosos']}");
        $this->line("      • Fallidos: {$resultado['fallidos']}");
        $this->line("      • Tasa de éxito: " . round(($resultado['exitosos'] / $resultado['total']) * 100, 1) . "%");
    }
} 