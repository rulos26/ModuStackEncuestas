<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Models\BloqueEnvio;
use App\Models\TokenEncuesta;
use App\Models\SentMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarEstadoSistema extends Command
{
    protected $signature = 'encuestas:verificar-estado {--encuesta_id=}';
    protected $description = 'Verifica el estado completo del sistema de encuestas';

    public function handle()
    {
        $encuestaId = $this->option('encuesta_id');

        $this->info("=== VERIFICACIÓN DEL ESTADO DEL SISTEMA ===");

        if ($encuestaId) {
            $this->verificarEncuestaEspecifica($encuestaId);
        } else {
            $this->verificarSistemaCompleto();
        }

        return 0;
    }

    /**
     * Verificar una encuesta específica
     */
    private function verificarEncuestaEspecifica(int $encuestaId)
    {
        $encuesta = Encuesta::find($encuestaId);

        if (!$encuesta) {
            $this->error("Encuesta con ID {$encuestaId} no encontrada.");
            return;
        }

        $this->info("📋 ENCUESTA: {$encuesta->titulo} (ID: {$encuesta->id})");
        $this->info("Estado: {$encuesta->estado}");
        $this->info("Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));
        $this->info("Envío masivo: " . ($encuesta->envio_masivo_activado ? 'Activado' : 'Desactivado'));
        $this->info("Validación completada: " . ($encuesta->validacion_completada ? 'Sí' : 'No'));

        // Estadísticas de envío
        $stats = $encuesta->calcularEstadisticasEnvio();
        $this->info("📊 ESTADÍSTICAS DE ENVÍO:");
        $this->info("   • Total: {$stats['total']}");
        $this->info("   • Enviadas: {$stats['enviadas']}");
        $this->info("   • Respondidas: {$stats['respondidas']}");
        $this->info("   • Pendientes: {$stats['pendientes']}");
        $this->info("   • Porcentaje respuesta: {$stats['porcentaje_respuesta']}%");

        // Bloques de envío
        $bloques = $encuesta->obtenerBloquesEnvio();
        $this->info("📦 BLOQUES DE ENVÍO ({$bloques->count()} total):");
        foreach ($bloques as $bloque) {
            $this->info("   • Bloque {$bloque->numero_bloque}: {$bloque->estado} ({$bloque->cantidad_correos} correos)");
        }

        // Tokens
        $tokens = $encuesta->tokensAcceso();
        $this->info("🔑 TOKENS DE ACCESO ({$tokens->count()} total):");
        $tokensValidos = $tokens->where('fecha_expiracion', '>', now())->where('usado', false)->count();
        $tokensUsados = $tokens->where('usado', true)->count();
        $tokensExpirados = $tokens->where('fecha_expiracion', '<', now())->count();
        $this->info("   • Válidos: {$tokensValidos}");
        $this->info("   • Usados: {$tokensUsados}");
        $this->info("   • Expirados: {$tokensExpirados}");

        // Correos enviados
        $correosEnviados = SentMail::where('encuesta_id', $encuestaId)->count();
        $this->info("📧 CORREOS ENVIADOS: {$correosEnviados}");
    }

    /**
     * Verificar el sistema completo
     */
    private function verificarSistemaCompleto()
    {
        $this->info("📊 ESTADÍSTICAS GENERALES DEL SISTEMA:");

        // Encuestas
        $totalEncuestas = Encuesta::count();
        $encuestasBorrador = Encuesta::where('estado', 'borrador')->count();
        $encuestasEnviadas = Encuesta::where('estado', 'enviada')->count();
        $encuestasPublicadas = Encuesta::where('estado', 'publicada')->count();
        $encuestasHabilitadas = Encuesta::where('habilitada', true)->count();

        $this->info("📋 ENCUESTAS:");
        $this->info("   • Total: {$totalEncuestas}");
        $this->info("   • Borrador: {$encuestasBorrador}");
        $this->info("   • Enviadas: {$encuestasEnviadas}");
        $this->info("   • Publicadas: {$encuestasPublicadas}");
        $this->info("   • Habilitadas: {$encuestasHabilitadas}");

        // Bloques de envío
        $totalBloques = BloqueEnvio::count();
        $bloquesPendientes = BloqueEnvio::where('estado', 'pendiente')->count();
        $bloquesEnProceso = BloqueEnvio::where('estado', 'en_proceso')->count();
        $bloquesEnviados = BloqueEnvio::where('estado', 'enviado')->count();
        $bloquesError = BloqueEnvio::where('estado', 'error')->count();

        $this->info("📦 BLOQUES DE ENVÍO:");
        $this->info("   • Total: {$totalBloques}");
        $this->info("   • Pendientes: {$bloquesPendientes}");
        $this->info("   • En proceso: {$bloquesEnProceso}");
        $this->info("   • Enviados: {$bloquesEnviados}");
        $this->info("   • Error: {$bloquesError}");

        // Tokens
        $totalTokens = TokenEncuesta::count();
        $tokensValidos = TokenEncuesta::where('fecha_expiracion', '>', now())->where('usado', false)->count();
        $tokensUsados = TokenEncuesta::where('usado', true)->count();
        $tokensExpirados = TokenEncuesta::where('fecha_expiracion', '<', now())->count();

        $this->info("🔑 TOKENS:");
        $this->info("   • Total: {$totalTokens}");
        $this->info("   • Válidos: {$tokensValidos}");
        $this->info("   • Usados: {$tokensUsados}");
        $this->info("   • Expirados: {$tokensExpirados}");

        // Correos enviados
        $totalCorreos = SentMail::count();
        $correosHoy = SentMail::whereDate('created_at', today())->count();
        $correosEstaSemana = SentMail::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        $this->info("📧 CORREOS ENVIADOS:");
        $this->info("   • Total: {$totalCorreos}");
        $this->info("   • Hoy: {$correosHoy}");
        $this->info("   • Esta semana: {$correosEstaSemana}");

        // Verificar problemas
        $this->verificarProblemas();
    }

    /**
     * Verificar problemas en el sistema
     */
    private function verificarProblemas()
    {
        $this->info("🔍 VERIFICACIÓN DE PROBLEMAS:");

        // Encuestas con bloques en error
        $bloquesConError = BloqueEnvio::where('estado', 'error')->count();
        if ($bloquesConError > 0) {
            $this->warn("⚠️  {$bloquesConError} bloques con errores");
        }

        // Tokens expirados
        $tokensExpirados = TokenEncuesta::where('fecha_expiracion', '<', now())->count();
        if ($tokensExpirados > 100) {
            $this->warn("⚠️  {$tokensExpirados} tokens expirados (considerar limpieza)");
        }

        // Encuestas sin validación
        $encuestasSinValidacion = Encuesta::where('validacion_completada', false)->where('estado', '!=', 'borrador')->count();
        if ($encuestasSinValidacion > 0) {
            $this->warn("⚠️  {$encuestasSinValidacion} encuestas sin validación completada");
        }

        // Bloques pendientes por mucho tiempo
        $bloquesPendientesAntiguos = BloqueEnvio::where('estado', 'pendiente')
            ->where('fecha_programada', '<', now()->subHours(1))
            ->count();
        if ($bloquesPendientesAntiguos > 0) {
            $this->warn("⚠️  {$bloquesPendientesAntiguos} bloques pendientes por más de 1 hora");
        }

        if ($bloquesConError == 0 && $tokensExpirados <= 100 && $encuestasSinValidacion == 0 && $bloquesPendientesAntiguos == 0) {
            $this->info("✅ Sistema funcionando correctamente");
        }
    }
}
