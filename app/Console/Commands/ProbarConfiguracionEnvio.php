<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ConfiguracionEnvioController;

class ProbarConfiguracionEnvio extends Command
{
    protected $signature = 'probar:configuracion-envio {configuracion_id}';
    protected $description = 'Probar la funcionalidad de configuración de envío';

    public function handle()
    {
        $configuracionId = $this->argument('configuracion_id');

        $this->info("Probando configuración ID: {$configuracionId}");

        $controller = new ConfiguracionEnvioController();
        $response = $controller->obtenerEmpleados($configuracionId);

        $data = $response->getData();

        $this->info("Success: " . ($data->success ? 'true' : 'false'));

        if ($data->success) {
            $this->info("Empresa: " . $data->configuracion->empresa_nombre);
            $this->info("Encuesta: " . $data->configuracion->encuesta_titulo);
            $this->info("Empleados encontrados: " . count($data->empleados));

            foreach ($data->empleados as $empleado) {
                $this->info("- {$empleado->nombre} ({$empleado->correo_electronico})");
            }
        } else {
            $this->error("Error: " . $data->message);
        }
    }
}
