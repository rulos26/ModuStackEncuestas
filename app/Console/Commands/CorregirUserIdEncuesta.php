<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Models\Encuesta;
use Exception;

class CorregirUserIdEncuesta extends Command
{
    protected $signature = 'encuesta:corregir-user-id {encuesta_id} {--user_id=} {--debug}';
    protected $description = 'Corrige el user_id de una encuesta para permitir acceso al dashboard';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $userId = $this->option('user_id');
        $debug = $this->option('debug');

        $this->info("🔧 CORRIGIENDO USER_ID DE ENCUESTA");
        $this->line('');

        try {
            // 1. Verificar encuesta
            $encuesta = Encuesta::find($encuestaId);
            if (!$encuesta) {
                $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                return 1;
            }

            $this->line("📋 ENCUESTA ENCONTRADA:");
            $this->line("   - Título: {$encuesta->titulo}");
            $this->line("   - User ID actual: {$encuesta->user_id}");
            $this->line("   - Usuario autenticado: " . (Auth::id() ?? 'No autenticado'));

            // 2. Determinar nuevo user_id
            if ($userId) {
                $nuevoUserId = $userId;
                $this->line("   - Nuevo User ID (especificado): {$nuevoUserId}");
            } else {
                $nuevoUserId = Auth::id() ?? 1;
                $this->line("   - Nuevo User ID (autenticado o default): {$nuevoUserId}");
            }

            // 3. Confirmar cambio
            if ($encuesta->user_id == $nuevoUserId) {
                $this->info("✅ El user_id ya es correcto");
                return 0;
            }

            $this->line('');
            $this->warn("⚠️  CAMBIO A REALIZAR:");
            $this->line("   - User ID actual: {$encuesta->user_id}");
            $this->line("   - User ID nuevo: {$nuevoUserId}");

            if (!$this->confirm('¿Deseas continuar con el cambio?')) {
                $this->info("❌ Operación cancelada");
                return 0;
            }

            // 4. Realizar cambio
            $encuesta->user_id = $nuevoUserId;
            $encuesta->save();

            $this->line('');
            $this->info("✅ CAMBIO REALIZADO:");
            $this->line("   - User ID actualizado: {$nuevoUserId}");
            $this->line("   - Encuesta: '{$encuesta->titulo}'");

            // 5. Verificar resultado
            $encuestaActualizada = Encuesta::find($encuestaId);
            $this->line('');
            $this->info("🔍 VERIFICACIÓN:");
            $this->line("   - User ID final: {$encuestaActualizada->user_id}");
            $this->line("   - ¿Coincide con autenticado?: " . ($encuestaActualizada->user_id == Auth::id() ? 'Sí' : 'No'));

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante la corrección: " . $e->getMessage());

            if ($debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }
}
