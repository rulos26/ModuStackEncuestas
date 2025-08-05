<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CrearEmpresaPrueba extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crear:empresa-prueba {--nombre= : Nombre de la empresa (opcional)} {--force : Forzar creación incluso si ya existen empresas}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea una empresa de prueba para desarrollo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏢 Creando empresa de prueba...');

        try {
            // Verificar conexión
            $this->info('📡 Probando conexión a la base de datos...');
            DB::connection()->getPdo();
            $this->info('✅ Conexión exitosa');

            $nombre = $this->option('nombre') ?: 'Empresa de Prueba';
            $force = $this->option('force');

            // Verificar si ya existen empresas
            $empresasExistentes = Empresa::count();

            if ($empresasExistentes > 0 && !$force) {
                $this->warn("⚠️ Ya existen {$empresasExistentes} empresas en la base de datos");
                $this->info("💡 Usa --force para crear una empresa adicional");
                return 0;
            }

            // Crear empresa
            $empresa = Empresa::create([
                'nombre' => $nombre,
                'descripcion' => 'Empresa creada automáticamente para pruebas',
                'direccion' => 'Dirección de prueba',
                'telefono' => '123-456-7890',
                'email' => 'prueba@empresa.com',
                'website' => 'https://empresa-prueba.com',
                'activa' => true,
            ]);

            $this->info("✅ Empresa creada exitosamente:");
            $this->line("   ID: {$empresa->id}");
            $this->line("   Nombre: {$empresa->nombre}");
            $this->line("   Creada: {$empresa->created_at}");

            $this->info("💡 Ahora puedes crear encuestas usando empresa_id = {$empresa->id}");

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
