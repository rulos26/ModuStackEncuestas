<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Encuesta;
use App\Models\Pregunta;
use App\Models\Respuesta;

class DiagnosticarEncuestas extends Command
{
    protected $signature = 'encuestas:diagnostico';
    protected $description = 'Diagnostica el estado del módulo de encuestas';

    public function handle()
    {
        $this->info('=== DIAGNÓSTICO DEL MÓDULO DE ENCUESTAS ===');

        // Verificar conexión a base de datos
        $this->info('1. Verificando conexión a base de datos...');
        try {
            DB::connection()->getPdo();
            $this->info('✅ Conexión a base de datos exitosa');
        } catch (\Exception $e) {
            $this->error('❌ Error de conexión: ' . $e->getMessage());
            return 1;
        }

        // Verificar tablas
        $this->info('2. Verificando tablas...');
        $tablas = ['encuestas', 'preguntas', 'respuestas', 'logicas'];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla)) {
                $this->info("✅ Tabla '{$tabla}' existe");
            } else {
                $this->error("❌ Tabla '{$tabla}' NO existe");
            }
        }

        // Verificar modelos
        $this->info('3. Verificando modelos...');
        try {
            $encuestas = Encuesta::count();
            $this->info("✅ Modelo Encuesta funciona - {$encuestas} encuestas encontradas");
        } catch (\Exception $e) {
            $this->error('❌ Error en modelo Encuesta: ' . $e->getMessage());
        }

        try {
            $preguntas = Pregunta::count();
            $this->info("✅ Modelo Pregunta funciona - {$preguntas} preguntas encontradas");
        } catch (\Exception $e) {
            $this->error('❌ Error en modelo Pregunta: ' . $e->getMessage());
        }

        try {
            $respuestas = Respuesta::count();
            $this->info("✅ Modelo Respuesta funciona - {$respuestas} respuestas encontradas");
        } catch (\Exception $e) {
            $this->error('❌ Error en modelo Respuesta: ' . $e->getMessage());
        }

        // Verificar rutas
        $this->info('4. Verificando rutas...');
        $rutas = [
            'encuestas.index' => route('encuestas.index'),
            'encuestas.create' => route('encuestas.create'),
            'encuestas.respuestas.create' => route('encuestas.respuestas.create', 1),
        ];

        foreach ($rutas as $nombre => $ruta) {
            if (strpos($ruta, 'http') === 0) {
                $this->info("✅ Ruta '{$nombre}' generada correctamente");
            } else {
                $this->error("❌ Error en ruta '{$nombre}'");
            }
        }

        // Verificar vistas
        $this->info('5. Verificando vistas...');
        $vistas = [
            'encuestas.index',
            'encuestas.create',
            'encuestas.respuestas.create',
        ];

        foreach ($vistas as $vista) {
            if (view()->exists($vista)) {
                $this->info("✅ Vista '{$vista}' existe");
            } else {
                $this->error("❌ Vista '{$vista}' NO existe");
            }
        }

        $this->info('=== FIN DEL DIAGNÓSTICO ===');
        return 0;
    }
}
