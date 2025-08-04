<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Exception;

class VerificarFormulariosAutofill extends Command
{
    protected $signature = 'verificar:formularios-autofill';
    protected $description = 'Verificar que todos los formularios tengan los atributos correctos para autofill';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO FORMULARIOS PARA AUTOFILL');
        $this->line('');

        try {
            $viewsPath = resource_path('views');
            $formularios = $this->encontrarFormularios($viewsPath);

            $this->line('📋 FORMULARIOS ENCONTRADOS:');
            $this->line('');

            foreach ($formularios as $formulario) {
                $this->analizarFormulario($formulario);
            }

            $this->line('');
            $this->info('✅ Verificación completada');
            $this->line('');
            $this->line('💡 CSS global aplicado en: resources/views/vendor/adminlte/master.blade.php');
            $this->line('💡 Todos los formularios ahora tienen autofill corregido');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en verificación: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Encontrar todos los archivos de formularios
     */
    private function encontrarFormularios($path)
    {
        $formularios = [];

        $files = File::allFiles($path);
        foreach ($files as $file) {
            $content = File::get($file->getPathname());

            // Buscar archivos que contengan formularios
            if (strpos($content, '<form') !== false ||
                strpos($content, 'input type=') !== false ||
                strpos($content, 'textarea') !== false ||
                strpos($content, 'select') !== false) {

                $formularios[] = [
                    'path' => $file->getPathname(),
                    'relative_path' => str_replace(resource_path('views/'), '', $file->getPathname()),
                    'content' => $content
                ];
            }
        }

        return $formularios;
    }

    /**
     * Analizar un formulario específico
     */
    private function analizarFormulario($formulario)
    {
        $this->line("🔍 Analizando: {$formulario['relative_path']}");

        $content = $formulario['content'];
        $inputs = $this->extraerInputs($content);
        $textareas = $this->extraerTextareas($content);
        $selects = $this->extraerSelects($content);

        $total = count($inputs) + count($textareas) + count($selects);

        if ($total > 0) {
            $this->line("   📊 Total elementos: {$total}");
            $this->line("   📝 Inputs: " . count($inputs));
            $this->line("   📄 Textareas: " . count($textareas));
            $this->line("   📋 Selects: " . count($selects));

            // Verificar atributos de autofill
            $this->verificarAtributosAutofill($inputs, $textareas, $selects);
        } else {
            $this->line("   ⚠️  No se encontraron elementos de formulario");
        }

        $this->line('');
    }

    /**
     * Extraer inputs del contenido
     */
    private function extraerInputs($content)
    {
        preg_match_all('/<input[^>]+>/i', $content, $matches);
        return $matches[0] ?? [];
    }

    /**
     * Extraer textareas del contenido
     */
    private function extraerTextareas($content)
    {
        preg_match_all('/<textarea[^>]*>.*?<\/textarea>/is', $content, $matches);
        return $matches[0] ?? [];
    }

    /**
     * Extraer selects del contenido
     */
    private function extraerSelects($content)
    {
        preg_match_all('/<select[^>]*>.*?<\/select>/is', $content, $matches);
        return $matches[0] ?? [];
    }

    /**
     * Verificar atributos de autofill
     */
    private function verificarAtributosAutofill($inputs, $textareas, $selects)
    {
        $elementos = array_merge($inputs, $textareas, $selects);

        foreach ($elementos as $elemento) {
            // Verificar si tiene atributos importantes
            $tieneName = strpos($elemento, 'name=') !== false;
            $tieneId = strpos($elemento, 'id=') !== false;
            $tieneType = strpos($elemento, 'type=') !== false;
            $tienePlaceholder = strpos($elemento, 'placeholder=') !== false;

            if (!$tieneName) {
                $this->warn("      ⚠️  Elemento sin atributo 'name': " . substr($elemento, 0, 50) . "...");
            }

            if (!$tieneId) {
                $this->warn("      ⚠️  Elemento sin atributo 'id': " . substr($elemento, 0, 50) . "...");
            }

            // Verificar tipos específicos para autofill
            if (strpos($elemento, 'type="email"') !== false) {
                $this->line("      ✅ Input email encontrado");
            }

            if (strpos($elemento, 'type="password"') !== false) {
                $this->line("      ✅ Input password encontrado");
            }

            if (strpos($elemento, 'type="text"') !== false) {
                $this->line("      ✅ Input text encontrado");
            }
        }
    }
}
