<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use App\Models\Empleado;
use App\Models\SentMail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TestDashboardButtons extends Command
{
    protected $signature = 'test:dashboard-buttons {encuesta_id?}';
    protected $description = 'Probar todos los botones del dashboard de seguimiento';

    public function handle()
    {
        $encuestaId = $this->argument('encuesta_id');

        if (!$encuestaId) {
            $encuesta = Encuesta::first();
            if (!$encuesta) {
                $this->error('No hay encuestas disponibles para probar');
                return 1;
            }
            $encuestaId = $encuesta->id;
        }

        $encuesta = Encuesta::find($encuestaId);
        if (!$encuesta) {
            $this->error("Encuesta con ID {$encuestaId} no encontrada");
            return 1;
        }

        $this->info("=== PRUEBA DE BOTONES DEL DASHBOARD ===");
        $this->info("Encuesta: {$encuesta->titulo} (ID: {$encuesta->id})");
        $this->line('');

        // Probar rutas
        $this->testRoutes($encuesta);

        // Probar datos
        $this->testData($encuesta);

        // Probar funcionalidades
        $this->testFunctionalities($encuesta);

        $this->info('✅ Pruebas completadas');
        return 0;
    }

    private function testRoutes($encuesta)
    {
        $this->info('🔗 PROBANDO RUTAS:');

        $routes = [
            'encuestas.seguimiento.dashboard' => 'Dashboard principal',
            'encuestas.seguimiento.actualizar' => 'Actualizar datos',
            'encuestas.seguimiento.pausar' => 'Pausar envío',
            'encuestas.seguimiento.reanudar' => 'Reanudar envío',
            'encuestas.seguimiento.cancelar' => 'Cancelar envío',
            'encuestas.seguimiento.enviar-masivo' => 'Enviar correos masivos',
            'encuestas.seguimiento.enviar-seleccionados' => 'Enviar correos seleccionados',
            'encuestas.seguimiento.enviar-individual' => 'Enviar correo individual',
            'encuestas.seguimiento.detalles-correo' => 'Detalles de correo',
            'encuestas.seguimiento.exportar-lista' => 'Exportar lista',
            'encuestas.seguimiento.actualizar-correos-pendientes' => 'Actualizar correos pendientes'
        ];

        foreach ($routes as $routeName => $description) {
            try {
                $url = route($routeName, $encuesta->id);
                $this->line("  ✅ {$description}: {$url}");
            } catch (\Exception $e) {
                $this->error("  ❌ {$description}: Error - {$e->getMessage()}");
            }
        }

        $this->line('');
    }

    private function testData($encuesta)
    {
        $this->info('📊 PROBANDO DATOS:');

        // Estadísticas
        $estadisticas = $this->obtenerEstadisticasEnvio($encuesta);
        $this->line("  📈 Estadísticas obtenidas:");
        $this->line("     - Total encuestas: {$estadisticas['total_encuestas']}");
        $this->line("     - Enviadas: {$estadisticas['encuestas_enviadas']}");
        $this->line("     - Pendientes: {$estadisticas['encuestas_pendientes']}");
        $this->line("     - Progreso: {$estadisticas['progreso_porcentaje']}%");

        // Correos pendientes
        $correosPendientes = $this->obtenerCorreosPendientes($encuesta);
        $this->line("  📧 Correos pendientes: " . count($correosPendientes));

        // Bloques de envío
        $bloques = $encuesta->obtenerBloquesEnvio();
        $this->line("  📦 Bloques de envío: " . count($bloques));

        // Correos enviados
        $correosEnviados = SentMail::where('encuesta_id', $encuesta->id)->count();
        $this->line("  ✅ Correos enviados: {$correosEnviados}");

        $this->line('');
    }

    private function testFunctionalities($encuesta)
    {
        $this->info('🔧 PROBANDO FUNCIONALIDADES:');

        // Probar actualización de estado
        $resultado = $encuesta->actualizarEstadoSegunProgreso();
        $this->line("  🔄 Actualización de estado: " . ($resultado['success'] ? '✅' : '❌'));
        if (!$resultado['success']) {
            $this->line("     Error: {$resultado['error']}");
        }

        // Probar generación de token
        $token = $encuesta->generarTokenAcceso();
        $this->line("  🔑 Generación de token: " . ($token ? '✅' : '❌'));

        // Probar enlace público
        $enlace = route('encuestas.publica', $encuesta->slug);
        $this->line("  🔗 Enlace público: {$enlace}");

        $this->line('');
    }

    private function obtenerEstadisticasEnvio($encuesta)
    {
        $totalEncuestas = Empleado::count();
        $encuestasEnviadas = SentMail::where('encuesta_id', $encuesta->id)->count();
        $encuestasPendientes = $totalEncuestas - $encuestasEnviadas;
        $progresoPorcentaje = $totalEncuestas > 0 ? round(($encuestasEnviadas / $totalEncuestas) * 100, 2) : 0;

        return [
            'total_encuestas' => $totalEncuestas,
            'encuestas_enviadas' => $encuestasEnviadas,
            'encuestas_pendientes' => $encuestasPendientes,
            'encuestas_respondidas' => 0, // Implementar lógica de respuestas
            'progreso_porcentaje' => $progresoPorcentaje,
            'estado_encuesta' => $encuesta->estado,
            'bloques_enviados' => 0,
            'bloques_pendientes' => 0,
            'bloques_en_proceso' => 0,
            'bloques_error' => 0,
            'bloques_cancelados' => 0,
            'correos_enviados' => $encuestasEnviadas
        ];
    }

    private function obtenerCorreosPendientes($encuesta)
    {
        $correosPendientes = collect();

        // Obtener empleados que no han recibido correo
        $empleadosSinCorreo = Empleado::whereNotExists(function ($query) use ($encuesta) {
            $query->select(DB::raw(1))
                  ->from('sent_mails')
                  ->whereColumn('sent_mails.to', 'empleados.correo_electronico')
                  ->where('sent_mails.encuesta_id', $encuesta->id);
        })->get();

        foreach ($empleadosSinCorreo as $empleado) {
            $correosPendientes->push([
                'id' => $empleado->id,
                'nombre' => $empleado->nombre,
                'email' => $empleado->correo_electronico,
                'tipo' => 'empleado',
                'cargo' => $empleado->empresa ? $empleado->empresa->nombre : null
            ]);
        }

        return $correosPendientes;
    }
}
