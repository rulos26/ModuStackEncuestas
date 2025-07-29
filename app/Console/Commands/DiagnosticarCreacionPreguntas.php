<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Pregunta;
use App\Models\Encuesta;
use App\Models\User;
use Exception;

class DiagnosticarCreacionPreguntas extends Command
{
    protected $signature = 'preguntas:diagnosticar-creacion {--encuesta_id=} {--crear_prueba} {--debug}';
    protected $description = 'Diagnostica problemas específicos en la creación de preguntas';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO DE CREACIÓN DE PREGUNTAS');
        $this->info('=====================================');

        try {
            // Verificar conexión a la base de datos
            $this->verificarConexionBD();

            // Verificar estructura de la tabla preguntas
            $this->verificarEstructuraTabla();

            // Verificar modelo Pregunta
            $this->verificarModeloPregunta();

            // Verificar datos de prueba
            $this->verificarDatosPrueba();

            // Verificar encuesta específica si se proporciona
            $encuestaId = $this->option('encuesta_id');
            if ($encuestaId) {
                $this->verificarEncuestaEspecifica($encuestaId);
            }

            // Crear pregunta de prueba si se solicita
            if ($this->option('crear_prueba')) {
                $this->crearPreguntaPrueba($encuestaId);
            }

            // Mostrar recomendaciones
            $this->mostrarRecomendaciones();

        } catch (\Exception $e) {
            $this->error('❌ Error durante el diagnóstico: ' . $e->getMessage());
            if ($this->option('debug')) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }
            return 1;
        }

        return 0;
    }

    private function verificarConexionBD()
    {
        $this->info("\n📊 VERIFICANDO CONEXIÓN A BASE DE DATOS:");

        try {
            DB::connection()->getPdo();
            $this->info("   ✅ Conexión a base de datos exitosa");
            $this->info("   📍 Base de datos: " . DB::connection()->getDatabaseName());
        } catch (\Exception $e) {
            $this->error("   ❌ Error de conexión: " . $e->getMessage());
            throw $e;
        }
    }

    private function verificarEstructuraTabla()
    {
        $this->info("\n📋 VERIFICANDO ESTRUCTURA DE LA TABLA PREGUNTAS:");

        if (!Schema::hasTable('preguntas')) {
            $this->error("   ❌ La tabla 'preguntas' no existe");
            $this->warn("   💡 Ejecuta: php artisan migrate");
            return;
        }

        $this->info("   ✅ La tabla 'preguntas' existe");

        // Obtener todas las columnas
        $columnas = Schema::getColumnListing('preguntas');
        $this->info("   📝 Columnas encontradas: " . count($columnas));

        // Verificar columnas críticas
        $columnasCriticas = [
            'id', 'encuesta_id', 'texto', 'tipo', 'orden', 'obligatoria',
            'descripcion', 'placeholder', 'min_caracteres', 'max_caracteres',
            'escala_min', 'escala_max', 'escala_etiqueta_min', 'escala_etiqueta_max',
            'tipos_archivo_permitidos', 'tamano_max_archivo',
            'latitud_default', 'longitud_default', 'zoom_default',
            'condiciones_mostrar', 'logica_salto', 'opciones_filas', 'opciones_columnas'
        ];

        foreach ($columnasCriticas as $columna) {
            if (in_array($columna, $columnas)) {
                $this->info("      ✅ {$columna}");
            } else {
                $this->error("      ❌ {$columna} - FALTANTE");
            }
        }

        // Verificar tipo de columna 'tipo'
        $this->verificarTipoColumna();
    }

    private function verificarTipoColumna()
    {
        $this->info("\n🔍 VERIFICANDO TIPO DE COLUMNA 'TIPO':");

        try {
            $resultado = DB::select("SHOW COLUMNS FROM preguntas WHERE Field = 'tipo'");
            if (!empty($resultado)) {
                $tipo = $resultado[0]->Type;
                $this->info("   📝 Tipo actual: {$tipo}");

                // Verificar si es enum con todos los tipos necesarios
                if (strpos($tipo, 'enum') !== false) {
                    $this->info("   ✅ Es un ENUM");

                    // Extraer valores del enum
                    preg_match("/enum\((.*)\)/", $tipo, $matches);
                    if (isset($matches[1])) {
                        $valores = str_getcsv($matches[1], ',', "'");
                        $this->info("   📋 Valores del ENUM: " . implode(', ', $valores));

                        $tiposNecesarios = [
                            'respuesta_corta', 'parrafo', 'seleccion_unica', 'casillas_verificacion',
                            'lista_desplegable', 'escala_lineal', 'cuadricula_opcion_multiple',
                            'cuadricula_casillas', 'fecha', 'hora', 'carga_archivos',
                            'ubicacion_mapa', 'logica_condicional'
                        ];

                        foreach ($tiposNecesarios as $tipoNecesario) {
                            if (in_array($tipoNecesario, $valores)) {
                                $this->info("      ✅ {$tipoNecesario}");
                            } else {
                                $this->error("      ❌ {$tipoNecesario} - FALTANTE");
                            }
                        }
                    }
                } else {
                    $this->warn("   ⚠️  No es un ENUM, es: {$tipo}");
                }
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando tipo: " . $e->getMessage());
        }
    }

    private function verificarModeloPregunta()
    {
        $this->info("\n🔧 VERIFICANDO MODELO PREGUNTA:");

        try {
            $pregunta = new Pregunta();

            // Verificar fillable
            $fillable = $pregunta->getFillable();
            $this->info("   📝 Campos fillable: " . count($fillable));

            $fillableNecesarios = [
                'encuesta_id', 'texto', 'descripcion', 'placeholder', 'tipo', 'orden', 'obligatoria',
                'min_caracteres', 'max_caracteres', 'escala_min', 'escala_max',
                'escala_etiqueta_min', 'escala_etiqueta_max', 'tipos_archivo_permitidos',
                'tamano_max_archivo', 'latitud_default', 'longitud_default', 'zoom_default',
                'condiciones_mostrar', 'logica_salto', 'opciones_filas', 'opciones_columnas'
            ];

            foreach ($fillableNecesarios as $campo) {
                if (in_array($campo, $fillable)) {
                    $this->info("      ✅ {$campo}");
                } else {
                    $this->error("      ❌ {$campo} - FALTANTE en fillable");
                }
            }

            // Verificar casts
            $casts = $pregunta->getCasts();
            $this->info("   📝 Casts definidos: " . count($casts));

            foreach ($casts as $campo => $tipo) {
                $this->info("      ✅ {$campo} => {$tipo}");
            }

            // Verificar método estático
            if (method_exists($pregunta, 'getTiposDisponibles')) {
                $tipos = Pregunta::getTiposDisponibles();
                $this->info("   ✅ Método getTiposDisponibles() existe");
                $this->info("   📝 Tipos disponibles: " . count($tipos));

                foreach ($tipos as $tipo => $config) {
                    $this->info("      ✅ {$tipo}: {$config['nombre']}");
                }
            } else {
                $this->error("   ❌ Método getTiposDisponibles() no existe");
            }

            // Verificar método calcularOrdenAutomatico
            if (method_exists($pregunta, 'calcularOrdenAutomatico')) {
                $this->info("   ✅ Método calcularOrdenAutomatico() existe");
            } else {
                $this->error("   ❌ Método calcularOrdenAutomatico() no existe");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando modelo: " . $e->getMessage());
        }
    }

    private function verificarDatosPrueba()
    {
        $this->info("\n📊 VERIFICANDO DATOS DE PRUEBA:");

        try {
            // Verificar usuarios
            $usuarios = User::count();
            $this->info("   👥 Usuarios: {$usuarios}");

            if ($usuarios == 0) {
                $this->warn("   ⚠️  No hay usuarios en la base de datos");
            }

            // Verificar encuestas
            $encuestas = Encuesta::count();
            $this->info("   📋 Encuestas: {$encuestas}");

            if ($encuestas == 0) {
                $this->warn("   ⚠️  No hay encuestas en la base de datos");
            } else {
                $encuestasConPreguntas = Encuesta::has('preguntas')->count();
                $this->info("   📝 Encuestas con preguntas: {$encuestasConPreguntas}");
            }

            // Verificar preguntas
            $preguntas = Pregunta::count();
            $this->info("   ❓ Preguntas: {$preguntas}");

            if ($preguntas > 0) {
                $tiposUsados = Pregunta::select('tipo')->distinct()->pluck('tipo')->toArray();
                $this->info("   📋 Tipos de preguntas usados: " . implode(', ', $tiposUsados));
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando datos: " . $e->getMessage());
        }
    }

    private function verificarEncuestaEspecifica($encuestaId)
    {
        $this->info("\n🎯 VERIFICANDO ENCUESTA ESPECÍFICA (ID: {$encuestaId}):");

        try {
            $encuesta = Encuesta::with('preguntas')->find($encuestaId);

            if (!$encuesta) {
                $this->error("   ❌ Encuesta con ID {$encuestaId} no encontrada");
                return;
            }

            $this->info("   ✅ Encuesta encontrada: {$encuesta->titulo}");
            $this->info("   👤 Propietario: {$encuesta->user->name} (ID: {$encuesta->user_id})");
            $this->info("   📝 Preguntas existentes: {$encuesta->preguntas->count()}");
            $this->info("   📅 Estado: {$encuesta->estado}");
            $this->info("   ✅ Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));

            // Verificar preguntas existentes
            if ($encuesta->preguntas->count() > 0) {
                $this->info("   📋 Preguntas existentes:");
                foreach ($encuesta->preguntas as $pregunta) {
                    $this->info("      - ID: {$pregunta->id}, Tipo: {$pregunta->tipo}, Orden: {$pregunta->orden}, Texto: {$pregunta->texto}");
                }
            }

            // Verificar si puede avanzar a respuestas
            if (method_exists($encuesta, 'puedeAvanzarA')) {
                $puedeAvanzar = $encuesta->puedeAvanzarA('respuestas');
                $this->info("   🔄 Puede avanzar a respuestas: " . ($puedeAvanzar ? 'Sí' : 'No'));
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando encuesta: " . $e->getMessage());
        }
    }

    private function crearPreguntaPrueba($encuestaId = null)
    {
        $this->info("\n🧪 CREANDO PREGUNTA DE PRUEBA:");

        try {
            // Obtener encuesta
            if (!$encuestaId) {
                $encuesta = Encuesta::first();
                if (!$encuesta) {
                    $this->error("   ❌ No hay encuestas disponibles para crear pregunta de prueba");
                    return;
                }
                $encuestaId = $encuesta->id;
            } else {
                $encuesta = Encuesta::find($encuestaId);
                if (!$encuesta) {
                    $this->error("   ❌ Encuesta con ID {$encuestaId} no encontrada");
                    return;
                }
            }

            $this->info("   📋 Usando encuesta: {$encuesta->titulo} (ID: {$encuestaId})");

            // Datos de prueba
            $datosPrueba = [
                'encuesta_id' => $encuestaId,
                'texto' => 'Pregunta de prueba - ' . now()->format('Y-m-d H:i:s'),
                'descripcion' => 'Esta es una pregunta de prueba para diagnosticar problemas',
                'placeholder' => 'Escribe tu respuesta aquí',
                'tipo' => 'respuesta_corta',
                'orden' => Pregunta::calcularOrdenAutomatico($encuestaId),
                'obligatoria' => true,
                'min_caracteres' => 3,
                'max_caracteres' => 100
            ];

            $this->info("   📝 Datos de prueba preparados:");
            foreach ($datosPrueba as $campo => $valor) {
                $this->info("      - {$campo}: {$valor}");
            }

            // Verificar que el orden no esté duplicado
            $ordenExistente = Pregunta::where('encuesta_id', $encuestaId)
                ->where('orden', $datosPrueba['orden'])
                ->exists();

            if ($ordenExistente) {
                $this->warn("   ⚠️  Ya existe una pregunta con orden {$datosPrueba['orden']}, incrementando...");
                $datosPrueba['orden']++;
            }

            // Crear la pregunta
            DB::beginTransaction();

            $pregunta = Pregunta::create($datosPrueba);

            if (!$pregunta->id) {
                throw new Exception('La pregunta no se creó correctamente - no se generó ID');
            }

            DB::commit();

            $this->info("   ✅ Pregunta creada exitosamente");
            $this->info("   🆔 ID de la pregunta: {$pregunta->id}");
            $this->info("   📝 Texto: {$pregunta->texto}");
            $this->info("   🏷️  Tipo: {$pregunta->tipo}");
            $this->info("   📊 Orden: {$pregunta->orden}");

            // Verificar métodos específicos
            $this->verificarMetodosEspecificos($pregunta);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("   ❌ Error creando pregunta de prueba: " . $e->getMessage());
            if ($this->option('debug')) {
                $this->error("   Stack trace: " . $e->getTraceAsString());
            }
        }
    }

    private function verificarMetodosEspecificos($pregunta)
    {
        $this->info("\n🔧 VERIFICANDO MÉTODOS ESPECÍFICOS:");

        try {
            // Verificar métodos de instancia
            $metodos = ['necesitaRespuestas', 'necesitaOpciones', 'getConfiguracionTipo', 'getNombreTipo', 'getIconoTipo'];

            foreach ($metodos as $metodo) {
                if (method_exists($pregunta, $metodo)) {
                    $resultado = $pregunta->$metodo();
                    $this->info("   ✅ {$metodo}(): " . (is_bool($resultado) ? ($resultado ? 'true' : 'false') : $resultado));
                } else {
                    $this->error("   ❌ Método {$metodo}() no existe");
                }
            }

            // Verificar scopes
            $scopes = ['scopeNecesitaRespuestas', 'scopeEsTexto', 'scopeEsFechaHora', 'scopeEsArchivo', 'scopeEsUbicacion', 'scopeEsEscala'];

            foreach ($scopes as $scope) {
                if (method_exists($pregunta, $scope)) {
                    $this->info("   ✅ {$scope}() existe");
                } else {
                    $this->error("   ❌ Scope {$scope}() no existe");
                }
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando métodos: " . $e->getMessage());
        }
    }

    private function mostrarRecomendaciones()
    {
        $this->info("\n💡 RECOMENDACIONES:");
        $this->info("   📌 Para crear una pregunta de prueba:");
        $this->info("      php artisan preguntas:diagnosticar-creacion --crear_prueba");
        $this->info("");
        $this->info("   📌 Para diagnosticar una encuesta específica:");
        $this->info("      php artisan preguntas:diagnosticar-creacion --encuesta_id=1");
        $this->info("");
        $this->info("   📌 Para ver información de debug:");
        $this->info("      php artisan preguntas:diagnosticar-creacion --debug");
        $this->info("");
        $this->info("   📌 Para ejecutar desde el módulo visual:");
        $this->info("      1. Ve a: Diagnósticos → Herramientas del Sistema → Pruebas del Sistema");
        $this->info("      2. Selecciona: 'Diagnosticar Columnas de Fecha'");
        $this->info("      3. Ejecuta la prueba");
        $this->info("");
        $this->info("   🔧 PROBLEMAS COMUNES Y SOLUCIONES:");
        $this->info("   - Si faltan columnas: Ejecuta la migración consolidada");
        $this->info("   - Si el tipo ENUM está mal: Ejecuta la migración de preguntas");
        $this->info("   - Si faltan métodos: Verifica el modelo Pregunta");
        $this->info("   - Si no hay datos: Ejecuta los seeders");
    }
}
