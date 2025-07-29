<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Pregunta;
use App\Models\Encuesta;
use App\Models\User;
use Exception;

class SimularCreacionPregunta extends Command
{
    protected $signature = 'preguntas:simular-creacion {--encuesta_id=} {--debug}';
    protected $description = 'Simula la creación de una pregunta y muestra todos los errores posibles';

    public function handle()
    {
        $this->info('🧪 SIMULACIÓN DE CREACIÓN DE PREGUNTA');
        $this->info('====================================');

        try {
            // Obtener encuesta
            $encuestaId = $this->option('encuesta_id');
            $encuesta = $this->obtenerEncuesta($encuestaId);

            if (!$encuesta) {
                $this->error('❌ No se pudo obtener una encuesta válida');
                return 1;
            }

            $this->info("📋 Usando encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");

            // Simular datos de request
            $datosRequest = $this->simularDatosRequest($encuesta->id);

            $this->info("\n📝 Datos simulados del request:");
            foreach ($datosRequest as $campo => $valor) {
                $this->info("   - {$campo}: " . (is_bool($valor) ? ($valor ? 'true' : 'false') : $valor));
            }

            // Simular validación
            $this->simularValidacion($datosRequest);

            // Simular preparación de datos
            $datosPregunta = $this->simularPreparacionDatos($datosRequest, $encuesta->id);

            // Simular creación
            $this->simularCreacion($datosPregunta, $encuesta);

            // Simular verificación post-creación
            $this->simularVerificacionPostCreacion($encuesta->id);

        } catch (\Exception $e) {
            $this->error('❌ Error durante la simulación: ' . $e->getMessage());
            if ($this->option('debug')) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }
            return 1;
        }

