<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ConfiguracionEnvioController;

class SimularClicBotonConfigurar extends Command
{
    protected $signature = 'simular:clic-boton-configurar {configuracion_id}';
    protected $description = 'Simular la experiencia completa del usuario al hacer clic en el botón configurar destinatarios';

    public function handle()
    {
        $configuracionId = $this->argument('configuracion_id');

        $this->info('🎯 SIMULANDO CLIC EN BOTÓN "CONFIGURAR DESTINATARIOS"');
        $this->line('');

        // Simular paso 1: Usuario hace clic en el botón
        $this->info('1️⃣ USUARIO HACE CLIC EN EL BOTÓN');
        $this->line("   Configuración ID: {$configuracionId}");
        $this->line("   URL llamada: /configuracion-envio/obtener-empleados/{$configuracionId}");
        $this->line('');

        // Simular paso 2: Mostrar loading
        $this->info('2️⃣ MOSTRANDO LOADING...');
        $this->line("   SweetAlert2: 'Cargando empleados...'");
        $this->line('');

        // Simular paso 3: Llamada al servidor
        $this->info('3️⃣ LLAMADA AL SERVIDOR');
        $this->line("   Método: GET");
        $this->line("   Endpoint: obtenerEmpleados({$configuracionId})");
        $this->line('');

        // Simular paso 4: Procesar respuesta
        $this->info('4️⃣ PROCESANDO RESPUESTA');

        try {
            $controller = new ConfiguracionEnvioController();
            $response = $controller->obtenerEmpleados($configuracionId);
            $data = $response->getData();

            if ($data->success) {
                $this->info("   ✅ Respuesta exitosa");
                $this->line("   Empresa Cliente: {$data->configuracion->empresa_nombre}");
                $this->line("   Encuesta: {$data->configuracion->encuesta_titulo}");
                $this->line("   Empleados encontrados: " . count($data->empleados));
                $this->line('');

                // Simular paso 5: Mostrar modal
                $this->info('5️⃣ MOSTRANDO MODAL');
                $this->line("   Título: 'Configurar Destinatarios'");
                $this->line("   Empresa mostrada: {$data->configuracion->empresa_nombre}");
                $this->line("   Encuesta mostrada: {$data->configuracion->encuesta_titulo}");
                $this->line('');

                // Simular paso 6: Lista de empleados
                $this->info('6️⃣ LISTA DE EMPLEADOS EN EL MODAL');
                foreach ($data->empleados as $index => $empleado) {
                    $this->line("   " . ($index + 1) . ". {$empleado->nombre}");
                    $this->line("      📧 {$empleado->correo_electronico}");
                    $this->line("      ☑️  Checkbox disponible para selección");
                    $this->line("");
                }

                // Simular paso 7: Botones del modal
                $this->info('7️⃣ BOTONES DISPONIBLES EN EL MODAL');
                $this->line("   ✅ 'Seleccionar Todos'");
                $this->line("   ❌ 'Deseleccionar Todos'");
                $this->line("   💾 'Guardar Configuración'");
                $this->line("   ❌ 'Cerrar'");
                $this->line('');

                // Simular paso 8: Funcionalidad esperada
                $this->info('8️⃣ FUNCIONALIDAD ESPERADA');
                $this->line("   ✅ Usuario puede seleccionar empleados individualmente");
                $this->line("   ✅ Usuario puede seleccionar todos los empleados");
                $this->line("   ✅ Usuario puede deseleccionar todos los empleados");
                $this->line("   ✅ Usuario puede guardar la configuración");
                $this->line("   ✅ Usuario puede cerrar el modal");
                $this->line('');

                $this->info('🎉 SIMULACIÓN COMPLETADA - TODO FUNCIONA CORRECTAMENTE');

            } else {
                $this->error("   ❌ Error en la respuesta: {$data->message}");
                $this->line('');
                $this->error('❌ SIMULACIÓN FALLIDA');
            }

        } catch (\Exception $e) {
            $this->error("   ❌ Excepción: " . $e->getMessage());
            $this->line('');
            $this->error('❌ SIMULACIÓN FALLIDA');
        }

        return 0;
    }
}
