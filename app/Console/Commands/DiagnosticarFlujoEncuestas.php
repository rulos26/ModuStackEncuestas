<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DiagnosticarFlujoEncuestas extends Command
{
    protected $signature = 'encuestas:diagnosticar {--encuesta_id=}';
    protected $description = 'Diagnostica problemas en el flujo de trabajo de encuestas';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO DEL FLUJO DE TRABAJO DE ENCUESTAS');
        $this->info('==================================================');

        try {
            // 1. Verificar conexión a base de datos
            $this->verificarConexionBD();

            // 2. Verificar tablas necesarias
            $this->verificarTablas();

            // 3. Verificar modelos
            $this->verificarModelos();

            // 4. Verificar datos de prueba
            $this->verificarDatosPrueba();

            // 5. Verificar encuesta específica si se proporciona
            $encuestaId = $this->option('encuesta_id');
            if ($encuestaId) {
                $this->verificarEncuestaEspecifica($encuestaId);
            }

            $this->info("\n🎉 DIAGNÓSTICO COMPLETADO");
            return 0;

        } catch (\Exception $e) {
            $this->error("\n❌ ERROR DURANTE EL DIAGNÓSTICO:");
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function verificarConexionBD()
    {
        $this->info("\n🔌 VERIFICANDO CONEXIÓN A BASE DE DATOS:");

        try {
            DB::connection()->getPdo();
            $this->info("   ✅ Conexión exitosa");

            $databaseName = DB::connection()->getDatabaseName();
            $this->info("   📊 Base de datos: {$databaseName}");

        } catch (\Exception $e) {
            $this->error("   ❌ Error de conexión: " . $e->getMessage());
            $this->error("   💡 Verifica la configuración en .env");
            throw $e;
        }
    }

    private function verificarTablas()
    {
        $this->info("\n📋 VERIFICANDO TABLAS NECESARIAS:");

        $tablasRequeridas = [
            'encuestas',
            'preguntas',
            'respuestas',
            'empresa',
            'users',
            'migrations'
        ];

        foreach ($tablasRequeridas as $tabla) {
            if (Schema::hasTable($tabla)) {
                $count = DB::table($tabla)->count();
                $this->info("   ✅ {$tabla}: {$count} registros");
            } else {
                $this->error("   ❌ {$tabla}: Tabla no existe");
            }
        }
    }

    private function verificarModelos()
    {
        $this->info("\n🎯 VERIFICANDO MODELOS:");

        $modelos = [
            'Encuesta' => Encuesta::class,
            'Pregunta' => Pregunta::class,
            'Respuesta' => Respuesta::class,
            'Empresa' => Empresa::class,
            'User' => User::class
        ];

        foreach ($modelos as $nombre => $clase) {
            if (class_exists($clase)) {
                try {
                    $count = $clase::count();
                    $this->info("   ✅ {$nombre}: {$count} registros");
                } catch (\Exception $e) {
                    $this->error("   ❌ {$nombre}: Error al consultar - " . $e->getMessage());
                }
            } else {
                $this->error("   ❌ {$nombre}: Clase no existe");
            }
        }
    }

    private function verificarDatosPrueba()
    {
        $this->info("\n📊 VERIFICANDO DATOS DE PRUEBA:");

        // Verificar empresas
        $empresas = Empresa::count();
        if ($empresas > 0) {
            $this->info("   ✅ Empresas: {$empresas} disponibles");
        } else {
            $this->warn("   ⚠️ No hay empresas. Necesitas crear al menos una empresa.");
        }

        // Verificar usuarios
        $usuarios = User::count();
        if ($usuarios > 0) {
            $this->info("   ✅ Usuarios: {$usuarios} registrados");
        } else {
            $this->warn("   ⚠️ No hay usuarios. Necesitas crear al menos un usuario.");
        }

        // Verificar encuestas
        $encuestas = Encuesta::count();
        if ($encuestas > 0) {
            $this->info("   ✅ Encuestas: {$encuestas} creadas");
        } else {
            $this->info("   ℹ️ No hay encuestas creadas aún.");
        }
    }

    private function verificarEncuestaEspecifica($encuestaId)
    {
        $this->info("\n🔍 VERIFICANDO ENCUESTA ESPECÍFICA (ID: {$encuestaId}):");

        try {
            $encuesta = Encuesta::with(['preguntas', 'empresa', 'user'])->find($encuestaId);

            if (!$encuesta) {
                $this->error("   ❌ Encuesta no encontrada");
                return;
            }

            $this->info("   ✅ Encuesta encontrada: {$encuesta->titulo}");
            $this->info("   📊 Estado: {$encuesta->estado}");
            $this->info("   👤 Propietario: " . ($encuesta->user->name ?? 'N/A'));
            $this->info("   🏢 Empresa: " . ($encuesta->empresa->nombre_legal ?? 'N/A'));
            $this->info("   ❓ Preguntas: {$encuesta->preguntas->count()}");

            // Verificar flujo de trabajo
            $this->verificarFlujoTrabajo($encuesta);

        } catch (\Exception $e) {
            $this->error("   ❌ Error verificando encuesta: " . $e->getMessage());
        }
    }

    private function verificarFlujoTrabajo($encuesta)
    {
        $this->info("\n⚙️ VERIFICANDO FLUJO DE TRABAJO:");

        // Verificar si tiene preguntas
        if ($encuesta->preguntas->isEmpty()) {
            $this->warn("   ⚠️ No tiene preguntas - Paso 1 pendiente");
        } else {
            $this->info("   ✅ Tiene preguntas");

            // Verificar respuestas
            $preguntasSinRespuestas = $encuesta->preguntas->filter(function($pregunta) {
                return $pregunta->necesitaRespuestas() && $pregunta->respuestas->isEmpty();
            });

            if ($preguntasSinRespuestas->isNotEmpty()) {
                $this->warn("   ⚠️ Algunas preguntas no tienen respuestas");
            } else {
                $this->info("   ✅ Todas las preguntas tienen respuestas");
            }
        }

        // Verificar método puedeAvanzarA
        if (method_exists($encuesta, 'puedeAvanzarA')) {
            $this->info("   ✅ Método puedeAvanzarA disponible");
        } else {
            $this->error("   ❌ Método puedeAvanzarA no existe");
        }

        // Verificar método obtenerProgresoConfiguracion
        if (method_exists($encuesta, 'obtenerProgresoConfiguracion')) {
            $this->info("   ✅ Método obtenerProgresoConfiguracion disponible");
        } else {
            $this->error("   ❌ Método obtenerProgresoConfiguracion no existe");
        }
    }
}
