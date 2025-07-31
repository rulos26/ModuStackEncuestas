<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

class ProbarContadoresEncuesta extends Command
{
    protected $signature = 'probar:contadores-encuesta {encuesta_id?}';
    protected $description = 'Probar la actualización de contadores de encuesta';

    public function handle()
    {
        $this->info('🧪 PROBANDO ACTUALIZACIÓN DE CONTADORES DE ENCUESTA');
        $this->line('');

        try {
            $encuestaId = $this->argument('encuesta_id');

            if ($encuestaId) {
                $encuesta = Encuesta::find($encuestaId);
                if (!$encuesta) {
                    $this->error("❌ Encuesta con ID {$encuestaId} no encontrada");
                    return 1;
                }
                $this->probarEncuestaEspecifica($encuesta);
            } else {
                $this->probarTodasLasEncuestas();
            }

            return 0;

        } catch (Exception $e) {
            $this->error('❌ Error en la prueba: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Probar una encuesta específica
     */
    private function probarEncuestaEspecifica($encuesta)
    {
        $this->line("📋 PROBANDO ENCUESTA ESPECÍFICA:");
        $this->line("   • ID: {$encuesta->id}");
        $this->line("   • Título: {$encuesta->titulo}");
        $this->line("   • Estado: {$encuesta->estado}");
        $this->line("   • Habilitada: " . ($encuesta->habilitada ? 'Sí' : 'No'));
        $this->line('');

        $this->mostrarContadoresActuales($encuesta);
        $this->line('');

        // Simular actualización de contadores
        $this->line('🔄 SIMULANDO ACTUALIZACIÓN DE CONTADORES...');

        $contadoresAntes = [
            'encuestas_respondidas' => $encuesta->encuestas_respondidas,
            'encuestas_pendientes' => $encuesta->encuestas_pendientes
        ];

        // Simular incremento
        $encuesta->increment('encuestas_respondidas');
        $encuesta->decrement('encuestas_pendientes');

        // Asegurar que no sea negativo
        if ($encuesta->encuestas_pendientes < 0) {
            $encuesta->update(['encuestas_pendientes' => 0]);
        }

        $encuesta->refresh();

        $this->line('✅ Contadores actualizados:');
        $this->line("   • encuestas_respondidas: {$contadoresAntes['encuestas_respondidas']} → {$encuesta->encuestas_respondidas}");
        $this->line("   • encuestas_pendientes: {$contadoresAntes['encuestas_pendientes']} → {$encuesta->encuestas_pendientes}");
        $this->line('');

        // Verificar lógica
        $this->verificarLogicaContadores($encuesta);
    }

    /**
     * Probar todas las encuestas
     */
    private function probarTodasLasEncuestas()
    {
        $this->line('📋 PROBANDO TODAS LAS ENCUESTAS');
        $this->line('');

        $encuestas = Encuesta::all();

        if ($encuestas->isEmpty()) {
            $this->warn('⚠️  No hay encuestas en la base de datos');
            return;
        }

        $this->line("📊 Total de encuestas: {$encuestas->count()}");
        $this->line('');

        foreach ($encuestas as $encuesta) {
            $this->line("🔍 Encuesta ID {$encuesta->id}: {$encuesta->titulo}");
            $this->mostrarContadoresActuales($encuesta);
            $this->verificarLogicaContadores($encuesta);
            $this->line('');
        }

        // Resumen general
        $this->mostrarResumenGeneral($encuestas);
    }

    /**
     * Mostrar contadores actuales de una encuesta
     */
    private function mostrarContadoresActuales($encuesta)
    {
        $this->line("   📊 Contadores actuales:");
        $this->line("      • numero_encuestas: {$encuesta->numero_encuestas}");
        $this->line("      • encuestas_respondidas: {$encuesta->encuestas_respondidas}");
        $this->line("      • encuestas_pendientes: {$encuesta->encuestas_pendientes}");
        $this->line("      • encuestas_enviadas: {$encuesta->encuestas_enviadas}");
    }

    /**
     * Verificar lógica de contadores
     */
    private function verificarLogicaContadores($encuesta)
    {
        $this->line("   🔍 Verificación de lógica:");

        // Verificar que encuestas_pendientes no sea negativo
        if ($encuesta->encuestas_pendientes < 0) {
            $this->error("      ❌ encuestas_pendientes es negativo: {$encuesta->encuestas_pendientes}");
        } else {
            $this->line("      ✅ encuestas_pendientes es válido: {$encuesta->encuestas_pendientes}");
        }

        // Verificar que encuestas_respondidas no sea mayor que numero_encuestas
        if ($encuesta->encuestas_respondidas > $encuesta->numero_encuestas) {
            $this->warn("      ⚠️  encuestas_respondidas mayor que numero_encuestas: {$encuesta->encuestas_respondidas} > {$encuesta->numero_encuestas}");
        } else {
            $this->line("      ✅ encuestas_respondidas es válido: {$encuesta->encuestas_respondidas}");
        }

        // Verificar suma lógica
        $suma = $encuesta->encuestas_respondidas + $encuesta->encuestas_pendientes;
        if ($suma > $encuesta->numero_encuestas) {
            $this->warn("      ⚠️  Suma excede numero_encuestas: {$suma} > {$encuesta->numero_encuestas}");
        } else {
            $this->line("      ✅ Suma lógica correcta: {$suma} ≤ {$encuesta->numero_encuestas}");
        }
    }

    /**
     * Mostrar resumen general
     */
    private function mostrarResumenGeneral($encuestas)
    {
        $this->line('📋 RESUMEN GENERAL:');

        $totalNumero = $encuestas->sum('numero_encuestas');
        $totalRespondidas = $encuestas->sum('encuestas_respondidas');
        $totalPendientes = $encuestas->sum('encuestas_pendientes');
        $totalEnviadas = $encuestas->sum('encuestas_enviadas');

        $this->line("   • Total numero_encuestas: {$totalNumero}");
        $this->line("   • Total encuestas_respondidas: {$totalRespondidas}");
        $this->line("   • Total encuestas_pendientes: {$totalPendientes}");
        $this->line("   • Total encuestas_enviadas: {$totalEnviadas}");

        $porcentajeRespondidas = $totalNumero > 0 ? round(($totalRespondidas / $totalNumero) * 100, 2) : 0;
        $this->line("   • Porcentaje respondidas: {$porcentajeRespondidas}%");

        $this->line('');
        $this->info('🎉 Verificación de contadores completada');
    }
}
