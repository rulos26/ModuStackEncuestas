<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ConfiguracionEnvioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProbarGuardarDestinatarios extends Command
{
    protected $signature = 'probar:guardar-destinatarios {configuracion_id}';
    protected $description = 'Probar la validación del método guardarDestinatarios';

    public function handle()
    {
        $configuracionId = $this->argument('configuracion_id');

        $this->info('🧪 PROBANDO GUARDAR DESTINATARIOS');
        $this->line('');

        // Simular datos que se envían desde el JavaScript
        $datosSimulados = [
            'configuracion_id' => $configuracionId,
            'empleados' => [4, 5, 6], // IDs correctos de empleados (Empresa de Prueba)
            'fecha_envio' => now()->addDays(1)->format('Y-m-d'),
            'hora_envio' => '09:00',
            'numero_bloques' => 2,
            'correo_prueba' => 'prueba@empresa.com'
        ];

        $this->info('📋 DATOS SIMULADOS:');
        foreach ($datosSimulados as $key => $value) {
            $this->line("   {$key}: " . (is_array($value) ? json_encode($value) : $value));
        }
        $this->line('');

        // Probar validación
        $this->info('🔍 PROBANDO VALIDACIÓN:');

        $validator = Validator::make($datosSimulados, [
            'configuracion_id' => 'required|exists:configuracion_envios,id',
            'empleados' => 'required|array|min:1',
            'empleados.*' => 'exists:empleados,id',
            'fecha_envio' => 'required|date|after_or_equal:today',
            'hora_envio' => 'required|date_format:H:i',
            'numero_bloques' => 'required|integer|min:1|max:10',
            'correo_prueba' => 'nullable|email'
        ]);

        if ($validator->fails()) {
            $this->error('❌ VALIDACIÓN FALLIDA:');
            foreach ($validator->errors()->all() as $error) {
                $this->line("   - {$error}");
            }
        } else {
            $this->info('✅ VALIDACIÓN EXITOSA');
        }
        $this->line('');

        // Verificar que la configuración existe
        $this->info('🔍 VERIFICANDO CONFIGURACIÓN:');
        try {
            $configuracion = \App\Models\ConfiguracionEnvio::find($configuracionId);
            if ($configuracion) {
                $this->info("   ✅ Configuración ID {$configuracionId} existe");
                $this->line("   Empresa ID: {$configuracion->empresa_id}");
                $this->line("   Encuesta ID: {$configuracion->encuesta_id}");
            } else {
                $this->error("   ❌ Configuración ID {$configuracionId} NO existe");
            }
        } catch (\Exception $e) {
            $this->error("   ❌ Error: " . $e->getMessage());
        }
        $this->line('');

        // Verificar que los empleados existen
        $this->info('👥 VERIFICANDO EMPLEADOS:');
        foreach ($datosSimulados['empleados'] as $empleadoId) {
            try {
                $empleado = \App\Models\Empleado::find($empleadoId);
                if ($empleado) {
                    $this->info("   ✅ Empleado ID {$empleadoId}: {$empleado->nombre}");
                } else {
                    $this->error("   ❌ Empleado ID {$empleadoId} NO existe");
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Error verificando empleado {$empleadoId}: " . $e->getMessage());
            }
        }
        $this->line('');

        // Verificar formato de fecha
        $this->info('📅 VERIFICANDO FECHA:');
        $fecha = $datosSimulados['fecha_envio'];
        $hoy = now()->format('Y-m-d');
        $this->line("   Fecha enviada: {$fecha}");
        $this->line("   Fecha hoy: {$hoy}");
        $this->line("   ¿Es después o igual a hoy?: " . ($fecha >= $hoy ? 'Sí' : 'No'));
        $this->line('');

        // Verificar formato de hora
        $this->info('⏰ VERIFICANDO HORA:');
        $hora = $datosSimulados['hora_envio'];
        $this->line("   Hora enviada: {$hora}");
        $this->line("   Formato correcto (HH:MM): " . (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora) ? 'Sí' : 'No'));
        $this->line('');

        // Verificar número de bloques
        $this->info('📦 VERIFICANDO BLOQUES:');
        $bloques = $datosSimulados['numero_bloques'];
        $this->line("   Bloques: {$bloques}");
        $this->line("   ¿Está entre 1 y 10?: " . ($bloques >= 1 && $bloques <= 10 ? 'Sí' : 'No'));
        $this->line('');

        // Verificar correo de prueba
        $this->info('📧 VERIFICANDO CORREO:');
        $correo = $datosSimulados['correo_prueba'];
        $this->line("   Correo: {$correo}");
        $this->line("   ¿Es email válido?: " . (filter_var($correo, FILTER_VALIDATE_EMAIL) ? 'Sí' : 'No'));
        $this->line('');

        $this->info('✅ Prueba completada');
        return 0;
    }
}
