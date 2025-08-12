<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CleanExpiredLogicaWizardSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:clean-logica-sessions {--days=1 : Días de antigüedad para considerar expiradas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpiar sesiones expiradas del wizard de lógica de preguntas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Limpiando sesiones del wizard de lógica expiradas desde hace {$days} días...");

        try {
            // Limpiar sesiones de la base de datos
            $deletedSessions = DB::table('sessions')
                ->where('last_activity', '<', $cutoffDate->timestamp)
                ->where(function ($query) {
                    $query->where('payload', 'like', '%wizard_encuesta_id%')
                          ->where('payload', 'like', '%wizard_pregunta_index%')
                          ->where('payload', 'like', '%wizard_logica_count%');
                })
                ->delete();

            $this->info("Se eliminaron {$deletedSessions} sesiones expiradas del wizard de lógica.");

            // Limpiar cookies expiradas (esto es más complejo, solo informamos)
            $this->info("Recuerda que las cookies se limpian automáticamente por el navegador.");

            $this->info('Limpieza completada exitosamente.');

        } catch (\Exception $e) {
            $this->error("Error durante la limpieza: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
