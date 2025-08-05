<?php

namespace App\Console\Commands;

use App\Models\Encuesta;
use Illuminate\Console\Command;

class GenerarEnlaceEncuesta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encuesta:generar-enlace {id : ID de la encuesta}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera el enlace público de una encuesta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');

        $this->info("🔗 Generando enlace para encuesta ID: {$id}");

        try {
            // Verificar conexión a BD
            $this->info("📡 Verificando conexión a la base de datos...");
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $this->info("✅ Conexión exitosa");

            // Buscar la encuesta
            $this->info("🔍 Buscando encuesta...");
            $encuesta = Encuesta::find($id);

            if (!$encuesta) {
                $this->error("❌ No se encontró encuesta con ID: {$id}");
                $this->info("💡 Verifica que la encuesta exista en la base de datos");
                return 1;
            }

            $this->info("✅ Encuesta encontrada:");
            $this->line("   📋 Título: {$encuesta->titulo}");
            $this->line("   📊 Estado: {$encuesta->estado}");
            $this->line("   🏢 Empresa: " . ($encuesta->empresa ? $encuesta->empresa->nombre : 'Sin empresa'));

            // Generar el enlace de forma manual
            $baseUrl = config('app.url');
            $enlace = $baseUrl . '/encuesta/' . $encuesta->slug;

            $this->info("🔗 Enlace generado:");
            $this->line("   🌐 URL Base: {$baseUrl}");
            $this->line("   🔗 Enlace Completo: {$enlace}");
            $this->line("   📝 Slug: {$encuesta->slug}");

            // Verificar si la ruta existe
            try {
                $routeEnlace = route('encuestas.publica', ['slug' => $encuesta->slug]);
                $this->info("✅ Ruta Laravel generada: {$routeEnlace}");

                // Mostrar ambos enlaces
                $this->info("🔗 ENLACES DISPONIBLES:");
                $this->line("   1️⃣ Enlace Laravel: {$routeEnlace}");
                $this->line("   2️⃣ Enlace Manual: {$enlace}");
                $this->line("");
                $this->info("💡 Usa cualquiera de los dos enlaces arriba");

            } catch (\Exception $e) {
                $this->warn("⚠️ No se pudo generar ruta Laravel: " . $e->getMessage());
                $this->info("💡 Usando enlace manual: {$enlace}");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("📋 Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
