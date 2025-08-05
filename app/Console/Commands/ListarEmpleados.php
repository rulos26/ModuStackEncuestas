<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empleado;

class ListarEmpleados extends Command
{
    protected $signature = 'listar:empleados';
    protected $description = 'Listar todos los empleados existentes';

    public function handle()
    {
        $this->info('👥 EMPLEADOS EXISTENTES:');
        $this->line('');

        $empleados = Empleado::all();

        if ($empleados->isEmpty()) {
            $this->warn('No hay empleados en la base de datos');
            return 0;
        }

        foreach ($empleados as $empleado) {
            $this->line("   ID: {$empleado->id}");
            $this->line("   Nombre: {$empleado->nombre}");
            $this->line("   Email: {$empleado->correo_electronico}");
            $this->line("   Empresa ID: {$empleado->empresa_id}");
            $this->line("   ---");
        }

        $this->info("Total: {$empleados->count()} empleados");
        return 0;
    }
}
