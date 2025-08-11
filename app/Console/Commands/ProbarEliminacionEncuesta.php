<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ProbarEliminacionEncuesta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encuesta:probar-eliminacion {encuesta_id?} {--dry-run : Solo mostrar estadísticas sin eliminar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el módulo de eliminación de encuestas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar si STDIN está disponible (para entornos web)
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }

        $encuestaId = $this->argument('encuesta_id');
        $dryRun = $this->option('dry-run');

        $this->info('🔍 PROBANDO MÓDULO DE ELIMINACIÓN DE ENCUESTAS');
        $this->line('');

        try {
            if (!$encuestaId) {
                $this->mostrarEncuestasDisponibles();
                return;
            }

            $encuesta = Encuesta::with([
                'preguntas.respuestas', 'empresa', 'user', 'bloquesEnvio', 'tokensAcceso',
                'configuracionesEnvio', 'correosEnviados', 'respuestasUsuarios'
            ])->find($encuestaId);

            if (!$encuesta) {
                $this->error("❌ Encuesta con ID {$encuestaId} no encontrada.");
                return 1;
            }

            $this->info("✅ Encuesta encontrada: {$encuesta->titulo}");
            $this->line('');

            $this->mostrarInformacionEncuesta($encuesta);
            $this->mostrarEstadisticas($encuesta);
            $this->mostrarRelaciones($encuesta);

            if ($dryRun) {
                $this->warn('🔍 MODO DRY-RUN: No se eliminará la encuesta');
                $this->info('Para eliminar realmente, ejecuta sin --dry-run');
                return 0;
            }

            // En entorno web, saltar la confirmación interactiva
            if (!defined('STDIN') || !STDIN) {
                $this->warn('⚠️  Ejecutando en entorno web - saltando confirmación interactiva');
                $this->info('🗑️  Eliminando encuesta...');
            } else {
                if (!$this->confirm('¿Estás seguro de que quieres eliminar esta encuesta?')) {
                    $this->info('❌ Eliminación cancelada por el usuario.');
                    return 0;
                }
            }

            $this->crearBackup($encuesta);
            $this->info('🗑️ Eliminando encuesta...');
            $encuesta->delete();
            $this->info('✅ Encuesta eliminada exitosamente');
            $this->info('📊 Backup guardado en logs');
            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            Log::error('Error en comando probar eliminación', [
                'encuesta_id' => $encuestaId, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Mostrar encuestas disponibles
     */
    private function mostrarEncuestasDisponibles()
    {
        $this->info('📋 ENCUESTAS DISPONIBLES:');
        $this->line('');

        $encuestas = Encuesta::with(['empresa', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($encuestas->isEmpty()) {
            $this->warn('No hay encuestas disponibles.');
            return;
        }

        $headers = ['ID', 'Título', 'Empresa', 'Usuario', 'Estado', 'Preguntas', 'Creada'];
        $rows = [];

        foreach ($encuestas as $encuesta) {
            $rows[] = [
                $encuesta->id,
                Str::limit($encuesta->titulo, 30),
                $encuesta->empresa->nombre ?? 'Sin empresa',
                $encuesta->user->name ?? 'Sin usuario',
                $encuesta->estado,
                $encuesta->preguntas->count(),
                $encuesta->created_at->format('d/m/Y')
            ];
        }

        $this->table($headers, $rows);
        $this->line('');
        $this->info('Uso: php artisan encuesta:probar-eliminacion {ID} [--dry-run]');
    }

    /**
     * Mostrar información de la encuesta
     */
    private function mostrarInformacionEncuesta($encuesta)
    {
        $this->info('📊 INFORMACIÓN DE LA ENCUESTA:');
        $this->line('');

        $info = [
            ['Campo', 'Valor'],
            ['ID', $encuesta->id],
            ['Título', $encuesta->titulo],
            ['Estado', $encuesta->estado],
            ['Empresa', $encuesta->empresa->nombre ?? 'No asignada'],
            ['Usuario', $encuesta->user->name ?? 'No asignado'],
            ['Habilitada', $encuesta->habilitada ? 'Sí' : 'No'],
            ['Fecha inicio', $encuesta->fecha_inicio ? $encuesta->fecha_inicio->format('d/m/Y') : 'No definida'],
            ['Fecha fin', $encuesta->fecha_fin ? $encuesta->fecha_fin->format('d/m/Y') : 'No definida'],
            ['Creada', $encuesta->created_at->format('d/m/Y H:i:s')],
        ];

        $this->table(['Campo', 'Valor'], array_slice($info, 1));
        $this->line('');
    }

    /**
     * Mostrar estadísticas
     */
    private function mostrarEstadisticas($encuesta)
    {
        $this->info('📈 ESTADÍSTICAS:');
        $this->line('');

        $stats = [
            ['Tipo de dato', 'Cantidad'],
            ['Preguntas', $encuesta->preguntas->count()],
            ['Opciones de respuesta', $encuesta->preguntas->sum(function($p) { return $p->respuestas->count(); })],
            ['Respuestas de usuarios', $encuesta->respuestasUsuarios->count()],
            ['Bloques de envío', $encuesta->bloquesEnvio->count()],
            ['Tokens de acceso', $encuesta->tokensAcceso->count()],
            ['Configuraciones de envío', $encuesta->configuracionesEnvio->count()],
            ['Correos enviados', $encuesta->correosEnviados->count()],
        ];

        $this->table(['Tipo de dato', 'Cantidad'], array_slice($stats, 1));
        $this->line('');
    }

    /**
     * Mostrar relaciones que se eliminarán
     */
    private function mostrarRelaciones($encuesta)
    {
        $this->info('🔗 RELACIONES QUE SE ELIMINARÁN:');
        $this->line('');

        $relaciones = [
            ['Tabla', 'Registros', 'Acción'],
            ['preguntas', $encuesta->preguntas->count(), 'CASCADE DELETE'],
            ['respuestas', $encuesta->preguntas->sum(function($p) { return $p->respuestas->count(); }), 'CASCADE DELETE'],
            ['respuestas_usuario', $encuesta->respuestasUsuarios->count(), 'CASCADE DELETE'],
            ['bloques_envio', $encuesta->bloquesEnvio->count(), 'CASCADE DELETE'],
            ['tokens_encuesta', $encuesta->tokensAcceso->count(), 'CASCADE DELETE'],
            ['configuracion_envios', $encuesta->configuracionesEnvio->count(), 'CASCADE DELETE'],
            ['sent_mails', $encuesta->correosEnviados->count(), 'SET NULL'],
        ];

        $this->table(['Tabla', 'Registros', 'Acción'], array_slice($relaciones, 1));
        $this->line('');

        // Mostrar detalles de preguntas si existen
        if ($encuesta->preguntas->count() > 0) {
            $this->info('📝 PREGUNTAS QUE SE ELIMINARÁN:');
            $this->line('');

            $preguntas = [];
            foreach ($encuesta->preguntas as $pregunta) {
                $preguntas[] = [
                    $pregunta->id,
                    Str::limit($pregunta->texto, 50),
                    $pregunta->tipo,
                    $pregunta->respuestas->count()
                ];
            }

            $this->table(['ID', 'Pregunta', 'Tipo', 'Respuestas'], $preguntas);
            $this->line('');
        }
    }

    /**
     * Crear backup de la encuesta
     */
    private function crearBackup($encuesta)
    {
        $this->info('💾 Creando backup...');

        $backup = [
            'encuesta' => $encuesta->toArray(),
            'fecha_backup' => now()->toDateTimeString(),
            'usuario_backup' => 'comando_artisan',
            'accion' => 'eliminacion_via_comando'
        ];

        Log::info('Backup de encuesta antes de eliminar (comando)', [
            'encuesta_id' => $encuesta->id,
            'backup' => $backup
        ]);

        $this->info('✅ Backup creado exitosamente');
    }
}