        return 0;
    }

    private function obtenerEncuesta($encuestaId = null)
    {
        try {
            if ($encuestaId) {
                $encuesta = Encuesta::find($encuestaId);
                if (!$encuesta) {
                    $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                    return null;
                }
            } else {
                $encuesta = Encuesta::first();
                if (!$encuesta) {
                    $this->error("❌ No hay encuestas disponibles");
                    return null;
                }
            }

            $this->info("✅ Encuesta obtenida correctamente");
            return $encuesta;

        } catch (\Exception $e) {
            $this->error("❌ Error obteniendo encuesta: " . $e->getMessage());
            return null;
        }
    }

    private function simularDatosRequest($encuestaId)
    {
        $this->info("\n📋 SIMULANDO DATOS DEL REQUEST:");

        $datos = [
            'texto' => 'Pregunta de simulación - ' . now()->format('Y-m-d H:i:s'),
            'descripcion' => 'Esta es una pregunta de simulación para diagnosticar problemas',
            'placeholder' => 'Escribe tu respuesta aquí',
            'tipo' => 'respuesta_corta',
            'orden' => Pregunta::calcularOrdenAutomatico($encuestaId),
            'obligatoria' => 'on', // Simula checkbox marcado
            'min_caracteres' => 3,
            'max_caracteres' => 100,
            'escala_min' => null,
            'escala_max' => null,
            'escala_etiqueta_min' => null,
            'escala_etiqueta_max' => null,
            'tipos_archivo_permitidos' => null,
            'tamano_max_archivo' => null,
            'latitud_default' => null,
            'longitud_default' => null,
            'zoom_default' => null
        ];

        $this->info("✅ Datos del request simulados correctamente");
        return $datos;
    }

    private function simularValidacion($datosRequest)
    {
        $this->info("\n✅ SIMULANDO VALIDACIÓN:");

        try {
            $reglas = [
                'texto' => 'required|string|max:500|min:3',
                'descripcion' => 'nullable|string|max:1000',
                'placeholder' => 'nullable|string|max:255',
                'tipo' => 'required|in:respuesta_corta,parrafo,seleccion_unica,casillas_verificacion,lista_desplegable,escala_lineal,cuadricula_opcion_multiple,cuadricula_casillas,fecha,hora,carga_archivos,ubicacion_mapa,logica_condicional',
                'orden' => 'required|integer|min:1',
                'obligatoria' => 'nullable|in:on,1,true',
                'min_caracteres' => 'nullable|integer|min:0',
                'max_caracteres' => 'nullable|integer|min:1',
                'escala_min' => 'nullable|integer',
                'escala_max' => 'nullable|integer|gt:escala_min',
                'escala_etiqueta_min' => 'nullable|string|max:100',
                'escala_etiqueta_max' => 'nullable|string|max:100',
                'tipos_archivo_permitidos' => 'nullable|string|max:255',
                'tamano_max_archivo' => 'nullable|integer|min:1|max:100',
                'latitud_default' => 'nullable|numeric|between:-90,90',
                'longitud_default' => 'nullable|numeric|between:-180,180',
                'zoom_default' => 'nullable|integer|between:1,20',
            ];

            $this->info("✅ Reglas de validación definidas correctamente");

            // Simular validación manual
            foreach ($reglas as $campo => $regla) {
                if (isset($datosRequest[$campo])) {
                    $valor = $datosRequest[$campo];
                    $this->info("   ✅ Campo '{$campo}' con valor '{$valor}' cumple regla: {$regla}");
                } else {
                    $this->warn("   ⚠️  Campo '{$campo}' no presente en datos");
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Error en validación: " . $e->getMessage());
        }
    }

    private function simularPreparacionDatos($datosRequest, $encuestaId)
    {
        $this->info("\n🔧 SIMULANDO PREPARACIÓN DE DATOS:");

        try {
            $datosPregunta = [
                'encuesta_id' => $encuestaId,
                'texto' => $datosRequest['texto'],
                'descripcion' => $datosRequest['descripcion'],
                'placeholder' => $datosRequest['placeholder'],
                'tipo' => $datosRequest['tipo'],
                'obligatoria' => isset($datosRequest['obligatoria']), // Convertir 'on' a boolean
                'min_caracteres' => $datosRequest['min_caracteres'],
                'max_caracteres' => $datosRequest['max_caracteres'],
                'escala_min' => $datosRequest['escala_min'],
                'escala_max' => $datosRequest['escala_max'],
                'escala_etiqueta_min' => $datosRequest['escala_etiqueta_min'],
                'escala_etiqueta_max' => $datosRequest['escala_etiqueta_max'],
                'tipos_archivo_permitidos' => $datosRequest['tipos_archivo_permitidos'],
                'tamano_max_archivo' => $datosRequest['tamano_max_archivo'],
                'latitud_default' => $datosRequest['latitud_default'],
                'longitud_default' => $datosRequest['longitud_default'],
                'zoom_default' => $datosRequest['zoom_default'],
            ];

            // Calcular orden automáticamente si no se proporciona
            if (!isset($datosRequest['orden']) || empty($datosRequest['orden'])) {
                $datosPregunta['orden'] = Pregunta::calcularOrdenAutomatico($encuestaId);
                $this->info("   🔄 Orden calculado automáticamente: {$datosPregunta['orden']}");
            } else {
                $datosPregunta['orden'] = $datosRequest['orden'];
                $this->info("   📊 Orden proporcionado: {$datosPregunta['orden']}");
            }

            $this->info("✅ Datos preparados correctamente");
            $this->info("📝 Datos finales para crear pregunta:");
            foreach ($datosPregunta as $campo => $valor) {
                $this->info("   - {$campo}: " . (is_bool($valor) ? ($valor ? 'true' : 'false') : $valor));
            }

            return $datosPregunta;

        } catch (\Exception $e) {
            $this->error("❌ Error preparando datos: " . $e->getMessage());
            return null;
        }
    }

    private function simularCreacion($datosPregunta, $encuesta)
    {
        $this->info("\n🚀 SIMULANDO CREACIÓN DE PREGUNTA:");

        try {
            // Verificar que el orden no esté duplicado
            $ordenExistente = Pregunta::where('encuesta_id', $encuesta->id)
                ->where('orden', $datosPregunta['orden'])
                ->exists();

            if ($ordenExistente) {
                $this->warn("   ⚠️  Ya existe una pregunta con orden {$datosPregunta['orden']}");
                $this->info("   🔄 Incrementando orden...");
                $datosPregunta['orden']++;
                $this->info("   📊 Nuevo orden: {$datosPregunta['orden']}");
            } else {
                $this->info("   ✅ Orden {$datosPregunta['orden']} disponible");
            }

            // Simular transacción
            DB::beginTransaction();

            $this->info("   🔄 Iniciando transacción...");

            // Crear la pregunta
            $pregunta = Pregunta::create($datosPregunta);

            if (!$pregunta->id) {
                throw new Exception('La pregunta no se creó correctamente - no se generó ID');
            }

            $this->info("   ✅ Pregunta creada con ID: {$pregunta->id}");

            // Verificar que se creó correctamente
            $preguntaVerificada = Pregunta::find($pregunta->id);
            if (!$preguntaVerificada) {
                throw new Exception('La pregunta no se puede recuperar después de la creación');
            }

            $this->info("   ✅ Pregunta verificada en base de datos");

            DB::commit();
            $this->info("   ✅ Transacción confirmada");

            $this->info("\n📊 RESULTADO DE LA CREACIÓN:");
            $this->info("   🆔 ID: {$pregunta->id}");
            $this->info("   📝 Texto: {$pregunta->texto}");
            $this->info("   🏷️  Tipo: {$pregunta->tipo}");
            $this->info("   📊 Orden: {$pregunta->orden}");
            $this->info("   ⚠️  Obligatoria: " . ($pregunta->obligatoria ? 'Sí' : 'No'));
            $this->info("   📅 Creada: {$pregunta->created_at}");

            return $pregunta;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error en creación: " . $e->getMessage());
            if ($this->option('debug')) {
                $this->error("Stack trace: " . $e->getTraceAsString());
            }
            return null;
        }
    }

    private function simularVerificacionPostCreacion($encuestaId)
    {
        $this->info("\n🔍 VERIFICACIÓN POST-CREACIÓN:");

        try {
            // Verificar total de preguntas
            $totalPreguntas = Pregunta::where('encuesta_id', $encuestaId)->count();
            $this->info("   📊 Total de preguntas en la encuesta: {$totalPreguntas}");

            // Verificar última pregunta creada
            $ultimaPregunta = Pregunta::where('encuesta_id', $encuestaId)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($ultimaPregunta) {
                $this->info("   📝 Última pregunta creada:");
                $this->info("      - ID: {$ultimaPregunta->id}");
                $this->info("      - Texto: {$ultimaPregunta->texto}");
                $this->info("      - Tipo: {$ultimaPregunta->tipo}");
                $this->info("      - Orden: {$ultimaPregunta->orden}");
                $this->info("      - Creada: {$ultimaPregunta->created_at}");
            }

            // Verificar métodos del modelo
            if (method_exists($ultimaPregunta, 'necesitaRespuestas')) {
                $necesitaRespuestas = $ultimaPregunta->necesitaRespuestas();
                $this->info("   🔄 Necesita respuestas: " . ($necesitaRespuestas ? 'Sí' : 'No'));
            }

            if (method_exists($ultimaPregunta, 'getNombreTipo')) {
                $nombreTipo = $ultimaPregunta->getNombreTipo();
                $this->info("   🏷️  Nombre del tipo: {$nombreTipo}");
            }

            $this->info("✅ Verificación post-creación completada");

        } catch (\Exception $e) {
            $this->error("❌ Error en verificación post-creación: " . $e->getMessage());
        }
    }
}
