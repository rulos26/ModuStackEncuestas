<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use App\Jobs\EnviarCorreosProgramados;
use Illuminate\Support\Facades\Log;

class ProbarEnvioCorreos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'probar:envio-correos
                            {--configuracion-id= : ID de la configuración específica a probar}
                            {--test : Enviar solo correo de prueba}
                            {--force : Forzar envío sin verificar fecha/hora}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el envío real de correos programados';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('📧 INICIANDO PRUEBA DE ENVÍO DE CORREOS');
        $this->line('');

        $configuracionId = $this->option('configuracion-id');
        $esTest = $this->option('test');
        $forzar = $this->option('force');

        if ($configuracionId) {
            $this->probarConfiguracionEspecifica($configuracionId, $esTest, $forzar);
        } else {
            $this->probarTodasLasConfiguraciones($esTest, $forzar);
        }

        $this->info('✅ PRUEBA DE ENVÍO COMPLETADA');
    }

    private function probarConfiguracionEspecifica($configuracionId, $esTest, $forzar)
    {
        $this->info("🎯 Probando configuración específica ID: {$configuracionId}");

        $configuracion = ConfiguracionEnvio::with(['encuesta', 'empresa'])->find($configuracionId);

        if (!$configuracion) {
            $this->error("❌ Configuración ID {$configuracionId} no encontrada");
            return;
        }

        $this->mostrarInfoConfiguracion($configuracion);

        if ($esTest) {
            $this->enviarCorreoPrueba($configuracion);
        } else {
            $this->enviarCorreosReales($configuracion, $forzar);
        }
    }

    private function probarTodasLasConfiguraciones($esTest, $forzar)
    {
        $this->info('📋 Probando todas las configuraciones programadas');

        $query = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('activo', true)
            ->with(['encuesta', 'empresa']);

        if (!$forzar) {
            $query->where('estado_programacion', 'pendiente');
        }

        $configuraciones = $query->get();

        if ($configuraciones->isEmpty()) {
            $this->warn('⚠️ No hay configuraciones programadas para probar');
            return;
        }

        $this->info("📈 Encontradas {$configuraciones->count()} configuraciones para probar");

        foreach ($configuraciones as $configuracion) {
            $this->line('');
            $this->info("🎯 Probando configuración ID: {$configuracion->id}");
            $this->mostrarInfoConfiguracion($configuracion);

            if ($esTest) {
                $this->enviarCorreoPrueba($configuracion);
            } else {
                $this->enviarCorreosReales($configuracion, $forzar);
            }
        }
    }

    private function mostrarInfoConfiguracion($configuracion)
    {
        $this->line("   📋 Encuesta: {$configuracion->encuesta->titulo}");
        $this->line("   📋 Empresa: {$configuracion->empresa->nombre}");
        $this->line("   📋 Estado: {$configuracion->estado_programacion}");
        $this->line("   📋 Fecha/Hora: {$configuracion->fecha_envio} {$configuracion->hora_envio}");
        $this->line("   📋 Destinatarios: {$configuracion->tipo_destinatario}");
        $this->line("   📋 Correo remitente: {$configuracion->correo_remitente}");
    }

    private function enviarCorreoPrueba($configuracion)
    {
        $this->info('🧪 Enviando correo de prueba...');

        if (!$configuracion->correo_prueba) {
            $this->error('❌ No hay correo de prueba configurado');
            return;
        }

        try {
            // Crear una copia de la configuración para modo prueba
            $configuracionPrueba = $configuracion->replicate();
            $configuracionPrueba->modo_prueba = true;
            $configuracionPrueba->correo_prueba = $configuracion->correo_prueba;

            // Dispatch del job en modo prueba
            EnviarCorreosProgramados::dispatch($configuracionPrueba->id);

            $this->info("✅ Correo de prueba enviado a: {$configuracion->correo_prueba}");

        } catch (\Exception $e) {
            $this->error("❌ Error enviando correo de prueba: " . $e->getMessage());
            Log::error("Error en correo de prueba: " . $e->getMessage());
        }
    }

    private function enviarCorreosReales($configuracion, $forzar)
    {
        $this->info('📤 Enviando correos reales...');

        // Verificar si es momento de enviar
        if (!$forzar) {
            $fechaHoraEnvio = $configuracion->fecha_envio . ' ' . $configuracion->hora_envio;
            $fechaEnvio = \Carbon\Carbon::parse($fechaHoraEnvio);
            $ahora = now();

            if ($fechaEnvio > $ahora) {
                $this->warn("⏳ No es momento de enviar. Programado para: {$fechaEnvio->format('Y-m-d H:i:s')}");
                $this->warn("   Usa --force para forzar el envío");
                return;
            }
        }

        try {
            // Marcar como en proceso
            $configuracion->update(['estado_programacion' => 'en_proceso']);

            // Dispatch del job
            EnviarCorreosProgramados::dispatch($configuracion->id);

            $this->info("✅ Job de envío dispatchado para configuración ID: {$configuracion->id}");

        } catch (\Exception $e) {
            $this->error("❌ Error dispatchando job: " . $e->getMessage());
            Log::error("Error dispatchando job: " . $e->getMessage());

            // Revertir estado
            $configuracion->update(['estado_programacion' => 'pendiente']);
        }
    }
}
