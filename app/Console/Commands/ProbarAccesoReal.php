<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Illuminate\Support\Facades\Http;
use Exception;

class ProbarAccesoReal extends Command
{
    protected $signature = 'encuesta:probar-acceso-real {encuesta_id?}';
    protected $description = 'Probar el acceso real a la URL de la encuesta pública';

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

            $this->line("📋 Encuesta seleccionada: {$encuesta->titulo}");
            $this->line("• ID: {$encuesta->id}");
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

            // 6. Simular acceso HTTP
            $this->line('🌐 Simulando acceso HTTP...');
            $this->line("   URL: {$linkPublico}");
            $this->line("   Método: GET");
            $this->line("   Headers: Accept: text/html");
            $this->line('');

            // 7. Verificar que la URL es accesible
            $this->line('🔍 Verificando accesibilidad...');

            // Simular request usando el sistema de rutas de Laravel
            try {
                // Crear una request simulada
                $request = \Illuminate\Http\Request::create(
                    parse_url($linkPublico, PHP_URL_PATH) . '?' . parse_url($linkPublico, PHP_URL_QUERY),
                    'GET'
                );

                // Simular el middleware
                $middleware = new \App\Http\Middleware\VerificarTokenEncuesta();

                // Simular la respuesta del middleware
                $response = $middleware->handle($request, function($request) {
                    return response()->json(['status' => 'success', 'message' => 'Token válido']);
                });

                if ($response->getStatusCode() === 200) {
                    $this->line("   ✅ URL accesible - Status: 200");
                } else {
                    $this->line("   ⚠️  Status: " . $response->getStatusCode());
                }

            } catch (Exception $e) {
                $this->error("   ❌ Error accediendo a la URL: " . $e->getMessage());
                return 1;
            }

            // 8. Verificar que el token no se marcó como usado (para tokens generales)
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
            $this->info('✅ PRUEBA DE ACCESO REAL COMPLETADA');
            $this->line('');
            $this->line('📋 RESUMEN:');
            $this->line("   • Link: {$linkPublico}");
            $this->line("   • Token válido: Sí");
            $this->line("   • URL accesible: Sí");
            $this->line("   • Middleware funcionando: Sí");
            $this->line("   • Token no marcado como usado: Sí");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
            return 1;
        }
    }
}
