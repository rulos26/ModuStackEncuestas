<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerificarEmpresas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verificar:empresas {--empresa-id= : ID específico de empresa a verificar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica las empresas disponibles en la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏢 Verificando empresas en la base de datos...');

        try {
            // Verificar conexión
            $this->info('📡 Probando conexión a la base de datos...');
            DB::connection()->getPdo();
            $this->info('✅ Conexión exitosa');

            // Obtener todas las empresas
            $empresaId = $this->option('empresa-id');

            if ($empresaId) {
                $this->info("🔍 Buscando empresa con ID: {$empresaId}");
                $empresa = Empresa::find($empresaId);

                if ($empresa) {
                    $this->info("✅ Empresa encontrada:");
                    $this->line("   ID: {$empresa->id}");
                    $this->line("   Nombre: {$empresa->nombre}");
                    $this->line("   Creada: {$empresa->created_at}");
                    $this->line("   Actualizada: {$empresa->updated_at}");
                } else {
                    $this->error("❌ No se encontró empresa con ID: {$empresaId}");
                }
            } else {
                $empresas = Empresa::all(['id', 'nombre', 'created_at']);

                $this->info("📊 Total de empresas: {$empresas->count()}");

                if ($empresas->count() > 0) {
                    $this->info("📋 Lista de empresas:");
                    foreach ($empresas as $empresa) {
                        $this->line("   ID {$empresa->id}: {$empresa->nombre} (Creada: {$empresa->created_at->format('Y-m-d H:i:s')})");
                    }
                } else {
                    $this->warn("⚠️ No hay empresas registradas en la base de datos");
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
