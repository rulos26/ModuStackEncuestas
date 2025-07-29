<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use App\Models\Encuesta;
use Exception;

class DebugDashboardEncuesta extends Command
{
    protected $signature = 'debug:dashboard-encuesta {encuesta_id} {--user_id=} {--debug}';
    protected $description = 'Debug completo del dashboard de encuesta';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');
        $userId = $this->option('user_id');
        $debug = $this->option('debug');

        $this->info("🔍 DEBUG COMPLETO DEL DASHBOARD DE ENCUESTA");
        $this->line('');

        try {
            // 1. Verificar encuesta
            $this->verificarEncuesta($encuestaId);

            // 2. Verificar usuario
            $this->verificarUsuario($userId);

            // 3. Verificar permisos
            $this->verificarPermisos($encuestaId, $userId);

            // 4. Verificar estado
            $this->verificarEstado($encuestaId);

            // 5. Verificar rutas
            $this->verificarRutas($encuestaId);

            // 6. Simular acceso al dashboard
            $this->simularAccesoDashboard($encuestaId);

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante el debug: " . $e->getMessage());

            if ($debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }

    private function verificarEncuesta($encuestaId)
    {
        $this->info("📋 VERIFICANDO ENCUESTA:");

        $encuesta = Encuesta::find($encuestaId);

        if (!$encuesta) {
            $this->error("   ❌ Encuesta con ID {$encuestaId} no encontrada");
            return;
        }

        $this->line("   ✅ Encuesta encontrada: '{$encuesta->titulo}'");
        $this->line("   - ID: {$encuesta->id}");
        $this->line("   - Estado: {$encuesta->estado}");
        $this->line("   - User ID: {$encuesta->user_id}");
        $this->line("   - Enviar por correo: " . ($encuesta->enviar_por_correo ? 'Sí' : 'No'));
        $this->line("   - Envío masivo activado: " . ($encuesta->envio_masivo_activado ? 'Sí' : 'No'));
        $this->line("   - Validación completada: " . ($encuesta->validacion_completada ? 'Sí' : 'No'));
        $this->line("   - Preguntas: {$encuesta->preguntas()->count()}");
        $this->line('');
    }

    private function verificarUsuario($userId)
    {
        $this->info("👤 VERIFICANDO USUARIO:");

        $usuarioAutenticado = Auth::id();
        $this->line("   - Usuario autenticado: " . ($usuarioAutenticado ?? 'No autenticado'));

        if ($userId) {
            $this->line("   - User ID especificado: {$userId}");
        } else {
            $this->line("   - User ID especificado: No especificado");
        }

        $this->line('');
    }

    private function verificarPermisos($encuestaId, $userId)
    {
        $this->info("🔐 VERIFICANDO PERMISOS:");

        $encuesta = Encuesta::find($encuestaId);
        $usuarioAutenticado = Auth::id();
        $userIdFinal = $userId ?? $usuarioAutenticado;

        $this->line("   - User ID de encuesta: {$encuesta->user_id}");
        $this->line("   - User ID para verificación: {$userIdFinal}");
        $this->line("   - ¿Coinciden?: " . ($encuesta->user_id == $userIdFinal ? 'Sí' : 'No'));

        if ($encuesta->user_id != $userIdFinal) {
            $this->warn("   ⚠️  POSIBLE PROBLEMA: Los user_id no coinciden");
            $this->line("   💡 Solución: Ejecutar 'encuesta:corregir-user-id {$encuestaId} --user_id={$userIdFinal}'");
        } else {
            $this->line("   ✅ Los user_id coinciden correctamente");
        }

        $this->line('');
    }

    private function verificarEstado($encuestaId)
    {
        $this->info("📊 VERIFICANDO ESTADO:");

        $encuesta = Encuesta::find($encuestaId);

        $this->line("   - Estado actual: {$encuesta->estado}");
        $this->line("   - ¿Puede enviarse masivamente?: " . ($encuesta->puedeEnviarseMasivamente() ? 'Sí' : 'No'));
        $this->line("   - ¿Puede avanzar a envío?: " . ($encuesta->puedeAvanzarA('envio') ? 'Sí' : 'No'));

        // Verificar condiciones para envío masivo
        $condiciones = [
            'enviar_por_correo' => $encuesta->enviar_por_correo,
            'envio_masivo_activado' => $encuesta->envio_masivo_activado,
            'estado_borrador' => $encuesta->estado === 'borrador',
            'validacion_completada' => $encuesta->validacion_completada
        ];

        $this->line("   - Condiciones para envío masivo:");
        foreach ($condiciones as $condicion => $valor) {
            $icono = $valor ? '✅' : '❌';
            $this->line("     {$icono} {$condicion}: " . ($valor ? 'Sí' : 'No'));
        }

        $this->line('');
    }

    private function verificarRutas($encuestaId)
    {
        $this->info("🛣️  VERIFICANDO RUTAS:");

        $rutas = [
            'encuestas.seguimiento.dashboard' => "/encuestas/{$encuestaId}/seguimiento",
            'encuestas.seguimiento.actualizar' => "/encuestas/{$encuestaId}/seguimiento/actualizar",
            'encuestas.seguimiento.pausar' => "/encuestas/{$encuestaId}/seguimiento/pausar",
            'encuestas.seguimiento.reanudar' => "/encuestas/{$encuestaId}/seguimiento/reanudar",
            'encuestas.seguimiento.cancelar' => "/encuestas/{$encuestaId}/seguimiento/cancelar"
        ];

        foreach ($rutas as $nombre => $url) {
            $this->line("   - {$nombre}: {$url}");
        }

        $this->line('');
    }

    private function simularAccesoDashboard($encuestaId)
    {
        $this->info("🎮 SIMULANDO ACCESO AL DASHBOARD:");

        $encuesta = Encuesta::find($encuestaId);
        $usuarioAutenticado = Auth::id();

        $this->line("   - Intentando acceder como usuario: " . ($usuarioAutenticado ?? 'No autenticado'));
        $this->line("   - User ID de encuesta: {$encuesta->user_id}");

        if ($encuesta->user_id !== $usuarioAutenticado) {
            $this->error("   ❌ ACCESO DENEGADO: Los user_id no coinciden");
            $this->line("   💡 El controlador redirigirá a encuestas.index");
        } else {
            $this->line("   ✅ ACCESO PERMITIDO: Los user_id coinciden");
            $this->line("   💡 El dashboard debería cargar correctamente");
        }

        $this->line('');
        $this->info("🎯 RESUMEN DEL DEBUG:");
        $this->line("   - Encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");
        $this->line("   - Estado: {$encuesta->estado}");
        $this->line("   - User ID encuesta: {$encuesta->user_id}");
        $this->line("   - User ID autenticado: " . ($usuarioAutenticado ?? 'No autenticado'));
        $this->line("   - ¿Acceso permitido?: " . ($encuesta->user_id === $usuarioAutenticado ? 'Sí' : 'No'));
    }
}
