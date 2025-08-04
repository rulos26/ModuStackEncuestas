<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use App\Models\Empresa;
use App\Models\Encuesta;
use Illuminate\Support\Facades\DB;

class DiagnosticarConfiguracionEnvio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'diagnosticar:configuracion-envio
                            {--empresa-id= : ID de empresa específica}
                            {--encuesta-id= : ID de encuesta específica}
                            {--debug : Mostrar información detallada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnostica configuraciones de envío de correos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 DIAGNÓSTICO DE CONFIGURACIONES DE ENVÍO');
        $this->line('');

        $empresaId = $this->option('empresa-id');
        $encuestaId = $this->option('encuesta-id');
        $debug = $this->option('debug');

        // 1. Verificar conexión a base de datos
        $this->verificarConexionBD();

        // 2. Mostrar estadísticas generales
        $this->mostrarEstadisticasGenerales($empresaId, $encuestaId);

        // 3. Diagnosticar configuraciones específicas
        $this->diagnosticarConfiguraciones($empresaId, $encuestaId, $debug);

        // 4. Verificar integridad de datos
        $this->verificarIntegridadDatos($empresaId, $encuestaId);

        $this->info('✅ DIAGNÓSTICO COMPLETADO');
    }

    private function verificarConexionBD()
    {
        $this->info('📊 1. Verificando conexión a base de datos...');

        try {
            DB::connection()->getPdo();
            $this->info('   ✅ Conexión exitosa a: ' . DB::connection()->getDatabaseName());
        } catch (\Exception $e) {
            $this->error('   ❌ Error de conexión: ' . $e->getMessage());
            return false;
        }

        $this->line('');
        return true;
    }

    private function mostrarEstadisticasGenerales($empresaId, $encuestaId)
    {
        $this->info('📈 2. Estadísticas generales...');

        try {
            $query = ConfiguracionEnvio::query();

            if ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }

            if ($encuestaId) {
                $query->where('encuesta_id', $encuestaId);
            }

            $total = $query->count();
            $manuales = $query->where('tipo_envio', 'manual')->count();
            $programadas = $query->where('tipo_envio', 'programado')->count();
            $activas = $query->where('activo', true)->count();
            $pendientes = $query->where('estado_programacion', 'pendiente')->count();
            $enProceso = $query->where('estado_programacion', 'en_proceso')->count();
            $completadas = $query->where('estado_programacion', 'completado')->count();

            $this->info("   📊 Total configuraciones: {$total}");
            $this->info("   📊 Configuraciones manuales: {$manuales}");
            $this->info("   📊 Configuraciones programadas: {$programadas}");
            $this->info("   📊 Configuraciones activas: {$activas}");
            $this->info("   📊 Configuraciones pendientes: {$pendientes}");
            $this->info("   📊 Configuraciones en proceso: {$enProceso}");
            $this->info("   📊 Configuraciones completadas: {$completadas}");

        } catch (\Exception $e) {
            $this->error('   ❌ Error obteniendo estadísticas: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function diagnosticarConfiguraciones($empresaId, $encuestaId, $debug)
    {
        $this->info('🔍 3. Diagnosticando configuraciones...');

        try {
            $query = ConfiguracionEnvio::with(['empresa', 'encuesta']);

            if ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }

            if ($encuestaId) {
                $query->where('encuesta_id', $encuestaId);
            }

            $configuraciones = $query->get();

            if ($configuraciones->isEmpty()) {
                $this->warn('   ⚠️ No se encontraron configuraciones');
                return;
            }

            foreach ($configuraciones as $config) {
                $this->line("   📋 Configuración ID: {$config->id}");
                $this->line("      Empresa: {$config->empresa->nombre}");
                $this->line("      Encuesta: {$config->encuesta->titulo}");
                $this->line("      Tipo envío: {$config->tipo_envio}");
                $this->line("      Estado: {$config->estado_programacion}");
                $this->line("      Activa: " . ($config->activo ? '✅ SÍ' : '❌ NO'));

                if ($config->tipo_envio === 'programado') {
                    $this->line("      Fecha envío: {$config->fecha_envio}");
                    $this->line("      Hora envío: {$config->hora_envio}");
                    $this->line("      Tipo destinatario: {$config->tipo_destinatario}");
                    $this->line("      Número bloques: {$config->numero_bloques}");
                    $this->line("      Modo prueba: " . ($config->modo_prueba ? '✅ SÍ' : '❌ NO'));

                    if ($config->correo_prueba) {
                        $this->line("      Correo prueba: {$config->correo_prueba}");
                    }
                }

                if ($debug) {
                    $this->line("      Remitente: {$config->nombre_remitente} <{$config->correo_remitente}>");
                    $this->line("      Asunto: {$config->asunto}");
                    $this->line("      Creado: {$config->created_at}");
                    $this->line("      Actualizado: {$config->updated_at}");
                }

                $this->line('');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error diagnosticando configuraciones: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarIntegridadDatos($empresaId, $encuestaId)
    {
        $this->info('🔧 4. Verificando integridad de datos...');

        try {
            $query = ConfiguracionEnvio::query();

            if ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }

            if ($encuestaId) {
                $query->where('encuesta_id', $encuestaId);
            }

            $configuraciones = $query->get();

            $problemas = [];

            foreach ($configuraciones as $config) {
                // Verificar empresa
                if (!$config->empresa) {
                    $problemas[] = "Configuración ID {$config->id}: Empresa no encontrada";
                }

                // Verificar encuesta
                if (!$config->encuesta) {
                    $problemas[] = "Configuración ID {$config->id}: Encuesta no encontrada";
                }

                // Verificar campos requeridos para programado
                if ($config->tipo_envio === 'programado') {
                    if (!$config->fecha_envio) {
                        $problemas[] = "Configuración ID {$config->id}: Fecha de envío faltante";
                    }
                    if (!$config->hora_envio) {
                        $problemas[] = "Configuración ID {$config->id}: Hora de envío faltante";
                    }
                    if (!$config->tipo_destinatario) {
                        $problemas[] = "Configuración ID {$config->id}: Tipo de destinatario faltante";
                    }
                }

                // Verificar campos básicos
                if (!$config->correo_remitente) {
                    $problemas[] = "Configuración ID {$config->id}: Correo remitente faltante";
                }
                if (!$config->asunto) {
                    $problemas[] = "Configuración ID {$config->id}: Asunto faltante";
                }
            }

            if (empty($problemas)) {
                $this->info('   ✅ No se encontraron problemas de integridad');
            } else {
                $this->warn('   ⚠️ Problemas encontrados:');
                foreach ($problemas as $problema) {
                    $this->line("      • {$problema}");
                }
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error verificando integridad: ' . $e->getMessage());
        }

        $this->line('');
    }
}
