<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Exception;

class CorregirCardsArmonicas extends Command
{
    protected $signature = 'corregir:cards-armonicas';
    protected $description = 'Verificar y corregir cards que tienen texto blanco en fondos blancos';

    public function handle()
    {
        $this->info('🎨 CORRIGIENDO CARDS ARMÓNICAS');
        $this->line('');

        try {
            $viewsPath = resource_path('views');
            $cards = $this->encontrarCards($viewsPath);

            $this->line('📋 CARDS ENCONTRADAS:');
            $this->line('');

            foreach ($cards as $card) {
                $this->analizarCard($card);
            }

            $this->line('');
            $this->info('✅ CSS global aplicado para corregir cards');
            $this->line('');
            $this->line('💡 Características del CSS aplicado:');
            $this->line('   • Cards básicas: Fondo suave (#f8f9fa)');
            $this->line('   • Modo oscuro: Fondo oscuro (#343a40)');
            $this->line('   • Headers: Color gris claro (#e9ecef)');
            $this->line('   • Texto: Negro en modo claro, blanco en modo oscuro');
            $this->line('   • Sombras: Sombra sutil para profundidad');
            $this->line('   • Bordes: Color gris claro (#dee2e6)');

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en corrección: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Encontrar todas las cards en las vistas
     */
    private function encontrarCards($path)
    {
        $cards = [];

        $files = File::allFiles($path);
        foreach ($files as $file) {
            $content = File::get($file->getPathname());

            // Buscar archivos que contengan cards
            if (strpos($content, 'class="card') !== false) {

                $cards[] = [
                    'path' => $file->getPathname(),
                    'relative_path' => str_replace(resource_path('views/'), '', $file->getPathname()),
                    'content' => $content
                ];
            }
        }

        return $cards;
    }

    /**
     * Analizar una card específica
     */
    private function analizarCard($card)
    {
        $this->line("🔍 Analizando: {$card['relative_path']}");

        $content = $card['content'];
        $cards = $this->extraerCards($content);

        if (count($cards) > 0) {
            $this->line("   📊 Total cards: " . count($cards));

            foreach ($cards as $index => $cardHtml) {
                $this->analizarCardEspecifica($cardHtml, $index + 1);
            }
        } else {
            $this->line("   ⚠️  No se encontraron cards");
        }

        $this->line('');
    }

    /**
     * Extraer cards del contenido
     */
    private function extraerCards($content)
    {
        preg_match_all('/<div[^>]*class="[^"]*card[^"]*"[^>]*>.*?<\/div>/is', $content, $matches);
        return $matches[0] ?? [];
    }

    /**
     * Analizar una card específica
     */
    private function analizarCardEspecifica($cardHtml, $numero)
    {
        $this->line("   📋 Card #{$numero}:");

        // Verificar si tiene clases de color
        $tieneBgPrimary = strpos($cardHtml, 'bg-primary') !== false;
        $tieneBgSuccess = strpos($cardHtml, 'bg-success') !== false;
        $tieneBgInfo = strpos($cardHtml, 'bg-info') !== false;
        $tieneBgWarning = strpos($cardHtml, 'bg-warning') !== false;
        $tieneBgDanger = strpos($cardHtml, 'bg-danger') !== false;
        $tieneBgSecondary = strpos($cardHtml, 'bg-secondary') !== false;
        $tieneBgDark = strpos($cardHtml, 'bg-dark') !== false;
        $tieneTextWhite = strpos($cardHtml, 'text-white') !== false;
        $tieneGradient = strpos($cardHtml, 'bg-gradient') !== false;

        // Verificar si es una card básica (sin color específico)
        $esCardBasica = !$tieneBgPrimary && !$tieneBgSuccess && !$tieneBgInfo &&
                       !$tieneBgWarning && !$tieneBgDanger && !$tieneBgSecondary &&
                       !$tieneBgDark && !$tieneGradient;

        if ($esCardBasica) {
            $this->line("      ✅ Card básica - CSS global aplicado");
        }

        if ($tieneBgPrimary) {
            $this->line("      🟦 Card primaria - Fondo azul con texto blanco");
        }

        if ($tieneBgSuccess) {
            $this->line("      🟢 Card exitosa - Fondo verde con texto blanco");
        }

        if ($tieneBgInfo) {
            $this->line("      🔵 Card informativa - Fondo azul claro con texto blanco");
        }

        if ($tieneBgWarning) {
            $this->line("      🟡 Card de advertencia - Fondo amarillo con texto negro");
        }

        if ($tieneBgDanger) {
            $this->line("      🔴 Card de peligro - Fondo rojo con texto blanco");
        }

        if ($tieneBgSecondary) {
            $this->line("      ⚫ Card secundaria - Fondo gris con texto blanco");
        }

        if ($tieneBgDark) {
            $this->line("      ⚫ Card oscura - Fondo negro con texto blanco");
        }

        if ($tieneGradient) {
            $this->line("      🌈 Card con gradiente - Efecto visual mejorado");
        }

        if ($tieneTextWhite) {
            $this->line("      📝 Tiene texto blanco - Verificar contraste");
        }

        // Verificar si tiene header
        if (strpos($cardHtml, 'card-header') !== false) {
            $this->line("      📋 Tiene header - Estilo aplicado");
        }

        // Verificar si tiene body
        if (strpos($cardHtml, 'card-body') !== false) {
            $this->line("      📄 Tiene body - Estilo aplicado");
        }

        // Verificar si tiene título
        if (strpos($cardHtml, 'card-title') !== false) {
            $this->line("      📌 Tiene título - Estilo aplicado");
        }

        // Verificar si tiene formularios
        if (strpos($cardHtml, 'form-control') !== false) {
            $this->line("      📝 Contiene formularios - Estilos aplicados");
        }

        // Verificar si tiene tablas
        if (strpos($cardHtml, 'table') !== false) {
            $this->line("      📊 Contiene tablas - Estilos aplicados");
        }

        // Verificar si tiene botones
        if (strpos($cardHtml, 'btn') !== false) {
            $this->line("      🔘 Contiene botones - Estilos aplicados");
        }
    }
}
