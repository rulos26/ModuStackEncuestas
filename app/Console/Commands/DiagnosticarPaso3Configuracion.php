<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Encuesta;
use App\Models\ConfiguracionEnvio;

class DiagnosticarPaso3Configuracion extends Command
{
    protected $signature = 'diagnosticar:paso3-configuracion {empresa_id?} {encuesta_ids?}';
    protected $description = 'Diagnosticar el Paso 3 de configuración de envío';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO PASO 3: CONFIGURACIÓN DE ENVÍO');
        $this->info('================================================');

        try {
            // Verificar parámetros
            $empresaId = $this->argument('empresa_id');
            $encuestaIds = $this->argument('encuesta_ids');

            if (!$empresaId) {
                $this->info('📋 Buscando empresas disponibles...');
                $empresas = DB::table('empresas_clientes')->select('id', 'nombre', 'nit')->get();

                if ($empresas->isEmpty()) {
                    $this->error('❌ No hay empresas disponibles');
                    return 1;
                }

                $this->info('📋 Empresas disponibles:');
                $this->table(['ID', 'Nombre', 'NIT'], $empresas->toArray());

                $empresaId = $this->ask('Ingrese el ID de la empresa a diagnosticar');
            }

            // Verificar empresa
            $empresa = DB::table('empresas_clientes')->where('id', $empresaId)->first();
            if (!$empresa) {
                $this->error("❌ Empresa con ID {$empresaId} no encontrada");
                return 1;
            }

            $this->info("✅ Empresa encontrada: {$empresa->nombre}");

            // Verificar encuestas
            if (!$encuestaIds) {
                $this->info('📋 Buscando encuestas de la empresa...');
                $encuestas = Encuesta::where('empresa_id', $empresaId)->select('id', 'titulo', 'estado')->get();

                if ($encuestas->isEmpty()) {
                    $this->error("❌ No hay encuestas para la empresa {$empresa->nombre}");
                    return 1;
                }

                $this->info('📋 Encuestas disponibles:');
                $this->table(['ID', 'Título', 'Estado'], $encuestas->toArray());

                $encuestaIds = $this->ask('Ingrese los IDs de encuestas separados por coma (ej: 1,2,3)');
            }

            $encuestaIdsArray = array_map('trim', explode(',', $encuestaIds));

            // Verificar encuestas específicas
            $encuestas = Encuesta::whereIn('id', $encuestaIdsArray)->get();
            if ($encuestas->isEmpty()) {
                $this->error("❌ No se encontraron encuestas con los IDs: {$encuestaIds}");
                return 1;
            }

            $this->info("✅ Encuestas encontradas: {$encuestas->count()}");

            // Verificar método getTiposEnvio
            $this->info('📋 Verificando método getTiposEnvio...');
            $tiposEnvio = ConfiguracionEnvio::getTiposEnvio();
            $this->info('✅ Tipos de envío disponibles:');
            $this->table(['Clave', 'Descripción'], array_map(function($key, $value) { return [$key, $value]; }, array_keys($tiposEnvio), array_values($tiposEnvio)));

            // Simular datos que se pasarían a la vista
            $this->info('📋 Datos que se pasarían a la vista:');
            $this->info("Empresa ID: {$empresa->id}");
            $this->info("Empresa Nombre: {$empresa->nombre}");
            $this->info("Empresa Email: {$empresa->correo_electronico}");
            $this->info("Encuestas count: {$encuestas->count()}");
            $this->info("Tipos de envío count: " . count($tiposEnvio));

            // Verificar si hay configuraciones existentes
            $configuracionesExistentes = ConfiguracionEnvio::where('empresa_id', $empresaId)
                ->whereIn('encuesta_id', $encuestaIdsArray)
                ->get();

            if ($configuracionesExistentes->isNotEmpty()) {
                $this->info('📋 Configuraciones existentes:');
                $this->table(['ID', 'Encuesta ID', 'Tipo Envío', 'Activo'],
                    $configuracionesExistentes->map(function($config) {
                        return [$config->id, $config->encuesta_id, $config->tipo_envio, $config->activo ? 'Sí' : 'No'];
                    })->toArray()
                );
            } else {
                $this->info('ℹ️  No hay configuraciones existentes para estas encuestas');
            }

            $this->info('🎉 Diagnóstico completado exitosamente');
            $this->info('💡 Si el Paso 3 sigue vacío, verifica:');
            $this->info('   1. Que la vista configurar.blade.php esté correcta');
            $this->info('   2. Que no haya errores de JavaScript');
            $this->info('   3. Que los datos se estén pasando correctamente');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error durante el diagnóstico: ' . $e->getMessage());
            $this->error('📋 Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
