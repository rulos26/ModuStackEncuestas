<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RevisarLogsPrueba extends Command
{
    protected $signature = 'encuesta:revisar-logs-prueba';
    protected $description = 'Revisar logs de prueba para verificar conexión vista-controlador';

    public function handle()
    {
        $this->info('📝 REVISANDO LOGS DE PRUEBA');
        $this->line('');

        $logPath = storage_path('logs/laravel.log');

        if (!File::exists($logPath)) {
            $this->error('❌ Archivo de log no encontrado: ' . $logPath);
            return 1;
        }

        $this->line('📋 Leyendo archivo de log...');
        $lines = file($logPath);

        if (empty($lines)) {
            $this->warn('⚠️  El archivo de log está vacío');
            return 0;
        }

        // Buscar líneas de prueba
        $pruebaLines = [];
        foreach ($lines as $lineNumber => $line) {
            if (strpos($line, '🧪 PRUEBA:') !== false) {
                $pruebaLines[] = [
                    'line' => $lineNumber + 1,
                    'content' => trim($line)
                ];
            }
        }

        if (empty($pruebaLines)) {
            $this->warn('⚠️  No se encontraron logs de prueba');
            $this->line('');
            $this->line('💡 Para generar logs de prueba:');
            $this->line('   1. Accede a una encuesta pública');
            $this->line('   2. Llena el formulario');
            $this->line('   3. Presiona "Enviar respuestas"');
            return 0;
        }

        $this->line('✅ Se encontraron ' . count($pruebaLines) . ' logs de prueba');
        $this->line('');

        // Mostrar logs de prueba
        $this->line('📋 LOGS DE PRUEBA ENCONTRADOS:');
        $this->line('');

        foreach ($pruebaLines as $index => $log) {
            $this->line('--- Log #' . ($index + 1) . ' (Línea ' . $log['line'] . ') ---');
            $this->line($log['content']);
            $this->line('');
        }

        // Analizar logs
        $this->line('🔍 ANÁLISIS DE LOGS:');
        $this->line('');

        $accesos = 0;
        $conexiones = 0;
        $encuestasEncontradas = 0;
        $respuestasRecibidas = 0;
        $respuestasGuardadas = 0;
        $errores = 0;

        foreach ($pruebaLines as $log) {
            $content = $log['content'];

            if (strpos($content, 'Acceso a mostrar encuesta') !== false) {
                $accesos++;
            }
            if (strpos($content, 'Conexión vista-controlador establecida') !== false) {
                $conexiones++;
            }
            if (strpos($content, 'Encuesta encontrada') !== false) {
                $encuestasEncontradas++;
            }
            if (strpos($content, 'Respuestas recibidas') !== false) {
                $respuestasRecibidas++;
            }
            if (strpos($content, 'Respuestas guardadas exitosamente') !== false) {
                $respuestasGuardadas++;
            }
            if (strpos($content, 'Error') !== false) {
                $errores++;
            }
        }

        $this->line('📊 ESTADÍSTICAS:');
        $this->line('   • Accesos a mostrar: ' . $accesos);
        $this->line('   • Conexiones vista-controlador: ' . $conexiones);
        $this->line('   • Encuestas encontradas: ' . $encuestasEncontradas);
        $this->line('   • Respuestas recibidas: ' . $respuestasRecibidas);
        $this->line('   • Respuestas guardadas: ' . $respuestasGuardadas);
        $this->line('   • Errores: ' . $errores);
        $this->line('');

        // Diagnóstico
        $this->line('🔍 DIAGNÓSTICO:');

        if ($accesos > 0) {
            $this->line('   ✅ Acceso a encuesta: FUNCIONA');
        } else {
            $this->line('   ❌ Acceso a encuesta: NO FUNCIONA');
        }

        if ($conexiones > 0) {
            $this->line('   ✅ Conexión vista-controlador: FUNCIONA');
        } else {
            $this->line('   ❌ Conexión vista-controlador: NO FUNCIONA');
        }

        if ($encuestasEncontradas > 0) {
            $this->line('   ✅ Búsqueda de encuesta: FUNCIONA');
        } else {
            $this->line('   ❌ Búsqueda de encuesta: NO FUNCIONA');
        }

        if ($respuestasRecibidas > 0) {
            $this->line('   ✅ Recepción de respuestas: FUNCIONA');
        } else {
            $this->line('   ❌ Recepción de respuestas: NO FUNCIONA');
        }

        if ($respuestasGuardadas > 0) {
            $this->line('   ✅ Guardado de respuestas: FUNCIONA');
        } else {
            $this->line('   ❌ Guardado de respuestas: NO FUNCIONA');
        }

        if ($errores > 0) {
            $this->line('   ⚠️  Errores detectados: ' . $errores);
        } else {
            $this->line('   ✅ Sin errores detectados');
        }

        $this->line('');
        $this->info('✅ Revisión de logs completada');

        return 0;
    }
}
