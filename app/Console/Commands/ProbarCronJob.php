<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ConfiguracionEnvio;
use App\Models\Empleado;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProbarCronJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'probar:cron-job {--debug : Mostrar información detallada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba el funcionamiento del cron job y sistema de envío programado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 INICIANDO PRUEBAS DEL CRON JOB Y SISTEMA DE ENVÍO PROGRAMADO');
        $this->line('');

        // 1. Verificar conexión a base de datos
        $this->verificarConexionBD();

        // 2. Verificar configuraciones programadas
        $this->verificarConfiguracionesProgramadas();

        // 3. Verificar empleados disponibles
        $this->verificarEmpleados();

        // 4. Simular verificación de envíos programados
        $this->simularVerificacionEnvios();

        // 5. Probar job de envío
        $this->probarJobEnvio();

        $this->info('✅ PRUEBAS COMPLETADAS');
    }

    private function verificarConexionBD()
    {
        $this->info('📊 1. Verificando conexión a base de datos...');

        try {
            DB::connection()->getPdo();
            $this->info('   ✅ Conexión exitosa a: ' . DB::connection()->getDatabaseName());
        } catch (\Exception $e) {
            $this->error('   ❌ Error de conexión: ' . $e->getMessage());
            return false;
        }

        $this->line('');
        return true;
    }

    private function verificarConfiguracionesProgramadas()
    {
        $this->info('📧 2. Verificando configuraciones programadas...');

        try {
            $total = ConfiguracionEnvio::count();
            $programadas = ConfiguracionEnvio::where('tipo_envio', 'programado')->count();
            $pendientes = ConfiguracionEnvio::where('tipo_envio', 'programado')
                ->where('estado_programacion', 'pendiente')
                ->count();
            $activas = ConfiguracionEnvio::where('activo', true)->count();

            $this->info("   📈 Total configuraciones: {$total}");
            $this->info("   📈 Configuraciones programadas: {$programadas}");
            $this->info("   📈 Configuraciones pendientes: {$pendientes}");
            $this->info("   📈 Configuraciones activas: {$activas}");

            if ($programadas > 0) {
                $this->info('   📋 Detalles de configuraciones programadas:');
                $configuraciones = ConfiguracionEnvio::where('tipo_envio', 'programado')
                    ->with(['encuesta', 'empresa'])
                    ->get();

                foreach ($configuraciones as $config) {
                    $this->line("      • ID: {$config->id} | Encuesta: {$config->encuesta->titulo} | Estado: {$config->estado_programacion}");
                    $this->line("        Fecha: {$config->fecha_envio} | Hora: {$config->hora_envio} | Destinatarios: {$config->tipo_destinatario}");
                }
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error verificando configuraciones: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function verificarEmpleados()
    {
        $this->info('👥 3. Verificando empleados disponibles...');

        try {
            $totalEmpleados = Empleado::count();
            $this->info("   📈 Total empleados: {$totalEmpleados}");

            if ($totalEmpleados > 0) {
                $empresas = Empleado::select('empresa_id', DB::raw('count(*) as total'))
                    ->groupBy('empresa_id')
                    ->get();

                $this->info('   📋 Empleados por empresa:');
                foreach ($empresas as $empresa) {
                    $this->line("      • Empresa ID: {$empresa->empresa_id} | Empleados: {$empresa->total}");
                }

                // Mostrar algunos empleados de ejemplo
                $empleados = Empleado::take(3)->get();
                $this->info('   📋 Ejemplos de empleados:');
                foreach ($empleados as $empleado) {
                    $this->line("      • {$empleado->nombre} {$empleado->apellido} ({$empleado->correo_electronico})");
                }
            } else {
                $this->warn('   ⚠️ No hay empleados registrados');
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error verificando empleados: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function simularVerificacionEnvios()
    {
        $this->info('⏰ 4. Simulando verificación de envíos programados...');

        try {
            $ahora = now();
            $this->info("   🕐 Hora actual: {$ahora->format('Y-m-d H:i:s')}");

            $configuracionesPendientes = ConfiguracionEnvio::where('tipo_envio', 'programado')
                ->where('estado_programacion', 'pendiente')
                ->where('activo', true)
                ->get();

            $this->info("   📈 Configuraciones pendientes encontradas: {$configuracionesPendientes->count()}");

            foreach ($configuracionesPendientes as $config) {
                $fechaHoraEnvio = $config->fecha_envio . ' ' . $config->hora_envio;
                $fechaEnvio = \Carbon\Carbon::parse($fechaHoraEnvio);

                $this->line("   📋 Configuración ID: {$config->id}");
                $this->line("      Fecha/Hora programada: {$fechaEnvio->format('Y-m-d H:i:s')}");
                $this->line("      ¿Lista para envío?: " . ($fechaEnvio <= $ahora ? '✅ SÍ' : '⏳ NO'));

                if ($fechaEnvio <= $ahora) {
                    $this->info("      🚀 Esta configuración debería enviarse ahora");
                }
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error simulando verificación: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function probarJobEnvio()
    {
        $this->info('📤 5. Probando job de envío...');

        try {
            // Buscar una configuración programada para probar
            $configuracion = ConfiguracionEnvio::where('tipo_envio', 'programado')
                ->where('activo', true)
                ->first();

            if ($configuracion) {
                $this->info("   📋 Probando con configuración ID: {$configuracion->id}");

                // Simular el job sin enviar realmente
                $destinatarios = $this->obtenerDestinatarios($configuracion);
                $this->info("   📈 Destinatarios encontrados: " . count($destinatarios));

                if (count($destinatarios) > 0) {
                    $this->info("   📋 Primeros 3 destinatarios:");
                    foreach (array_slice($destinatarios, 0, 3) as $destinatario) {
                        $this->line("      • {$destinatario['nombre']} ({$destinatario['email']})");
                    }
                }

                // Verificar si el job se puede dispatch
                try {
                    \App\Jobs\EnviarCorreosProgramados::dispatch($configuracion->id);
                    $this->info("   ✅ Job dispatchado correctamente");
                } catch (\Exception $e) {
                    $this->error("   ❌ Error dispatchando job: " . $e->getMessage());
                }
            } else {
                $this->warn("   ⚠️ No hay configuraciones programadas para probar");
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error probando job: ' . $e->getMessage());
        }

        $this->line('');
    }

    private function obtenerDestinatarios($configuracion)
    {
        $destinatarios = [];

        if ($configuracion->tipo_destinatario === 'empleados') {
            $empleados = Empleado::where('empresa_id', $configuracion->empresa_id)
                ->select('nombre', 'apellido', 'correo_electronico')
                ->get();

            foreach ($empleados as $empleado) {
                $destinatarios[] = [
                    'nombre' => $empleado->nombre . ' ' . $empleado->apellido,
                    'email' => $empleado->correo_electronico
                ];
            }
        }

        return $destinatarios;
    }
}
