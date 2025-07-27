<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Empresa;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;

class CreateTestCompany extends Command
{
    protected $signature = 'company:create-test';
    protected $description = 'Crea una empresa de prueba para desarrollo';

    public function handle()
    {
        $this->info('=== CREANDO EMPRESA DE PRUEBA ===');

        try {
            // Verificar si ya existe una empresa
            $existingCompany = Empresa::first();
            if ($existingCompany) {
                $this->info("✅ Ya existe una empresa: {$existingCompany->nombre_legal}");
                return 0;
            }

            // Crear país de prueba si no existe
            $pais = Pais::firstOrCreate(
                ['name' => 'Colombia'],
                ['alfa2' => 'CO', 'created_at' => now(), 'updated_at' => now()]
            );

            // Crear departamento de prueba si no existe
            $departamento = Departamento::firstOrCreate(
                ['nombre' => 'CUNDINAMARCA', 'pais_id' => $pais->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Crear municipio de prueba si no existe
            $municipio = Municipio::firstOrCreate(
                ['nombre' => 'BOGOTÁ', 'departamento_id' => $departamento->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Crear empresa de prueba
            $empresa = new Empresa();
            $empresa->nombre_legal = 'Empresa de Prueba S.A.S';
            $empresa->nit = '900123456-7';
            $empresa->representante_legal = 'Juan Pérez';
            $empresa->telefono = '3001234567';
            $empresa->email = 'contacto@empresaprueba.com';
            $empresa->direccion = 'Calle 123 #45-67, Bogotá';
            $empresa->mision = 'Proporcionar servicios de calidad para satisfacer las necesidades de nuestros clientes';
            $empresa->vision = 'Ser líder en el mercado de servicios empresariales';
            $empresa->descripcion = 'Empresa dedicada a la prestación de servicios empresariales y consultoría';
            $empresa->fecha_creacion = now();
            $empresa->pais_id = $pais->id;
            $empresa->departamento_id = $departamento->id;
            $empresa->municipio_id = $municipio->id;
            $empresa->save();

            $this->info("✅ Empresa creada exitosamente:");
            $this->info("   Nombre: {$empresa->nombre_legal}");
            $this->info("   NIT: {$empresa->nit}");
            $this->info("   Email: {$empresa->email}");
            $this->info("   País: {$pais->name}");
            $this->info("   Departamento: {$departamento->nombre}");
            $this->info("   Municipio: {$municipio->nombre}");
            $this->info("   ID: {$empresa->id}");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error creando empresa: " . $e->getMessage());
            return 1;
        }
    }
}
