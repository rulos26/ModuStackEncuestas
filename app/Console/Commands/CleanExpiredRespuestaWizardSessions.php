<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CleanExpiredRespuestaWizardSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'respuesta-wizard:clean-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia sesiones de wizard de respuestas expiradas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Limpiando sesiones de wizard de respuestas expiradas...');

        try {
            // Obtener todas las sesiones que contengan wizard_encuesta_id
            $sessions = DB::table('sessions')
                ->where('payload', 'like', '%wizard_encuesta_id%')
                ->where('payload', 'like', '%wizard_respuestas_count%')
                ->get();

            $cleanedCount = 0;

            foreach ($sessions as $session) {
                $payload = unserialize(base64_decode($session->payload));

                // Verificar si la sesión tiene datos del wizard de respuestas
                if (isset($payload['wizard_encuesta_id']) && isset($payload['wizard_respuestas_count'])) {
                    // Verificar si la sesión es antigua (más de 2 horas)
                    $sessionAge = time() - $session->last_activity;
                    if ($sessionAge > 7200) { // 2 horas en segundos
                        DB::table('sessions')->where('id', $session->id)->delete();
                        $cleanedCount++;
                    }
                }
            }

            $this->info("Se limpiaron {$cleanedCount} sesiones de wizard de respuestas expiradas.");

            Log::info('Sesiones de wizard de respuestas limpiadas', [
                'cleaned_count' => $cleanedCount
            ]);

        } catch (\Exception $e) {
            $this->error('Error limpiando sesiones: ' . $e->getMessage());
            Log::error('Error limpiando sesiones de wizard de respuestas', [
                'error' => $e->getMessage()
            ]);
        }

        return 0;
    }
}
