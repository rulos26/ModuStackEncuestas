<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class VerificarEnumEstado extends Command
{
    protected $signature = 'verificar:enum-estado {--debug}';
    protected $description = 'Verifica el ENUM de estado en la tabla encuestas';

    public function handle()
    {
        $debug = $this->option('debug');

        $this->info("🔍 VERIFICANDO ENUM DE ESTADO");
        $this->line('');

        try {
            // 1. Verificar estructura de la tabla
            $this->verificarEstructuraTabla();

            // 2. Verificar ENUM actual
            $this->verificarEnumActual();

            // 3. Verificar valores permitidos
            $this->verificarValoresPermitidos();

            // 4. Probar actualización
            $this->probarActualizacion();

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error durante la verificación: " . $e->getMessage());

            if ($debug) {
                $this->line("Stack trace:");
                $this->line($e->getTraceAsString());
            }

            return 1;
        }
    }

    private function verificarEstructuraTabla()
    {
        $this->info("📋 VERIFICANDO ESTRUCTURA DE TABLA:");

        try {
            $columnas = DB::select("SHOW COLUMNS FROM encuestas LIKE 'estado'");

            if (empty($columnas)) {
                $this->error("   ❌ Columna 'estado' no encontrada");
                return;
            }

            $columna = $columnas[0];
            $this->line("   ✅ Columna 'estado' encontrada");
            $this->line("   - Tipo: {$columna->Type}");
            $this->line("   - Null: {$columna->Null}");
            $this->line("   - Default: {$columna->Default}");

            // Extraer valores del ENUM
            if (preg_match("/enum\((.*)\)/", $columna->Type, $matches)) {
                $valores = str_getcsv($matches[1], ',', "'");
                $this->line("   - Valores permitidos: " . implode(', ', $valores));
            }

        } catch (Exception $e) {
            $this->error("   ❌ Error verificando estructura: " . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarEnumActual()
    {
        $this->info("🔍 VERIFICANDO ENUM ACTUAL:");

        try {
            $resultado = DB::select("
                SELECT COLUMN_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'encuestas'
                AND COLUMN_NAME = 'estado'
            ");

            if (!empty($resultado)) {
                $enumDefinicion = $resultado[0]->COLUMN_TYPE;
                $this->line("   - Definición actual: {$enumDefinicion}");

                // Extraer valores
                if (preg_match("/enum\((.*)\)/", $enumDefinicion, $matches)) {
                    $valores = str_getcsv($matches[1], ',', "'");
                    $this->line("   - Valores permitidos:");
                    foreach ($valores as $valor) {
                        $this->line("     • '{$valor}'");
                    }
                }
            } else {
                $this->error("   ❌ No se pudo obtener la definición del ENUM");
            }

        } catch (Exception $e) {
            $this->error("   ❌ Error obteniendo ENUM: " . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarValoresPermitidos()
    {
        $this->info("✅ VERIFICANDO VALORES PERMITIDOS:");

        $valoresEsperados = ['borrador', 'en_progreso', 'enviada', 'pausada', 'completada', 'publicada'];
        $valoresActuales = [];

        try {
            $resultado = DB::select("
                SELECT COLUMN_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'encuestas'
                AND COLUMN_NAME = 'estado'
            ");

            if (!empty($resultado)) {
                $enumDefinicion = $resultado[0]->COLUMN_TYPE;

                if (preg_match("/enum\((.*)\)/", $enumDefinicion, $matches)) {
                    $valoresActuales = str_getcsv($matches[1], ',', "'");
                }
            }

            $this->line("   - Valores esperados:");
            foreach ($valoresEsperados as $valor) {
                $icono = in_array($valor, $valoresActuales) ? '✅' : '❌';
                $this->line("     {$icono} '{$valor}'");
            }

            $this->line('');
            $this->line("   - Valores faltantes:");
            $faltantes = array_diff($valoresEsperados, $valoresActuales);
            if (empty($faltantes)) {
                $this->line("     ✅ Todos los valores están presentes");
            } else {
                foreach ($faltantes as $valor) {
                    $this->line("     ❌ '{$valor}'");
                }
            }

        } catch (Exception $e) {
            $this->error("   ❌ Error verificando valores: " . $e->getMessage());
        }

        $this->line('');
    }

    private function probarActualizacion()
    {
        $this->info("🧪 PROBANDO ACTUALIZACIÓN:");

        try {
            // Intentar actualizar con cada valor
            $valores = ['borrador', 'en_progreso', 'enviada', 'pausada', 'completada', 'publicada'];

            foreach ($valores as $valor) {
                try {
                    DB::table('encuestas')->where('id', 2)->update(['estado' => $valor]);
                    $this->line("   ✅ '{$valor}': OK");
                } catch (Exception $e) {
                    $this->error("   ❌ '{$valor}': " . $e->getMessage());
                }
            }

        } catch (Exception $e) {
            $this->error("   ❌ Error en prueba de actualización: " . $e->getMessage());
        }

        $this->line('');
    }
}
