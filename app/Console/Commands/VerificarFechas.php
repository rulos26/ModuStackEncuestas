<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\FechaHelper;
use Carbon\Carbon;

class VerificarFechas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fechas:verificar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar la configuración de fechas y zona horaria';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VERIFICACIÓN DE CONFIGURACIÓN DE FECHAS ===');
        
        // Información de zona horaria
        $this->line('');
        $this->info('📅 CONFIGURACIÓN DE ZONA HORARIA:');
        $this->line('Zona horaria configurada: ' . config('app.timezone'));
        $this->line('Zona horaria del sistema: ' . date_default_timezone_get());
        
        // Información de fechas
        $this->line('');
        $this->info('🕐 FECHAS ACTUALES:');
        $this->line('Fecha UTC: ' . Carbon::now()->utc()->format('Y-m-d H:i:s'));
        $this->line('Fecha local: ' . Carbon::now()->format('Y-m-d H:i:s'));
        $this->line('Fecha helper: ' . FechaHelper::hoyFormateada());
        
        // Información del helper
        $info = FechaHelper::getInfoZonaHoraria();
        $this->line('');
        $this->info('🔧 INFORMACIÓN DEL HELPER:');
        foreach ($info as $key => $value) {
            $this->line("$key: $value");
        }
        
        // Verificar validaciones
        $this->line('');
        $this->info('✅ PRUEBAS DE VALIDACIÓN:');
        
        $hoy = Carbon::now()->format('Y-m-d');
        $ayer = Carbon::now()->subDay()->format('Y-m-d');
        $manana = Carbon::now()->addDay()->format('Y-m-d');
        
        $this->line("Fecha de hoy ($hoy): " . (FechaHelper::esFechaInicioValida($hoy) ? '✅ Válida' : '❌ Inválida'));
        $this->line("Fecha de ayer ($ayer): " . (FechaHelper::esFechaInicioValida($ayer) ? '✅ Válida' : '❌ Inválida'));
        $this->line("Fecha de mañana ($manana): " . (FechaHelper::esFechaInicioValida($manana) ? '✅ Válida' : '❌ Inválida'));
        
        // Verificar fechas de fin
        $this->line('');
        $this->line("Fecha fin válida ($manana vs $hoy): " . (FechaHelper::esFechaFinValida($manana, $hoy) ? '✅ Válida' : '❌ Inválida'));
        $this->line("Fecha fin inválida ($ayer vs $hoy): " . (FechaHelper::esFechaFinValida($ayer, $hoy) ? '✅ Válida' : '❌ Inválida'));
        
        $this->line('');
        $this->info('✅ Verificación completada. Si hay problemas, revisa la configuración de zona horaria.');
        
        return 0;
    }
}
