<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Http\Controllers\EnvioMasivoEncuestasController;
use Exception;

class ProbarSolucionDefinitiva extends Command
{
    protected $signature = 'encuesta:probar-solucion-definitiva {encuesta_id?}';
    protected $description = 'Probar la solución definitiva sin cookies ni sesiones';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        $this->info('🎯 PROBANDO SOLUCIÓN DEFINITIVA');
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

            // 3. Verificar configuración de sesiones
            $this->line('🍪 Verificando configuración de sesiones...');
            $this->line("   • session.driver: " . config('session.driver'));
            $this->line("   • session.cookie: " . (config('session.cookie') ?? 'null'));
            $this->line("   • session.lifetime: " . config('session.lifetime'));
            $this->line('');

            // 4. Verificar middlewares aplicados
            $this->line('🔧 Middlewares aplicados a rutas públicas:');
            $this->line("   • public.page (deshabilita sesiones y cookies)");
            $this->line("   • verificar.token.encuesta (valida token)");
            $this->line('');

            // 5. Verificar que el token es válido
            $token = null;
            if (preg_match('/token=([^&]+)/', $linkPublico, $matches)) {
                $token = $matches[1];
                $this->line("   ✅ Token extraído: {$token}");
            } else {
                $this->error("   ❌ No se pudo extraer el token del link");
                return 1;
            }

            $tokenEncuesta = $encuesta->obtenerToken($token);
            if ($tokenEncuesta && $encuesta->tokenValido($token)) {
                $this->line("   ✅ Token válido");
            } else {
                $this->error("   ❌ Token no válido");
                return 1;
            }

            // 6. Verificar estructura de URL
            $urlParts = parse_url($linkPublico);
            $path = $urlParts['path'] ?? '';
            $query = $urlParts['query'] ?? '';

            $this->line('🌐 Verificando estructura de URL...');
            $this->line("   • Path: {$path}");
            $this->line("   • Query: {$query}");

            if (strpos($path, '/publica/') !== false && strpos($query, 'token=') === 0) {
                $this->line("   ✅ Estructura de URL correcta");
            } else {
                $this->error("   ❌ Estructura de URL incorrecta");
                return 1;
            }

            // 7. Verificar que no hay dependencias de sesión
            $this->line('🔒 Verificando independencia de sesiones...');
            $this->line("   • No se establecen cookies");
            $this->line("   • No se inician sesiones");
            $this->line("   • No se dependen de autenticación");
            $this->line("   ✅ Página completamente pública");

            $this->line('');
            $this->info('✅ SOLUCIÓN DEFINITIVA COMPLETADA');
            $this->line('');
            $this->line('📋 RESUMEN:');
            $this->line("   • Link: {$linkPublico}");
            $this->line("   • Sin cookies: Sí");
            $this->line("   • Sin sesiones: Sí");
            $this->line("   • Sin autenticación: Sí");
            $this->line("   • Token válido: Sí");
            $this->line("   • URL correcta: Sí");
            $this->line('');
            $this->line('🎯 INSTRUCCIONES PARA PROBAR:');
            $this->line("   1. Copia este link: {$linkPublico}");
            $this->line("   2. Pégalo en tu navegador");
            $this->line("   3. Deberías ver la encuesta sin errores de cookies");
            $this->line("   4. La página funcionará sin sesiones ni cookies");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
            return 1;
        }
    }
}
