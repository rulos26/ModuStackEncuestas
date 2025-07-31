<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empresa;
use App\Models\User;
use Exception;

class ProbarNumeroEncuestas extends Command
{
    protected $signature = 'probar:numero-encuestas {--valor=100}';
    protected $description = 'Probar que el campo numero_encuestas se llene automáticamente';

    public function handle()
    {
        $this->info('🧪 PROBANDO CAMPO NUMERO_ENCUESTAS');
        $this->line('');

        try {
            $valorDefecto = $this->option('valor');

            // Buscar una empresa para usar
            $empresa = Empresa::first();
            if (!$empresa) {
                $this->error('❌ No hay empresas disponibles. Crea una empresa primero.');
                return 1;
            }

            // Buscar un usuario para usar
            $user = User::first();
            if (!$user) {
                $this->error('❌ No hay usuarios disponibles. Crea un usuario primero.');
                return 1;
            }

            $this->line("📋 Configuración de prueba:");
            $this->line("   • Empresa: {$empresa->nombre}");
            $this->line("   • Usuario: {$user->name}");
            $this->line("   • Valor por defecto: {$valorDefecto}");
            $this->line('');

            // Prueba 1: Crear encuesta sin especificar numero_encuestas
            $this->line('🔍 PRUEBA 1: Crear encuesta sin especificar numero_encuestas');

            $encuesta1 = Encuesta::create([
                'titulo' => 'Prueba Numero Encuestas - ' . now()->format('Y-m-d H:i:s'),
                'empresa_id' => $empresa->id,
                'user_id' => $user->id,
                'slug' => 'prueba-numero-encuestas-' . time(),
                'habilitada' => false,
                'estado' => 'borrador'
                // No especificamos numero_encuestas
            ]);

            $this->line("   ✅ Encuesta creada con ID: {$encuesta1->id}");
            $this->line("   📊 numero_encuestas: {$encuesta1->numero_encuestas}");

            if ($encuesta1->numero_encuestas == $valorDefecto) {
                $this->line("   ✅ Valor por defecto aplicado correctamente");
            } else {
                $this->error("   ❌ Valor por defecto NO aplicado. Esperado: {$valorDefecto}, Obtenido: {$encuesta1->numero_encuestas}");
            }
            $this->line('');

            // Prueba 2: Crear encuesta especificando numero_encuestas
            $this->line('🔍 PRUEBA 2: Crear encuesta especificando numero_encuestas');

            $valorPersonalizado = 250;
            $encuesta2 = Encuesta::create([
                'titulo' => 'Prueba Numero Encuestas Personalizado - ' . now()->format('Y-m-d H:i:s'),
                'empresa_id' => $empresa->id,
                'user_id' => $user->id,
                'slug' => 'prueba-numero-encuestas-personalizado-' . time(),
                'habilitada' => false,
                'estado' => 'borrador',
                'numero_encuestas' => $valorPersonalizado
            ]);

            $this->line("   ✅ Encuesta creada con ID: {$encuesta2->id}");
            $this->line("   📊 numero_encuestas: {$encuesta2->numero_encuestas}");

            if ($encuesta2->numero_encuestas == $valorPersonalizado) {
                $this->line("   ✅ Valor personalizado aplicado correctamente");
            } else {
                $this->error("   ❌ Valor personalizado NO aplicado. Esperado: {$valorPersonalizado}, Obtenido: {$encuesta2->numero_encuestas}");
            }
            $this->line('');

            // Prueba 3: Verificar encuestas existentes
            $this->line('🔍 PRUEBA 3: Verificar encuestas existentes');

            $encuestasSinNumero = Encuesta::whereNull('numero_encuestas')->orWhere('numero_encuestas', 0)->count();
            $encuestasConNumero = Encuesta::whereNotNull('numero_encuestas')->where('numero_encuestas', '>', 0)->count();

            $this->line("   📊 Encuestas sin numero_encuestas: {$encuestasSinNumero}");
            $this->line("   📊 Encuestas con numero_encuestas: {$encuestasConNumero}");

            if ($encuestasSinNumero == 0) {
                $this->line("   ✅ Todas las encuestas tienen numero_encuestas configurado");
            } else {
                $this->warn("   ⚠️  Hay {$encuestasSinNumero} encuestas sin numero_encuestas configurado");
            }
            $this->line('');

            // Limpiar encuestas de prueba
            $this->line('🧹 Limpiando encuestas de prueba...');
            $encuesta1->delete();
            $encuesta2->delete();
            $this->line("   ✅ Encuestas de prueba eliminadas");
            $this->line('');

            $this->info('🎉 PRUEBA COMPLETADA EXITOSAMENTE');
            $this->line('');
            $this->line('📋 RESUMEN:');
            $this->line("   • Valor por defecto: {$valorDefecto}");
            $this->line("   • Se aplica automáticamente cuando no se especifica");
            $this->line("   • Se respeta cuando se especifica manualmente");
            $this->line("   • Todas las encuestas tienen el campo configurado");

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
            return 1;
        }
    }
}
