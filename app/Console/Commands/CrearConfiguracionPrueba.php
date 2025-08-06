<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use Carbon\Carbon;

class CrearConfiguracionPrueba extends Command
{
    protected $signature = 'crear:configuracion-prueba {--minutos=2 : Minutos en el futuro para programar}';
    protected $description = 'Crea una configuración de prueba para verificar el Cron Job';

    public function handle()
    {
        $minutos = (int) $this->option('minutos');
        $fechaEnvio = now()->addMinutes($minutos);

        $this->info("🚀 CREANDO CONFIGURACIÓN DE PRUEBA");
        $this->info("📅 Programada para: {$fechaEnvio->format('Y-m-d H:i:s')}");
        $this->line('');

        try {
            // Crear configuración de prueba
            $configuracion = new ConfiguracionEnvio();
            $configuracion->empresa_id = 1;
            $configuracion->encuesta_id = 1;
            $configuracion->tipo_envio = 'programado';
            $configuracion->estado_programacion = 'pendiente';
            $configuracion->activo = true;
            $configuracion->fecha_envio = $fechaEnvio->format('Y-m-d');
            $configuracion->hora_envio = $fechaEnvio->format('H:i');
            $configuracion->tipo_destinatario = 'empleados';
            $configuracion->asunto = 'Prueba Cron Job - ' . now()->format('H:i:s');
            $configuracion->cuerpo_mensaje = 'Esta es una prueba del Cron Job automático.';
            $configuracion->nombre_remitente = 'Sistema de Pruebas';
            $configuracion->correo_remitente = 'pruebas@rulossoluciones.com';
            $configuracion->save();

            $this->info("✅ Configuración creada exitosamente");
            $this->info("📋 ID: {$configuracion->id}");
            $this->info("📅 Fecha: {$configuracion->fecha_envio}");
            $this->info("🕐 Hora: {$configuracion->hora_envio}");
            $this->info("📧 Asunto: {$configuracion->asunto}");
            $this->line('');

            $this->info("🔍 Para monitorear el estado:");
            $this->line("   php artisan monitorear:cron-job --ultimas=10");
            $this->line("   php artisan cron-job:estado-tiempo-real");
            $this->line('');

            $this->info("⏰ El Cron Job debería ejecutarse en {$minutos} minutos");
            $this->info("📊 Verifica que el estado cambie de 'pendiente' a 'en_proceso'");

        } catch (\Exception $e) {
            $this->error("❌ Error creando configuración: " . $e->getMessage());
        }
    }
}
