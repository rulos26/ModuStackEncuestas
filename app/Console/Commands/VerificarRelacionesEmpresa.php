<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\ConfiguracionEnvio;
use App\Models\EmpresasCliente;
use Illuminate\Support\Facades\DB;

class VerificarRelacionesEmpresa extends Command
{
    protected $signature = 'verificar:relaciones-empresa';
    protected $description = 'Verifica que las relaciones entre encuestas y empresas_clientes estén correctas';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO RELACIONES ENTRE ENCUESTAS Y EMPRESAS_CLIENTES');
        $this->line('');

        // 1. Verificar tabla empresas_clientes
        $this->verificarTablaEmpresasClientes();

        // 2. Verificar encuestas
        $this->verificarEncuestas();

        // 3. Verificar configuraciones de envío
        $this->verificarConfiguracionesEnvio();

        // 4. Verificar empleados
        $this->verificarEmpleados();

        $this->info('✅ VERIFICACIÓN COMPLETADA');
    }

    private function verificarTablaEmpresasClientes()
    {
        $this->info('📊 1. Verificando tabla empresas_clientes...');

        $empresasClientes = EmpresasCliente::count();
        $this->info("   📈 Total empresas_clientes: {$empresasClientes}");

        if ($empresasClientes > 0) {
            $empresas = EmpresasCliente::limit(3)->get();
            foreach ($empresas as $empresa) {
                $this->line("      • ID: {$empresa->id} | Nombre: {$empresa->nombre}");
            }
        } else {
            $this->warn("   ⚠️ No hay empresas_clientes registradas");
        }
    }

    private function verificarEncuestas()
    {
        $this->info('📋 2. Verificando encuestas...');

        $totalEncuestas = Encuesta::count();
        $encuestasConEmpresa = Encuesta::whereNotNull('empresa_id')->count();
        $encuestasSinEmpresa = Encuesta::whereNull('empresa_id')->count();

        $this->info("   📈 Total encuestas: {$totalEncuestas}");
        $this->info("   📈 Con empresa_id: {$encuestasConEmpresa}");
        $this->info("   📈 Sin empresa_id: {$encuestasSinEmpresa}");

        // Verificar encuestas con empresa_id válido
        $encuestasValidas = Encuesta::whereNotNull('empresa_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('empresas_clientes')
                    ->whereRaw('encuestas.empresa_id = empresas_clientes.id');
            })
            ->count();

        $this->info("   📈 Con empresa_id válido: {$encuestasValidas}");

        if ($encuestasValidas < $encuestasConEmpresa) {
            $this->warn("   ⚠️ Hay encuestas con empresa_id inválido");
        }

        // Mostrar algunas encuestas
        $encuestas = Encuesta::with('empresa')->limit(3)->get();
        foreach ($encuestas as $encuesta) {
            $empresaNombre = $encuesta->empresa ? $encuesta->empresa->nombre : 'No encontrada';
            $this->line("      • ID: {$encuesta->id} | Empresa: {$empresaNombre}");
        }
    }

    private function verificarConfiguracionesEnvio()
    {
        $this->info('📧 3. Verificando configuraciones de envío...');

        $totalConfiguraciones = ConfiguracionEnvio::count();
        $configuracionesConEmpresa = ConfiguracionEnvio::whereNotNull('empresa_id')->count();
        $configuracionesSinEmpresa = ConfiguracionEnvio::whereNull('empresa_id')->count();

        $this->info("   📈 Total configuraciones: {$totalConfiguraciones}");
        $this->info("   📈 Con empresa_id: {$configuracionesConEmpresa}");
        $this->info("   📈 Sin empresa_id: {$configuracionesSinEmpresa}");

        // Verificar configuraciones con empresa_id válido
        $configuracionesValidas = ConfiguracionEnvio::whereNotNull('empresa_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('empresas_clientes')
                    ->whereRaw('configuracion_envios.empresa_id = empresas_clientes.id');
            })
            ->count();

        $this->info("   📈 Con empresa_id válido: {$configuracionesValidas}");

        if ($configuracionesValidas < $configuracionesConEmpresa) {
            $this->warn("   ⚠️ Hay configuraciones con empresa_id inválido");
        }
    }

    private function verificarEmpleados()
    {
        $this->info('👥 4. Verificando empleados...');

        $totalEmpleados = DB::table('empleados')->count();
        $empleadosConEmpresa = DB::table('empleados')->whereNotNull('empresa_id')->count();
        $empleadosSinEmpresa = DB::table('empleados')->whereNull('empresa_id')->count();

        $this->info("   📈 Total empleados: {$totalEmpleados}");
        $this->info("   📈 Con empresa_id: {$empleadosConEmpresa}");
        $this->info("   📈 Sin empresa_id: {$empleadosSinEmpresa}");

        // Verificar empleados con empresa_id válido
        $empleadosValidos = DB::table('empleados')
            ->join('empresas_clientes', 'empleados.empresa_id', '=', 'empresas_clientes.id')
            ->count();

        $this->info("   📈 Con empresa_id válido: {$empleadosValidos}");

        if ($empleadosValidos < $empleadosConEmpresa) {
            $this->warn("   ⚠️ Hay empleados con empresa_id inválido");
        }
    }
}
