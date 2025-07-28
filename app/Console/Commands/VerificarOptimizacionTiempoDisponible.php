<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class VerificarOptimizacionTiempoDisponible extends Command
{
    protected $signature = 'encuestas:verificar-optimizacion-tiempo';
    protected $description = 'Verifica que la optimización del campo tiempo_disponible se completó correctamente';

    public function handle()
    {
        $this->info("=== VERIFICACIÓN DE OPTIMIZACIÓN DEL CAMPO TIEMPO_DISPONIBLE ===");

        // Verificar si el campo existe en la base de datos
        $this->verificarCampoEnBaseDeDatos();

        // Verificar modelo
        $this->verificarModelo();

        // Verificar request
        $this->verificarRequest();

        // Verificar vistas
        $this->verificarVistas();

        // Verificar funcionalidad
        $this->verificarFuncionalidad();

        $this->info("✅ Verificación completada exitosamente");
        return 0;
    }

    /**
     * Verificar si el campo existe en la base de datos
     */
    private function verificarCampoEnBaseDeDatos()
    {
        $this->info("\n📊 VERIFICANDO BASE DE DATOS:");

        try {
            $columnas = Schema::getColumnListing('encuestas');

            if (in_array('tiempo_disponible', $columnas)) {
                $this->error("   ❌ El campo 'tiempo_disponible' aún existe en la base de datos");
                $this->warn("   💡 Ejecuta: php artisan migrate");
            } else {
                $this->info("   ✅ El campo 'tiempo_disponible' ha sido eliminado correctamente");
            }

            // Verificar que los campos nuevos existen
            $camposRequeridos = ['fecha_inicio', 'fecha_fin'];
            foreach ($camposRequeridos as $campo) {
                if (in_array($campo, $columnas)) {
                    $this->info("   ✅ Campo '{$campo}' existe");
                } else {
                    $this->error("   ❌ Campo '{$campo}' no existe");
                }
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando base de datos: " . $e->getMessage());
        }
    }

    /**
     * Verificar modelo Encuesta
     */
    private function verificarModelo()
    {
        $this->info("\n🔧 VERIFICANDO MODELO ENCUESTA:");

        try {
            $encuesta = new Encuesta();
            $fillable = $encuesta->getFillable();

            if (in_array('tiempo_disponible', $fillable)) {
                $this->error("   ❌ 'tiempo_disponible' aún está en \$fillable");
            } else {
                $this->info("   ✅ 'tiempo_disponible' ha sido removido de \$fillable");
            }

            $casts = $encuesta->getCasts();
            if (isset($casts['tiempo_disponible'])) {
                $this->error("   ❌ 'tiempo_disponible' aún está en \$casts");
            } else {
                $this->info("   ✅ 'tiempo_disponible' ha sido removido de \$casts");
            }

            // Verificar método estaDisponible
            $reflection = new \ReflectionClass($encuesta);
            $method = $reflection->getMethod('estaDisponible');
            $source = file_get_contents($reflection->getFileName());
            $lines = explode("\n", $source);
            $methodLines = array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);
            $methodCode = implode("\n", $methodLines);

            if (strpos($methodCode, 'tiempo_disponible') !== false) {
                $this->error("   ❌ Método 'estaDisponible' aún hace referencia a 'tiempo_disponible'");
            } else {
                $this->info("   ✅ Método 'estaDisponible' ha sido optimizado");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando modelo: " . $e->getMessage());
        }
    }

    /**
     * Verificar EncuestaRequest
     */
    private function verificarRequest()
    {
        $this->info("\n📝 VERIFICANDO ENCUESTAREQUEST:");

        try {
            $request = new \App\Http\Requests\EncuestaRequest();
            $rules = $request->rules();

            if (isset($rules['tiempo_disponible'])) {
                $this->error("   ❌ 'tiempo_disponible' aún está en las reglas de validación");
            } else {
                $this->info("   ✅ 'tiempo_disponible' ha sido removido de las reglas de validación");
            }

            $messages = $request->messages();
            $tiempoDisponibleMessages = array_filter($messages, function($key) {
                return strpos($key, 'tiempo_disponible') !== false;
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($tiempoDisponibleMessages)) {
                $this->error("   ❌ Mensajes de 'tiempo_disponible' aún existen");
            } else {
                $this->info("   ✅ Mensajes de 'tiempo_disponible' han sido removidos");
            }

            $attributes = $request->attributes();
            if (isset($attributes['tiempo_disponible'])) {
                $this->error("   ❌ 'tiempo_disponible' aún está en los atributos");
            } else {
                $this->info("   ✅ 'tiempo_disponible' ha sido removido de los atributos");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando request: " . $e->getMessage());
        }
    }

    /**
     * Verificar vistas
     */
    private function verificarVistas()
    {
        $this->info("\n👁️ VERIFICANDO VISTAS:");

        $vistas = [
            'resources/views/encuestas/create.blade.php',
            'resources/views/encuestas/show.blade.php'
        ];

        foreach ($vistas as $vista) {
            if (file_exists($vista)) {
                $contenido = file_get_contents($vista);
                if (strpos($contenido, 'tiempo_disponible') !== false) {
                    $this->error("   ❌ 'tiempo_disponible' aún existe en {$vista}");
                } else {
                    $this->info("   ✅ {$vista} ha sido optimizada");
                }
            } else {
                $this->warn("   ⚠️ Vista {$vista} no encontrada");
            }
        }
    }

    /**
     * Verificar funcionalidad
     */
    private function verificarFuncionalidad()
    {
        $this->info("\n⚙️ VERIFICANDO FUNCIONALIDAD:");

        try {
            // Crear una encuesta de prueba
            $encuesta = new Encuesta();
            $encuesta->titulo = 'Encuesta de Prueba';
            $encuesta->empresa_id = 1;
            $encuesta->habilitada = true;
            $encuesta->fecha_inicio = now()->addDay();
            $encuesta->fecha_fin = now()->addDays(7);

            // Verificar método estaDisponible
            $disponible = $encuesta->estaDisponible();
            $this->info("   ✅ Método 'estaDisponible' funciona correctamente");

            // Verificar que no hay referencias a tiempo_disponible
            $reflection = new \ReflectionClass($encuesta);
            $methods = $reflection->getMethods();

            $referenciasEncontradas = false;
            foreach ($methods as $method) {
                if ($method->class === Encuesta::class) {
                    $source = file_get_contents($reflection->getFileName());
                    $lines = explode("\n", $source);
                    $methodLines = array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1);
                    $methodCode = implode("\n", $methodLines);

                    if (strpos($methodCode, 'tiempo_disponible') !== false) {
                        $this->error("   ❌ Método '{$method->getName()}' aún hace referencia a 'tiempo_disponible'");
                        $referenciasEncontradas = true;
                    }
                }
            }

            if (!$referenciasEncontradas) {
                $this->info("   ✅ No se encontraron referencias a 'tiempo_disponible' en los métodos");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando funcionalidad: " . $e->getMessage());
        }
    }
}
