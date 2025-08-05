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
            // Buscar la encuesta
            $encuesta = Encuesta::find($id);

            if (!$encuesta) {
                $this->error("❌ No se encontró encuesta con ID: {$id}");
                return 1;
            }

            // Generar el enlace
            $enlace = route('encuestas.publica', ['slug' => $encuesta->slug]);

            $this->info("✅ Enlace generado exitosamente:");
            $this->line("   📋 Encuesta: {$encuesta->titulo}");
            $this->line("   🔗 Enlace: {$enlace}");
            $this->line("   📊 Estado: {$encuesta->estado}");
            $this->line("   🏢 Empresa: " . ($encuesta->empresa ? $encuesta->empresa->nombre : 'Sin empresa'));

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}
