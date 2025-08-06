<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class ConfigurarEmailReal extends Command
{
    protected $signature = 'configurar:email-real {--email= : Email de destino para pruebas}';
    protected $description = 'Configura el envío de correos reales usando Gmail';

    public function handle()
    {
        $emailDestino = $this->option('email') ?: 'rulos26@gmail.com';

        $this->info('📧 CONFIGURANDO ENVÍO DE CORREOS REALES');
        $this->line('');

        try {
            // Configurar Gmail SMTP
            $this->configurarGmailSMTP();

            // Crear configuración de prueba
            $this->crearConfiguracionPrueba($emailDestino);

            // Probar envío
            $this->probarEnvio($emailDestino);

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }

    private function configurarGmailSMTP()
    {
        $this->info('🔧 Configurando Gmail SMTP...');

        // Configuración para Gmail
        $configuracion = [
            'default' => 'smtp',
            'mailers' => [
                'smtp' => [
                    'transport' => 'smtp',
                    'host' => 'smtp.gmail.com',
                    'port' => 587,
                    'encryption' => 'tls',
                    'username' => 'rulos26@gmail.com', // Cambiar por tu email
                    'password' => 'tu_app_password', // Cambiar por tu contraseña de aplicación
                    'timeout' => null,
                    'local_domain' => 'rulossoluciones.com',
                ],
            ],
        ];

        // Guardar configuración temporal
        Config::set('mail', $configuracion);

        $this->info('✅ Configuración SMTP actualizada');
        $this->warn('⚠️ IMPORTANTE: Necesitas configurar tu email y contraseña de aplicación en el código');
    }

    private function crearConfiguracionPrueba($emailDestino)
    {
        $this->info('📝 Creando configuración de prueba...');

        // Crear configuración de prueba
        $configuracion = new \App\Models\ConfiguracionEnvio();
        $configuracion->empresa_id = 1;
        $configuracion->encuesta_id = 1;
        $configuracion->tipo_envio = 'programado';
        $configuracion->estado_programacion = 'pendiente';
        $configuracion->activo = true;
        $configuracion->fecha_envio = now()->format('Y-m-d');
        $configuracion->hora_envio = now()->addMinutes(1)->format('H:i');
        $configuracion->tipo_destinatario = 'empleados';
        $configuracion->asunto = 'PRUEBA - Cron Job Funcionando - ' . now()->format('H:i:s');
        $configuracion->cuerpo_mensaje = 'Esta es una prueba del Cron Job funcionando correctamente.';
        $configuracion->nombre_remitente = 'Sistema ModuStack';
        $configuracion->correo_remitente = 'sistema@rulossoluciones.com';
        $configuracion->correo_prueba = $emailDestino;
        $configuracion->modo_prueba = true;
        $configuracion->save();

        $this->info("✅ Configuración de prueba creada (ID: {$configuracion->id})");
        $this->info("📧 Email de destino: {$emailDestino}");
    }

    private function probarEnvio($emailDestino)
    {
        $this->info('🚀 Probando envío de correo...');

        try {
            // Ejecutar el Cron Job manualmente
            $this->call('encuestas:verificar-envio-programado');

            $this->info('✅ Cron Job ejecutado');
            $this->info('📧 Verifica tu email en: ' . $emailDestino);

        } catch (\Exception $e) {
            $this->error("❌ Error en envío: " . $e->getMessage());
        }
    }
}
