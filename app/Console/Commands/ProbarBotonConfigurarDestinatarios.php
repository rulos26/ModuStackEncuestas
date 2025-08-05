<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use App\Models\EmpresasCliente;
use App\Models\Empleado;

class ProbarBotonConfigurarDestinatarios extends Command
{
    protected $signature = 'probar:boton-configurar-destinatarios';
    protected $description = 'Probar la funcionalidad completa del botón configurar destinatarios';

    public function handle()
    {
        $this->info('🔍 PROBANDO BOTÓN "CONFIGURAR DESTINATARIOS"');
        $this->line('');

        // 1. Verificar configuraciones existentes
        $this->info('📋 CONFIGURACIONES EXISTENTES:');
        $configuraciones = ConfiguracionEnvio::with(['empresa', 'encuesta'])->get();

        if ($configuraciones->isEmpty()) {
            $this->error('❌ No hay configuraciones de envío');
            return 1;
        }

        foreach ($configuraciones as $config) {
            $this->line("   ID: {$config->id}");
            $this->line("   Empresa ID: {$config->empresa_id}");
            $this->line("   Empresa Nombre: " . ($config->empresa ? $config->empresa->nombre : 'NULL'));
            $this->line("   Encuesta ID: {$config->encuesta_id}");
            $this->line("   Encuesta Título: " . ($config->encuesta ? $config->encuesta->titulo : 'NULL'));
            $this->line("   ---");
        }

        // 2. Verificar empresas cliente
        $this->info('🏢 EMPRESAS CLIENTE:');
        $empresas = EmpresasCliente::all();

        foreach ($empresas as $empresa) {
            $empleadosCount = Empleado::where('empresa_id', $empresa->id)->count();
            $this->line("   ID: {$empresa->id} - {$empresa->nombre} ({$empleadosCount} empleados)");
        }

        // 3. Verificar empleados por empresa
        $this->info('👥 EMPLEADOS POR EMPRESA:');
        foreach ($empresas as $empresa) {
            $empleados = Empleado::where('empresa_id', $empresa->id)->get();
            $this->line("   Empresa: {$empresa->nombre}");

            if ($empleados->isEmpty()) {
                $this->warn("     ⚠️  No hay empleados");
            } else {
                foreach ($empleados as $empleado) {
                    $this->line("     - {$empleado->nombre} ({$empleado->correo_electronico})");
                }
            }
            $this->line("");
        }

        // 4. Probar endpoint específico
        $this->info('🧪 PROBANDO ENDPOINT obtenerEmpleados:');

        foreach ($configuraciones as $config) {
            $this->line("   Probando configuración ID: {$config->id}");

            try {
                $controller = new \App\Http\Controllers\ConfiguracionEnvioController();
                $response = $controller->obtenerEmpleados($config->id);
                $data = $response->getData();

                if ($data->success) {
                    $this->info("     ✅ Éxito");
                    $this->line("     Empresa: {$data->configuracion->empresa_nombre}");
                    $this->line("     Encuesta: {$data->configuracion->encuesta_titulo}");
                    $this->line("     Empleados: " . count($data->empleados));
                } else {
                    $this->error("     ❌ Error: {$data->message}");
                }
            } catch (\Exception $e) {
                $this->error("     ❌ Excepción: " . $e->getMessage());
            }
            $this->line("");
        }

        // 5. Recomendaciones
        $this->info('💡 RECOMENDACIONES:');

        $configuracionesSinEmpresa = $configuraciones->filter(function($config) {
            return !$config->empresa;
        });

        if ($configuracionesSinEmpresa->isNotEmpty()) {
            $this->warn("   ⚠️  Configuraciones sin empresa asignada:");
            foreach ($configuracionesSinEmpresa as $config) {
                $this->line("     - Configuración ID: {$config->id}");
            }
        }

        $empresasSinEmpleados = $empresas->filter(function($empresa) {
            return Empleado::where('empresa_id', $empresa->id)->count() === 0;
        });

        if ($empresasSinEmpleados->isNotEmpty()) {
            $this->warn("   ⚠️  Empresas sin empleados:");
            foreach ($empresasSinEmpleados as $empresa) {
                $this->line("     - {$empresa->nombre} (ID: {$empresa->id})");
            }
        }

        if ($configuracionesSinEmpresa->isEmpty() && $empresasSinEmpleados->isEmpty()) {
            $this->info("   ✅ Todo está configurado correctamente");
        }

        $this->info('✅ Prueba completada');
        return 0;
    }
}
