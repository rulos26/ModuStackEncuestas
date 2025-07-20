<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserSession;
use Carbon\Carbon;

class CleanExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sessions:clean {--minutes=120 : Minutos de inactividad para considerar expirada}';

    /**
     * The console command description.
     */
    protected $description = 'Limpiar sesiones expiradas automáticamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timeoutMinutes = $this->option('minutes');
        $expiredTime = Carbon::now()->subMinutes($timeoutMinutes);

        $this->info("Buscando sesiones inactivas por más de {$timeoutMinutes} minutos...");

        $expiredSessions = UserSession::where('is_active', true)
            ->where('last_activity', '<', $expiredTime)
            ->get();

        if ($expiredSessions->isEmpty()) {
            $this->info('No se encontraron sesiones expiradas.');
            return 0;
        }

        $this->info("Se encontraron {$expiredSessions->count()} sesiones expiradas.");

        $bar = $this->output->createProgressBar($expiredSessions->count());
        $bar->start();

        foreach ($expiredSessions as $session) {
            $session->markAsInactive();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Se cerraron {$expiredSessions->count()} sesiones expiradas exitosamente.");

        return 0;
    }
}
