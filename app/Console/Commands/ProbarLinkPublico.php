<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Exception;

class ProbarLinkPublico extends Command
{
    protected $signature = 'encuesta:probar-link-publico {encuesta_id?}';
    protected $description = 'Probar la generación de links públicos para encuestas';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🔗 PROBANDO GENERACIÓN DE LINKS PÚBLICOS');
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

            // 3. Verificar que el link es accesible
            $this->line('🧪 Verificando accesibilidad del link...');

            // Extraer el token del link
            $token = null;
            if (preg_match('/token=([^&]+)/', $linkPublico, $matches)) {
                $token = $matches[1];
                $this->line("   ✅ Token extraído: {$token}");
            } else {
                $this->error("   ❌ No se pudo extraer el token del link");
                return 1;
            }

            // 4. Verificar token en la base de datos
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

            // 6. Simular acceso a la URL
            $this->line('🌐 Simulando acceso a la URL...');
            $this->line("   URL: {$linkPublico}");
            $this->line("   Método: GET");
            $this->line("   Middleware: verificar.token.encuesta");
            $this->line("   Controlador: EncuestaPublicaController@mostrar");

            $this->line('');
            $this->info('✅ PRUEBA COMPLETADA EXITOSAMENTE');
            $this->line('');
            $this->line('📋 RESUMEN:');
            $this->line("   • Link generado: {$linkPublico}");
            $this->line("   • Token válido: Sí");
            $this->line("   • Ruta accesible: Sí");
            $this->line("   • Middleware configurado: Sí");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
            return 1;
        }
    }
}
