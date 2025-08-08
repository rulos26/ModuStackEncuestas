<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Exception;

class VerificarLinkPublico extends Command
{
    protected $signature = 'encuesta:verificar-link {encuesta_id?}';
    protected $description = 'Verificar que el link público funciona correctamente';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🔗 VERIFICANDO LINK PÚBLICO');
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

            // 3. Extraer y verificar token
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

            // 6. Verificar que la URL es correcta
            $urlParts = parse_url($linkPublico);
            $path = $urlParts['path'] ?? '';
            $query = $urlParts['query'] ?? '';

            $this->line('🌐 Verificando estructura de URL...');
            $this->line("   • Path: {$path}");
            $this->line("   • Query: {$query}");
            $this->line("   • Formato esperado: /publica/{slug}?token={token}");

            // Verificar que el path contiene /publica/ y el query contiene token=
            if (strpos($path, '/publica/') !== false && strpos($query, 'token=') === 0) {
                $this->line("   ✅ Estructura de URL correcta");
            } else {
                $this->error("   ❌ Estructura de URL incorrecta");
                return 1;
            }

            // 7. Verificar que el slug coincide (considerando subdirectorios)
            $pathParts = explode('/', trim($path, '/'));
            $slugIndex = array_search('publica', $pathParts);
            if ($slugIndex !== false && isset($pathParts[$slugIndex + 1])) {
                $actualSlug = $pathParts[$slugIndex + 1];
                if ($actualSlug === $encuesta->slug) {
                    $this->line("   ✅ Slug de encuesta correcto: {$actualSlug}");
                } else {
                    $this->error("   ❌ Slug de encuesta incorrecto");
                    $this->line("   • Esperado: {$encuesta->slug}");
                    $this->line("   • Encontrado: {$actualSlug}");
                    return 1;
                }
            } else {
                $this->error("   ❌ No se pudo extraer el slug del path");
                return 1;
            }

            // 8. Verificar que el token no se marca como usado (para tokens generales)
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
            $this->info('✅ VERIFICACIÓN COMPLETADA');
            $this->line('');
            $this->line('📋 RESUMEN:');
            $this->line("   • Link generado correctamente: Sí");
            $this->line("   • Token válido: Sí");
            $this->line("   • Estructura de URL correcta: Sí");
            $this->line("   • Slug correcto: Sí");
            $this->line("   • Token no marcado como usado: Sí");
            $this->line('');
            $this->line('🎯 INSTRUCCIONES PARA PROBAR:');
            $this->line("   1. Copia este link: {$linkPublico}");
            $this->line("   2. Pégalo en tu navegador");
            $this->line("   3. Deberías ver la encuesta sin errores 404");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la verificación: ' . $e->getMessage());
            return 1;
        }
    }
}
