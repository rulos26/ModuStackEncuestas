<?php

namespace App\Console\Commands;

use App\Models\TokenEncuesta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LimpiarTokensExpirados extends Command
{
    protected $signature = 'encuestas:limpiar-tokens {--dias=7 : Días de antigüedad para eliminar}';
    protected $description = 'Limpia tokens de encuesta expirados y antiguos';

    public function handle()
    {
        $dias = $this->option('dias');
        $fechaLimite = now()->subDays($dias);

        $this->info("=== LIMPIEZA DE TOKENS EXPIRADOS ===");
        $this->info("Eliminando tokens más antiguos que: {$fechaLimite->format('Y-m-d H:i:s')}");

        try {
            // Contar tokens a eliminar
            $tokensAEliminar = TokenEncuesta::where('created_at', '<', $fechaLimite)->count();

            if ($tokensAEliminar === 0) {
                $this->info("No hay tokens antiguos para eliminar.");
                return 0;
            }

            $this->info("Encontrados {$tokensAEliminar} tokens para eliminar.");

            // Eliminar tokens antiguos
            $eliminados = TokenEncuesta::where('created_at', '<', $fechaLimite)->delete();

            $this->info("✅ Eliminados {$eliminados} tokens exitosamente.");

            // Estadísticas adicionales
            $totalTokens = TokenEncuesta::count();
            $tokensExpirados = TokenEncuesta::where('fecha_expiracion', '<', now())->count();
            $tokensUsados = TokenEncuesta::where('usado', true)->count();

            $this->info("📊 Estadísticas actuales:");
            $this->info("   • Total de tokens: {$totalTokens}");
            $this->info("   • Tokens expirados: {$tokensExpirados}");
            $this->info("   • Tokens usados: {$tokensUsados}");

            Log::info('Limpieza de tokens expirados completada', [
                'tokens_eliminados' => $eliminados,
                'fecha_limite' => $fechaLimite->toISOString(),
                'total_tokens' => $totalTokens,
                'tokens_expirados' => $tokensExpirados,
                'tokens_usados' => $tokensUsados
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Error durante la limpieza: ' . $e->getMessage());
            Log::error('Error limpiando tokens expirados', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
