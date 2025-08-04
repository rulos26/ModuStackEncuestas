<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use Illuminate\Support\Facades\DB;

class CorregirConfiguracionesEnvio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'corregir:configuraciones-envio
                            {--configuracion-id= : ID de configuración específica}
                            {--tipo-destinatario=empleados : Tipo de destinatario por defecto}
                            {--dry-run : Solo mostrar qué se corregiría sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige configuraciones de envío con campos faltantes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 CORRIGIENDO CONFIGURACIONES DE ENVÍO');
        $this->line('');

        $configuracionId = $this->option('configuracion-id');
        $tipoDestinatario = $this->option('tipo-destinatario');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🧪 MODO DRY RUN - No se realizarán cambios reales');
            $this->line('');
        }

        // 1. Verificar conexión a base de datos
        $this->verificarConexionBD();

        // 2. Identificar configuraciones problemáticas
        $configuraciones = $this->identificarConfiguracionesProblematicas($configuracionId);

        if ($configuraciones->isEmpty()) {
            $this->info('✅ No se encontraron configuraciones que necesiten corrección');
            return;
        }

        // 3. Mostrar configuraciones que se van a corregir
        $this->mostrarConfiguracionesACorregir($configuraciones);

        // 4. Confirmar corrección
        if (!$dryRun && !$this->confirm('¿Deseas proceder con las correcciones?')) {
            $this->info('❌ Operación cancelada');
            return;
        }

        // 5. Aplicar correcciones
        $this->aplicarCorrecciones($configuraciones, $tipoDestinatario, $dryRun);

        $this->info('✅ CORRECCIÓN COMPLETADA');
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

    private function identificarConfiguracionesProblematicas($configuracionId)
    {
        $this->info('🔍 2. Identificando configuraciones problemáticas...');

        $query = ConfiguracionEnvio::with(['empresa', 'encuesta']);

        if ($configuracionId) {
            $query->where('id', $configuracionId);
        }

        // Buscar configuraciones programadas con campos faltantes
        $configuraciones = $query->where('tipo_envio', 'programado')
            ->where(function ($q) {
                $q->whereNull('tipo_destinatario')
                  ->orWhere('tipo_destinatario', '')
                  ->orWhereNull('numero_bloques')
                  ->orWhere('numero_bloques', 0);
            })
            ->get();

        $this->info("   📈 Configuraciones problemáticas encontradas: {$configuraciones->count()}");

        return $configuraciones;
    }

    private function mostrarConfiguracionesACorregir($configuraciones)
    {
        $this->info('📋 3. Configuraciones que se van a corregir:');

        foreach ($configuraciones as $config) {
            $this->line("   📋 Configuración ID: {$config->id}");
            $this->line("      Empresa: {$config->empresa->nombre}");
            $this->line("      Encuesta: {$config->encuesta->titulo}");
            $this->line("      Tipo destinatario actual: " . ($config->tipo_destinatario ?: '❌ VACÍO'));
            $this->line("      Número bloques actual: " . ($config->numero_bloques ?: '❌ VACÍO'));
            $this->line('');
        }
    }

    private function aplicarCorrecciones($configuraciones, $tipoDestinatario, $dryRun)
    {
        $this->info('🔧 4. Aplicando correcciones...');

        $corregidas = 0;
        $errores = [];

        foreach ($configuraciones as $config) {
            try {
                $cambios = [];

                // Corregir tipo_destinatario si está vacío
                if (empty($config->tipo_destinatario)) {
                    $cambios['tipo_destinatario'] = $tipoDestinatario;
                    $this->line("   ✅ Configuración ID {$config->id}: tipo_destinatario → '{$tipoDestinatario}'");
                }

                // Corregir numero_bloques si está vacío o es 0
                if (empty($config->numero_bloques) || $config->numero_bloques == 0) {
                    $cambios['numero_bloques'] = 1;
                    $this->line("   ✅ Configuración ID {$config->id}: numero_bloques → 1");
                }

                // Aplicar cambios si no es dry run
                if (!$dryRun && !empty($cambios)) {
                    $config->update($cambios);
                    $corregidas++;
                } elseif ($dryRun && !empty($cambios)) {
                    $corregidas++;
                }

            } catch (\Exception $e) {
                $errores[] = "Error corrigiendo configuración ID {$config->id}: " . $e->getMessage();
                $this->error("   ❌ Error corrigiendo configuración ID {$config->id}: " . $e->getMessage());
            }
        }

        if ($dryRun) {
            $this->info("   🧪 DRY RUN: Se corregirían {$corregidas} configuraciones");
        } else {
            $this->info("   ✅ Se corrigieron {$corregidas} configuraciones");
        }

        if (!empty($errores)) {
            $this->warn("   ⚠️ Errores encontrados:");
            foreach ($errores as $error) {
                $this->line("      • {$error}");
            }
        }

        $this->line('');
    }
}
