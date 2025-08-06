<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use Carbon\Carbon;

class VerificarConfiguracionesPendientes extends Command
{
    protected $signature = 'verificar:configuraciones-pendientes';
    protected $description = 'Verifica el estado de las configuraciones pendientes y por qué no se han actualizado';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO CONFIGURACIONES PENDIENTES');
        $this->line('');

        // Obtener todas las configuraciones programadas
        $configuraciones = ConfiguracionEnvio::where('tipo_envio', 'programado')->get();

        $this->info("📊 Total configuraciones programadas: {$configuraciones->count()}");
        $this->line('');

        foreach ($configuraciones as $config) {
            $this->analizarConfiguracion($config);
        }

        // Verificar por qué no se han actualizado
        $this->verificarPorQueNoSeActualizan();
    }

    private function analizarConfiguracion($config)
    {
        $this->info("📋 Configuración ID: {$config->id}");
        $this->line("   📧 Asunto: {$config->asunto}");
        $this->line("   📅 Fecha: {$config->fecha_envio}");
        $this->line("   🕐 Hora: {$config->hora_envio}");
        $this->line("   📊 Estado: {$config->estado_programacion}");
        $this->line("   ✅ Activo: " . ($config->activo ? 'SÍ' : 'NO'));

        try {
            $fechaHora = $config->fecha_envio . ' ' . $config->hora_envio;
            $fechaEnvio = Carbon::parse($fechaHora);
            $ahora = now();

            $this->line("   🕐 Fecha/Hora combinada: {$fechaHora}");
            $this->line("   ⏰ ¿Es momento de enviar?: " . ($fechaEnvio <= $ahora ? '✅ SÍ' : '⏳ NO'));

            if ($fechaEnvio <= $ahora && $config->estado_programacion === 'pendiente') {
                $this->warn("   ⚠️ Esta configuración debería haberse ejecutado ya");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error en fecha: {$e->getMessage()}");
        }

        $this->line('');
    }

    private function verificarPorQueNoSeActualizan()
    {
        $this->info('🔍 ANÁLISIS: ¿Por qué no se han actualizado?');
        $this->line('');

        // 1. Verificar si el Cron Job se ha ejecutado
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            $patron = '/verificar-envio-programado/';
            $matches = preg_match_all($patron, $logs);

            $this->line("📊 Veces que se ha ejecutado el comando: {$matches}");

            if ($matches == 0) {
                $this->warn("⚠️ El Cron Job no se ha ejecutado automáticamente");
                $this->line("   💡 Posibles causas:");
                $this->line("   • El Cron Job no está configurado en el hosting");
                $this->line("   • La ruta del comando es incorrecta");
                $this->line("   • El hosting no permite Cron Jobs");
            }
        }

        // 2. Verificar configuraciones que deberían ejecutarse
        $configuracionesPendientes = ConfiguracionEnvio::where('tipo_envio', 'programado')
            ->where('estado_programacion', 'pendiente')
            ->where('activo', true)
            ->get();

        $this->line('');
        $this->info("📈 Configuraciones pendientes que deberían ejecutarse: {$configuracionesPendientes->count()}");

        foreach ($configuracionesPendientes as $config) {
            try {
                $fechaHora = $config->fecha_envio . ' ' . $config->hora_envio;
                $fechaEnvio = Carbon::parse($fechaHora);
                $ahora = now();

                if ($fechaEnvio <= $ahora) {
                    $this->warn("   ⚠️ ID {$config->id}: Debería haberse ejecutado en {$fechaHora}");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ ID {$config->id}: Error en fecha - {$e->getMessage()}");
            }
        }

        // 3. Recomendaciones
        $this->line('');
        $this->info('💡 RECOMENDACIONES:');
        $this->line('   1. Verificar que el Cron Job esté configurado en el hosting');
        $this->line('   2. Ejecutar manualmente: php artisan encuestas:verificar-envio-programado');
        $this->line('   3. Verificar logs del hosting para errores');
        $this->line('   4. Contactar al proveedor de hosting si no funciona');
    }
}
