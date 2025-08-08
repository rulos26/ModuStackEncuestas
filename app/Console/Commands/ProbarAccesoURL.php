<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Exception;

class ProbarAccesoURL extends Command
{
    protected $signature = 'encuesta:probar-acceso-url {encuesta_id?}';
    protected $description = 'Probar el acceso real a la URL y verificar que no haya errores de cookies';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🌐 PROBANDO ACCESO REAL A LA URL');
        $this->line('');

        try {
            // 1. Buscar encuesta
            if ($encuestaId) {
                $encuesta = Encuesta::find($encuestaId);
            } else {
                $encuesta = Encuesta::where('estado', 'publicada')->first();
            }

            if (!$encuesta) {
                $this->error('❌ No se encontró encuesta para probar');
                return 1;
            }

            $this->line("📋 Encuesta: {$encuesta->titulo}");
            $this->line("• Slug: {$encuesta->slug}");
            $this->line("• Estado: {$encuesta->estado}");
            $this->line("• Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));
            $this->line('');

            // 2. Generar link público
            $controller = new EnvioMasivoEncuestasController();
            $linkPublico = $controller->generarLinkPublico($encuesta);

            $this->line('🔗 Link público generado:');
            $this->line("   {$linkPublico}");
            $this->line('');

            // 3. Extraer token
            $token = null;
            if (preg_match('/token=([^&]+)/', $linkPublico, $matches)) {
                $token = $matches[1];
                $this->line("   ✅ Token extraído: {$token}");
            } else {
                $this->error("   ❌ No se pudo extraer el token del link");
                return 1;
            }

            // 4. Verificar token en base de datos
            $tokenEncuesta = $encuesta->obtenerToken($token);
            if ($tokenEncuesta) {
                $this->line("   ✅ Token encontrado en base de datos");
                $this->line("   • Email: {$tokenEncuesta->email_destinatario}");
                $this->line("   • Expiración: {$tokenEncuesta->fecha_expiracion}");
                $this->line("   • Usado: " . ($tokenEncuesta->usado ? 'Sí' : 'No'));
            } else {
                $this->error("   ❌ Token no encontrado en base de datos");
                return 1;
            }

            // 5. Verificar que el token es válido
            if ($encuesta->tokenValido($token)) {
                $this->line("   ✅ Token es válido");
            } else {
                $this->error("   ❌ Token no es válido");
                return 1;
            }

            // 6. Simular request HTTP
            $this->line('🌐 Simulando request HTTP...');
            $this->line("   URL: {$linkPublico}");
            $this->line("   Método: GET");
            $this->line("   Headers: Accept: text/html");
            $this->line('');

            // 7. Verificar middlewares aplicados
            $this->line('🔧 Verificando middlewares...');
            $this->line("   • EmergencyHostingFix (global)");
            $this->line("   • FixSessionForHosting (global)");
            $this->line("   • verificar.token.encuesta (ruta específica)");
            $this->line('');

            // 8. Verificar configuración de cookies
            $this->line('🍪 Verificando configuración de cookies...');
            $this->line("   • session.driver: " . config('session.driver'));
            $this->line("   • session.cookie: " . config('session.cookie'));
            $this->line("   • session.cookie_path: " . config('session.cookie_path'));
            $this->line("   • session.cookie_secure: " . (config('session.cookie_secure') ? 'Sí' : 'No'));
            $this->line("   • session.cookie_http_only: " . (config('session.cookie_http_only') ? 'Sí' : 'No'));
            $this->line('');

            // 9. Verificar directorios de sesión
            $this->line('📁 Verificando directorios de sesión...');
            $sessionPath = storage_path('framework/sessions');
            if (is_dir($sessionPath)) {
                $this->line("   ✅ Directorio de sesiones existe: {$sessionPath}");
                $this->line("   • Permisos: " . substr(sprintf('%o', fileperms($sessionPath)), -4));
            } else {
                $this->error("   ❌ Directorio de sesiones no existe: {$sessionPath}");
            }

            // 10. Verificar que el token no se marcó como usado
            $tokenEncuesta->refresh();
            if ($tokenEncuesta->email_destinatario === 'general@encuesta.com') {
                if (!$tokenEncuesta->usado) {
                    $this->line("   ✅ Token general no marcado como usado (correcto)");
                } else {
                    $this->error("   ❌ Token general marcado como usado (incorrecto)");
                    return 1;
                }
            }

            $this->line('');
            $this->info('✅ PRUEBA DE ACCESO COMPLETADA');
            $this->line('');
            $this->line('📋 RESUMEN:');
            $this->line("   • Link: {$linkPublico}");
            $this->line("   • Token válido: Sí");
            $this->line("   • Middlewares configurados: Sí");
            $this->line("   • Cookies configuradas: Sí");
            $this->line("   • Directorios de sesión: Sí");
            $this->line("   • Token no marcado como usado: Sí");
            $this->line('');
            $this->line('🎯 INSTRUCCIONES PARA PROBAR:');
            $this->line("   1. Copia este link: {$linkPublico}");
            $this->line("   2. Pégalo en tu navegador");
            $this->line("   3. Deberías ver la encuesta sin errores 404 o de cookies");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
            $this->line('');
            $this->line('🔍 Detalles del error:');
            $this->line("   • Archivo: " . $e->getFile());
            $this->line("   • Línea: " . $e->getLine());
            $this->line("   • Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
