<?php

namespace App\Console\Commands;

use App\Models\Pregunta;
use App\Models\Encuesta;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiagnosticarPreguntas extends Command
{
    protected $signature = 'preguntas:diagnosticar {--encuesta_id=} {--crear_prueba}';
    protected $description = 'Diagnostica problemas específicos con las preguntas';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO ESPECÍFICO DE PREGUNTAS');
        $this->info('=======================================');

        try {
            // 1. Verificar tabla preguntas
            $this->verificarTablaPreguntas();

            // 2. Verificar modelo Pregunta
            $this->verificarModeloPregunta();

            // 3. Verificar datos de prueba
            $this->verificarDatosPrueba();

            // 4. Verificar encuesta específica si se proporciona
            $encuestaId = $this->option('encuesta_id');
            if ($encuestaId) {
                $this->verificarEncuestaEspecifica($encuestaId);
            }

            // 5. Crear pregunta de prueba si se solicita
            if ($this->option('crear_prueba')) {
                $this->crearPreguntaPrueba($encuestaId);
            }

            $this->info("\n🎉 DIAGNÓSTICO COMPLETADO");
            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ ERROR DURANTE EL DIAGNÓSTICO:");
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function verificarTablaPreguntas()
    {
        $this->info("\n📋 VERIFICANDO TABLA PREGUNTAS:");

        if (!Schema::hasTable('preguntas')) {
            $this->error("   ❌ Tabla 'preguntas' no existe");
            throw new \Exception('La tabla preguntas no existe. Ejecuta las migraciones.');
        }

        $count = DB::table('preguntas')->count();
        $this->info("   ✅ Tabla 'preguntas' existe con {$count} registros");

        // Verificar estructura de la tabla
        $columnas = Schema::getColumnListing('preguntas');
        $columnasRequeridas = [
            'id', 'encuesta_id', 'texto', 'tipo', 'orden', 'obligatoria'
        ];

        foreach ($columnasRequeridas as $columna) {
            if (in_array($columna, $columnas)) {
                $this->info("   ✅ Columna '{$columna}' existe");
            } else {
                $this->error("   ❌ Columna '{$columna}' no existe");
            }
        }

        // Mostrar estructura completa
        $this->info("   📊 Estructura completa de la tabla:");
        foreach ($columnas as $columna) {
            $tipo = Schema::getColumnType('preguntas', $columna);
            $this->info("      • {$columna}: {$tipo}");
        }
    }

    private function verificarModeloPregunta()
    {
        $this->info("\n🎯 VERIFICANDO MODELO PREGUNTA:");

        if (!class_exists(Pregunta::class)) {
            $this->error("   ❌ Clase Pregunta no existe");
            throw new \Exception('El modelo Pregunta no existe');
        }

        $this->info("   ✅ Clase Pregunta existe");

        // Verificar métodos estáticos
        $metodosEstaticos = [
            'calcularOrdenAutomatico',
            'getTiposDisponibles',
            'todasTienenRespuestas'
        ];

        foreach ($metodosEstaticos as $metodo) {
            if (method_exists(Pregunta::class, $metodo)) {
                $this->info("   ✅ Método estático '{$metodo}' existe");
            } else {
                $this->error("   ❌ Método estático '{$metodo}' no existe");
            }
        }

        // Verificar métodos de instancia
        $metodosInstancia = [
            'necesitaRespuestas',
            'necesitaOpciones',
            'getConfiguracionTipo',
            'getNombreTipo',
            'getIconoTipo'
        ];

        foreach ($metodosInstancia as $metodo) {
            if (method_exists(Pregunta::class, $metodo)) {
                $this->info("   ✅ Método de instancia '{$metodo}' existe");
            } else {
                $this->error("   ❌ Método de instancia '{$metodo}' no existe");
            }
        }

        // Verificar relaciones
        $pregunta = new Pregunta();
        $relaciones = ['encuesta', 'respuestas', 'logica'];

        foreach ($relaciones as $relacion) {
            if (method_exists($pregunta, $relacion)) {
                $this->info("   ✅ Relación '{$relacion}' existe");
            } else {
                $this->error("   ❌ Relación '{$relacion}' no existe");
            }
        }

        // Verificar fillable
        $fillable = $pregunta->getFillable();
        $this->info("   📝 Campos fillable: " . implode(', ', $fillable));

        // Verificar tipos disponibles
        $tipos = Pregunta::getTiposDisponibles();
        $this->info("   🎨 Tipos disponibles: " . count($tipos) . " tipos");
        foreach ($tipos as $tipo => $config) {
            $this->info("      • {$tipo}: {$config['nombre']}");
        }
    }

    private function verificarDatosPrueba()
    {
        $this->info("\n📊 VERIFICANDO DATOS DE PRUEBA:");

        // Verificar encuestas
        $encuestas = Encuesta::count();
        if ($encuestas > 0) {
            $this->info("   ✅ Encuestas disponibles: {$encuestas}");
        } else {
            $this->warn("   ⚠️ No hay encuestas. Necesitas crear al menos una encuesta.");
        }

        // Verificar preguntas
        $preguntas = Pregunta::count();
        if ($preguntas > 0) {
            $this->info("   ✅ Preguntas existentes: {$preguntas}");
        } else {
            $this->info("   ℹ️ No hay preguntas creadas aún.");
        }

        // Mostrar encuestas disponibles
        $encuestasList = Encuesta::select('id', 'titulo', 'estado')->get();
        if ($encuestasList->isNotEmpty()) {
            $this->info("   📊 Encuestas disponibles:");
            foreach ($encuestasList as $encuesta) {
                $preguntasCount = $encuesta->preguntas->count();
                $this->info("      • ID: {$encuesta->id} - {$encuesta->titulo} ({$encuesta->estado}) - {$preguntasCount} preguntas");
            }
        }
    }

    private function verificarEncuestaEspecifica($encuestaId)
    {
        $this->info("\n🔍 VERIFICANDO ENCUESTA ESPECÍFICA (ID: {$encuestaId}):");

        try {
            $encuesta = Encuesta::with(['preguntas'])->find($encuestaId);

            if (!$encuesta) {
                $this->error("   ❌ Encuesta no encontrada");
                return;
            }

            $this->info("   ✅ Encuesta encontrada: {$encuesta->titulo}");
            $this->info("   📊 Estado: {$encuesta->estado}");
            $this->info("   ❓ Preguntas: {$encuesta->preguntas->count()}");

            // Verificar preguntas de la encuesta
            if ($encuesta->preguntas->isNotEmpty()) {
                $this->info("   📝 Preguntas de la encuesta:");
                foreach ($encuesta->preguntas as $pregunta) {
                    $this->info("      • ID: {$pregunta->id} - {$pregunta->texto} ({$pregunta->tipo}) - Orden: {$pregunta->orden}");
                }
            }

            // Verificar método puedeAvanzarA
            if (method_exists($encuesta, 'puedeAvanzarA')) {
                $puedeRespuestas = $encuesta->puedeAvanzarA('respuestas');
                $this->info("   ✅ puedeAvanzarA('respuestas'): " . ($puedeRespuestas ? 'Sí' : 'No'));
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando encuesta: " . $e->getMessage());
        }
    }

    private function crearPreguntaPrueba($encuestaId = null)
    {
        $this->info("\n🔧 CREANDO PREGUNTA DE PRUEBA:");

        // Obtener encuesta
        if (!$encuestaId) {
            $encuesta = Encuesta::first();
            if (!$encuesta) {
                throw new \Exception('No hay encuestas disponibles. Crea una encuesta primero.');
            }
            $encuestaId = $encuesta->id;
        } else {
            $encuesta = Encuesta::find($encuestaId);
            if (!$encuesta) {
                throw new \Exception("Encuesta con ID {$encuestaId} no encontrada.");
            }
        }

        $this->info("   🏢 Usando encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");

        // Calcular orden automático
        $orden = Pregunta::calcularOrdenAutomatico($encuestaId);
        $this->info("   📊 Orden calculado: {$orden}");

        // Datos de prueba
        $datosPrueba = [
            'encuesta_id' => $encuestaId,
            'texto' => 'Pregunta de prueba - ' . now()->format('Y-m-d H:i:s'),
            'descripcion' => 'Descripción de prueba',
            'tipo' => 'respuesta_corta',
            'orden' => $orden,
            'obligatoria' => true
        ];

        $this->info("   📝 Datos de prueba preparados");

        // Crear pregunta
        DB::beginTransaction();

        try {
            $pregunta = Pregunta::create($datosPrueba);

            DB::commit();

            $this->info("   ✅ Pregunta creada exitosamente");
            $this->info("   🆔 ID de la pregunta: {$pregunta->id}");
            $this->info("   📊 Texto: {$pregunta->texto}");
            $this->info("   🎨 Tipo: {$pregunta->tipo}");
            $this->info("   📅 Orden: {$pregunta->orden}");
            $this->info("   ⚠️ Obligatoria: " . ($pregunta->obligatoria ? 'Sí' : 'No'));

            // Verificar métodos del modelo
            $this->verificarMetodosPregunta($pregunta);

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Error creando pregunta: ' . $e->getMessage());
        }
    }

    private function verificarMetodosPregunta($pregunta)
    {
        $this->info("\n🔧 VERIFICANDO MÉTODOS DE LA PREGUNTA:");

        // Verificar método necesitaRespuestas
        if (method_exists($pregunta, 'necesitaRespuestas')) {
            $necesitaRespuestas = $pregunta->necesitaRespuestas();
            $this->info("   ✅ necesitaRespuestas(): " . ($necesitaRespuestas ? 'Sí' : 'No'));
        } else {
            $this->error("   ❌ Método necesitaRespuestas() no existe");
        }

        // Verificar método necesitaOpciones
        if (method_exists($pregunta, 'necesitaOpciones')) {
            $necesitaOpciones = $pregunta->necesitaOpciones();
            $this->info("   ✅ necesitaOpciones(): " . ($necesitaOpciones ? 'Sí' : 'No'));
        } else {
            $this->error("   ❌ Método necesitaOpciones() no existe");
        }

        // Verificar método getNombreTipo
        if (method_exists($pregunta, 'getNombreTipo')) {
            $nombreTipo = $pregunta->getNombreTipo();
            $this->info("   ✅ getNombreTipo(): {$nombreTipo}");
        } else {
            $this->error("   ❌ Método getNombreTipo() no existe");
        }

        // Verificar relaciones
        $this->info("   🔗 Verificando relaciones:");
        $this->info("      • encuesta: " . ($pregunta->encuesta ? '✅' : '❌'));
        $this->info("      • respuestas: " . $pregunta->respuestas->count() . " respuestas");
        $this->info("      • logica: " . $pregunta->logica->count() . " lógicas");
    }
}
