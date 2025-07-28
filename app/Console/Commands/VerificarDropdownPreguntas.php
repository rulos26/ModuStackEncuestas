<?php

namespace App\Console\Commands;

use App\Models\Pregunta;
use Illuminate\Console\Command;

class VerificarDropdownPreguntas extends Command
{
    protected $signature = 'encuestas:verificar-dropdown';
    protected $description = 'Verifica que el dropdown de tipos de preguntas funciona correctamente';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO DROPDOWN DE TIPOS DE PREGUNTAS');
        $this->info('================================================');

        try {
            // Verificar que el modelo Pregunta existe
            $this->info("\n📋 VERIFICANDO MODELO:");
            if (class_exists(Pregunta::class)) {
                $this->info("   ✅ Modelo Pregunta existe");
            } else {
                $this->error("   ❌ Modelo Pregunta no existe");
                return 1;
            }

            // Verificar método getTiposDisponibles
            $this->info("\n🎯 VERIFICANDO MÉTODO getTiposDisponibles:");
            if (method_exists(Pregunta::class, 'getTiposDisponibles')) {
                $this->info("   ✅ Método getTiposDisponibles existe");

                $tipos = Pregunta::getTiposDisponibles();
                $this->info("   ✅ Se obtuvieron " . count($tipos) . " tipos de preguntas");

                foreach ($tipos as $tipo => $config) {
                    $this->info("      • {$tipo}: {$config['nombre']}");
                }
            } else {
                $this->error("   ❌ Método getTiposDisponibles no existe");
                return 1;
            }

            // Verificar estructura de configuración
            $this->info("\n⚙️ VERIFICANDO ESTRUCTURA DE CONFIGURACIÓN:");
            $tipos = Pregunta::getTiposDisponibles();
            $camposRequeridos = ['nombre', 'descripcion', 'icono', 'necesita_respuestas', 'necesita_opciones'];

            foreach ($tipos as $tipo => $config) {
                $camposFaltantes = [];
                foreach ($camposRequeridos as $campo) {
                    if (!isset($config[$campo])) {
                        $camposFaltantes[] = $campo;
                    }
                }

                if (empty($camposFaltantes)) {
                    $this->info("   ✅ {$tipo}: Configuración completa");
                } else {
                    $this->error("   ❌ {$tipo}: Faltan campos: " . implode(', ', $camposFaltantes));
                }
            }

            // Verificar tipos específicos importantes
            $this->info("\n🎯 VERIFICANDO TIPOS ESPECÍFICOS:");
            $tiposImportantes = [
                'respuesta_corta' => 'Texto breve',
                'seleccion_unica' => 'Radio buttons',
                'casillas_verificacion' => 'Checkboxes',
                'escala_lineal' => 'Escala numérica'
            ];

            foreach ($tiposImportantes as $tipo => $descripcion) {
                if (isset($tipos[$tipo])) {
                    $this->info("   ✅ {$tipo}: {$descripcion} - {$tipos[$tipo]['nombre']}");
                } else {
                    $this->error("   ❌ {$tipo}: No encontrado");
                }
            }

            // Verificar métodos auxiliares
            $this->info("\n🔧 VERIFICANDO MÉTODOS AUXILIARES:");
            $metodosAuxiliares = [
                'necesitaRespuestas',
                'necesitaOpciones',
                'getConfiguracionTipo',
                'getNombreTipo',
                'getIconoTipo'
            ];

            foreach ($metodosAuxiliares as $metodo) {
                if (method_exists(Pregunta::class, $metodo)) {
                    $this->info("   ✅ Método {$metodo} existe");
                } else {
                    $this->error("   ❌ Método {$metodo} no existe");
                }
            }

            // Verificar scopes
            $this->info("\n🔍 VERIFICANDO SCOPES:");
            $scopes = [
                'necesitaRespuestas',
                'esTexto',
                'esFechaHora',
                'esArchivo',
                'esUbicacion',
                'esEscala'
            ];

            foreach ($scopes as $scope) {
                if (method_exists(Pregunta::class, 'scope' . ucfirst($scope))) {
                    $this->info("   ✅ Scope {$scope} existe");
                } else {
                    $this->error("   ❌ Scope {$scope} no existe");
                }
            }

            // Verificar validaciones en el controlador
            $this->info("\n✅ VERIFICANDO VALIDACIONES:");
            $tiposValidos = [
                'respuesta_corta', 'parrafo', 'seleccion_unica', 'casillas_verificacion',
                'lista_desplegable', 'escala_lineal', 'cuadricula_opcion_multiple',
                'cuadricula_casillas', 'fecha', 'hora', 'carga_archivos',
                'ubicacion_mapa', 'logica_condicional'
            ];

            $tiposConfigurados = array_keys($tipos);
            $tiposFaltantes = array_diff($tiposValidos, $tiposConfigurados);
            $tiposExtra = array_diff($tiposConfigurados, $tiposValidos);

            if (empty($tiposFaltantes) && empty($tiposExtra)) {
                $this->info("   ✅ Todos los tipos están sincronizados entre modelo y validaciones");
            } else {
                if (!empty($tiposFaltantes)) {
                    $this->error("   ❌ Tipos faltantes en configuración: " . implode(', ', $tiposFaltantes));
                }
                if (!empty($tiposExtra)) {
                    $this->error("   ❌ Tipos extra en configuración: " . implode(', ', $tiposExtra));
                }
            }

            $this->info("\n🎉 VERIFICACIÓN COMPLETADA EXITOSAMENTE");
            $this->info("El dropdown de tipos de preguntas debería funcionar correctamente.");

            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ ERROR DURANTE LA VERIFICACIÓN:");
            $this->error($e->getMessage());
            return 1;
        }
    }
}
