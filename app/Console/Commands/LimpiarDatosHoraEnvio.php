<?php

namespace App\Console\Commands;

use App\Models\ConfiguracionEnvio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LimpiarDatosHoraEnvio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'limpiar:hora-envio {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia los datos mal formateados de hora_envio en configuracion_envios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Limpiando datos mal formateados de hora_envio...');

        $dryRun = $this->option('dry-run');

        try {
            // Verificar conexión primero
            DB::connection()->getPdo();

            // Obtener configuraciones con hora_envio mal formateada
            $configuraciones = ConfiguracionEnvio::where('tipo_envio', 'programado')
                ->whereNotNull('hora_envio')
                ->get();

            $this->info("📊 Encontradas {$configuraciones->count()} configuraciones para revisar");

            $corregidas = 0;
            $errores = 0;

            foreach ($configuraciones as $config) {
                $horaOriginal = $config->hora_envio;

                $this->line("🔍 Configuración ID: {$config->id}");
                $this->line("   Hora original: {$horaOriginal}");

                // Verificar si la hora está mal formateada (contiene fecha duplicada)
                if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} \d{2}:\d{2}:\d{2}$/', $horaOriginal)) {
                    // Extraer solo la hora (última parte)
                    $partes = explode(' ', $horaOriginal);
                    $horaCorregida = end($partes);

                    $this->line("   ❌ Hora mal formateada detectada");
                    $this->line("   ✅ Hora corregida: {$horaCorregida}");

                    if (!$dryRun) {
                        try {
                            $config->update(['hora_envio' => '2000-01-01 ' . $horaCorregida]);
                            $corregidas++;
                            $this->info("   ✅ Corregida");
                        } catch (\Exception $e) {
                            $errores++;
                            $this->error("   ❌ Error al corregir: " . $e->getMessage());
                        }
                    } else {
                        $this->info("   🔍 (DRY RUN) Se corregiría a: 2000-01-01 {$horaCorregida}");
                    }
                } else {
                    $this->line("   ✅ Hora correcta");
                }

                $this->line('');
            }

            if ($dryRun) {
                $this->info("🔍 DRY RUN completado. Se corregirían {$corregidas} configuraciones.");
            } else {
                $this->info("✅ Limpieza completada:");
                $this->info("   📊 Configuraciones corregidas: {$corregidas}");
                $this->info("   ❌ Errores: {$errores}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error durante la limpieza: " . $e->getMessage());
            return 1;
        }
    }
}
