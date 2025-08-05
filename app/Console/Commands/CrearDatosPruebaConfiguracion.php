<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmpresasCliente;
use App\Models\Encuesta;
use App\Models\ConfiguracionEnvio;
use App\Models\Empleado;

class CrearDatosPruebaConfiguracion extends Command
{
    protected $signature = 'crear:datos-prueba-configuracion';
    protected $description = 'Crear datos de prueba para configuración de envío';

    public function handle()
    {
        $this->info('Creando datos de prueba para configuración de envío...');

        // Crear empresa de prueba
        $empresa = EmpresasCliente::firstOrCreate(
            ['nombre' => 'Empresa de Prueba'],
            [
                'nombre' => 'Empresa de Prueba',
                'nit' => '900123456-8',
                'telefono' => '3001234567',
                'correo_electronico' => 'contacto@empresaprueba.com',
                'direccion' => 'Calle 123 #45-67',
                'nombre_contacto' => 'Juan Director'
            ]
        );

        $this->info("Empresa creada: {$empresa->nombre} (ID: {$empresa->id})");

        // Crear encuesta de prueba usando empresa_id = 1 (que debe existir)
        $encuesta = Encuesta::firstOrCreate(
            ['titulo' => 'Encuesta de Prueba'],
            [
                'titulo' => 'Encuesta de Prueba',
                'descripcion' => 'Encuesta para pruebas del sistema',
                'empresa_id' => 1, // Usar empresa_id = 1 que debe existir
                'user_id' => 1, // Usuario por defecto
                'fecha_inicio' => now(),
                'fecha_fin' => now()->addDays(30),
                'estado' => 'publicada'
            ]
        );

        $this->info("Encuesta creada: {$encuesta->titulo} (ID: {$encuesta->id})");

        // Crear empleados de prueba
        $empleados = [
            [
                'nombre' => 'Juan Pérez',
                'correo_electronico' => 'juan.perez@empresaprueba.com',
                'telefono' => '3001234567',
                'empresa_id' => $empresa->id
            ],
            [
                'nombre' => 'María García',
                'correo_electronico' => 'maria.garcia@empresaprueba.com',
                'telefono' => '3001234568',
                'empresa_id' => $empresa->id
            ],
            [
                'nombre' => 'Carlos López',
                'correo_electronico' => 'carlos.lopez@empresaprueba.com',
                'telefono' => '3001234569',
                'empresa_id' => $empresa->id
            ]
        ];

        foreach ($empleados as $empleadoData) {
            $empleado = Empleado::firstOrCreate(
                ['correo_electronico' => $empleadoData['correo_electronico']],
                $empleadoData
            );
            $this->info("Empleado creado: {$empleado->nombre}");
        }

        // Crear configuración de envío
        $configuracion = ConfiguracionEnvio::firstOrCreate(
            [
                'empresa_id' => $empresa->id,
                'encuesta_id' => $encuesta->id
            ],
            [
                'empresa_id' => $empresa->id,
                'encuesta_id' => $encuesta->id,
                'nombre_remitente' => 'Sistema de Encuestas',
                'correo_remitente' => 'encuestas@empresaprueba.com',
                'asunto' => 'Encuesta de Satisfacción',
                'cuerpo_mensaje' => 'Hola, te invitamos a participar en nuestra encuesta.',
                'tipo_envio' => 'programado',
                'fecha_envio' => now()->addDays(1),
                'hora_envio' => now()->addHours(2),
                'numero_bloques' => 2,
                'correo_prueba' => 'prueba@empresaprueba.com',
                'activo' => true
            ]
        );

        $this->info("Configuración creada: ID {$configuracion->id}");
        $this->info("Empresa ID: {$configuracion->empresa_id}");
        $this->info("Encuesta ID: {$configuracion->encuesta_id}");

        $this->info('✅ Datos de prueba creados exitosamente!');
        $this->info("URL para probar: /configuracion-envio/resumen?empresa_id={$empresa->id}");
    }
}
