<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Models\BloqueEnvio;
use App\Models\TokenEncuesta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProbarSistemaEncuestas extends Command
{
    protected $signature = 'encuestas:probar-sistema {encuesta_id}';
    protected $description = 'Prueba completa del sistema de encuestas';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info("=== PRUEBA COMPLETA DEL SISTEMA DE ENCUESTAS ===");
        $this->info("Encuesta ID: {$encuestaId}");

        try {
            $encuesta = Encuesta::findOrFail($encuestaId);

            $this->probarEncuesta($encuesta);
            $this->probarBloquesEnvio($encuesta);
            $this->probarTokensAcceso($encuesta);
            $this->probarEstadisticas($encuesta);

            $this->info("✅ Todas las pruebas completadas exitosamente");
            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error en las pruebas: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Probar funcionalidad básica de la encuesta
     */
    private function probarEncuesta(Encuesta $encuesta)
    {
        $this->info("\n📋 PRUEBA DE ENCUESTA:");
        $this->info("   • Título: {$encuesta->titulo}");
        $this->info("   • Estado: {$encuesta->estado}");
        $this->info("   • Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));
        $this->info("   • Número de encuestas: {$encuesta->numero_encuestas}");
        $this->info("   • Enviadas: {$encuesta->encuestas_enviadas}");
        $this->info("   • Respondidas: {$encuesta->encuestas_respondidas}");
        $this->info("   • Pendientes: {$encuesta->encuestas_pendientes}");

        // Probar métodos
        $this->info("   • Puede enviarse masivamente: " . ($encuesta->puedeEnviarseMasivamente() ? 'Sí' : 'No'));
        $this->info("   • Está disponible: " . ($encuesta->estaDisponible() ? 'Sí' : 'No'));
        $this->info("   • Envío en progreso: " . ($encuesta->envioEnProgreso() ? 'Sí' : 'No'));
        $this->info("   • Envío completado: " . ($encuesta->envioCompletado() ? 'Sí' : 'No'));
    }

    /**
     * Probar sistema de bloques de envío
     */
    private function probarBloquesEnvio(Encuesta $encuesta)
    {
        $this->info("\n📦 PRUEBA DE BLOQUES DE ENVÍO:");

        // Crear bloques de prueba
        $this->info("   • Creando bloques de envío...");
        $encuesta->crearBloquesEnvio(5); // 5 minutos entre bloques

        $bloques = $encuesta->obtenerBloquesEnvio();
        $this->info("   • Bloques creados: {$bloques->count()}");

        foreach ($bloques as $bloque) {
            $this->info("     - Bloque {$bloque->numero_bloque}: {$bloque->cantidad_correos} correos, {$bloque->estado}");
        }

        // Probar siguiente bloque
        $siguienteBloque = $encuesta->obtenerSiguienteBloque();
        if ($siguienteBloque) {
            $this->info("   • Siguiente bloque: {$siguienteBloque->numero_bloque}");
        } else {
            $this->info("   • No hay bloques pendientes");
        }

        // Probar estadísticas de bloques
        $stats = $encuesta->obtenerEstadisticasEnvioDetalladas();
        $this->info("   • Total bloques: {$stats['total_bloques']}");
        $this->info("   • Bloques enviados: {$stats['bloques_enviados']}");
        $this->info("   • Bloques pendientes: {$stats['bloques_pendientes']}");
        $this->info("   • Progreso: {$stats['progreso_porcentaje']}%");
    }

    /**
     * Probar sistema de tokens de acceso
     */
    private function probarTokensAcceso(Encuesta $encuesta)
    {
        $this->info("\n🔑 PRUEBA DE TOKENS DE ACCESO:");

        // Generar token de prueba
        $emailPrueba = 'test@example.com';
        $tokenEncuesta = $encuesta->generarTokenParaDestinatario($emailPrueba, 24);

        $this->info("   • Token generado para: {$emailPrueba}");
        $this->info("   • Token: {$tokenEncuesta->token_acceso}");
        $this->info("   • Expira: {$tokenEncuesta->fecha_expiracion}");
        $this->info("   • Es válido: " . ($tokenEncuesta->esValido() ? 'Sí' : 'No'));

        // Probar enlace dinámico
        $enlace = $encuesta->generarEnlaceDinamico($emailPrueba, 24);
        $this->info("   • Enlace generado: " . substr($enlace, 0, 50) . "...");

        // Probar validación de token
        $esValido = $encuesta->tokenValido($tokenEncuesta->token_acceso);
        $this->info("   • Token válido en sistema: " . ($esValido ? 'Sí' : 'No'));

        // Probar renovación
        $nuevoEnlace = $encuesta->renovarEnlace($emailPrueba, 24);
        $this->info("   • Enlace renovado: " . substr($nuevoEnlace, 0, 50) . "...");

        // Contar tokens
        $tokens = $encuesta->tokensAcceso();
        $this->info("   • Total tokens: {$tokens->count()}");
    }

    /**
     * Probar estadísticas del sistema
     */
    private function probarEstadisticas(Encuesta $encuesta)
    {
        $this->info("\n📊 PRUEBA DE ESTADÍSTICAS:");

        // Estadísticas básicas
        $stats = $encuesta->calcularEstadisticasEnvio();
        $this->info("   • Total: {$stats['total']}");
        $this->info("   • Enviadas: {$stats['enviadas']}");
        $this->info("   • Respondidas: {$stats['respondidas']}");
        $this->info("   • Pendientes: {$stats['pendientes']}");
        $this->info("   • Porcentaje respuesta: {$stats['porcentaje_respuesta']}%");

        // Estadísticas detalladas
        $statsDetalladas = $encuesta->obtenerEstadisticasEnvioDetalladas();
        $this->info("   • Tiempo estimado: {$statsDetalladas['tiempo_estimado_minutos']} minutos");
        $this->info("   • Siguiente envío: " . ($statsDetalladas['siguiente_envio'] ? $statsDetalladas['siguiente_envio']->format('H:i:s') : 'N/A'));

        // Validar integridad
        $errores = $encuesta->validarIntegridad();
        if (empty($errores)) {
            $this->info("   • ✅ Integridad válida");
        } else {
            $this->warn("   • ⚠️ Errores de integridad:");
            foreach ($errores as $error) {
                $this->warn("     - {$error}");
            }
        }
    }
}
