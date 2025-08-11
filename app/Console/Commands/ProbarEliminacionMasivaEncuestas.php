<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encuesta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class ProbarEliminacionMasivaEncuestas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'encuesta:probar-eliminacion-masiva {--encuesta_ids= : IDs de encuestas separados por coma} {--estado= : Filtrar por estado (borrador, publicada, etc.)} {--dry-run : Solo mostrar estadísticas sin eliminar} {--limit=10 : Límite de encuestas a procesar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el módulo de eliminación masiva de encuestas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $encuestaIds = $this->option('encuesta_ids');
        $estado = $this->option('estado');
        $dryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('🔍 PROBANDO MÓDULO DE ELIMINACIÓN MASIVA DE ENCUESTAS');
        $this->line('');

        try {
            // Construir query
            $query = Encuesta::with([
                'preguntas.respuestas',
                'empresa',
                'user',
                'bloquesEnvio',
                'tokensAcceso',
                'configuracionesEnvio',
                'correosEnviados',
                'respuestasUsuarios'
            ]);

            // Filtrar por IDs específicos
            if ($encuestaIds) {
                $ids = array_map('trim', explode(',', $encuestaIds));
                $query->whereIn('id', $ids);
                $this->info("📋 Filtrando por IDs: " . implode(', ', $ids));
            }

            // Filtrar por estado
            if ($estado) {
                $query->where('estado', $estado);
                $this->info("📊 Filtrando por estado: {$estado}");
            }

            // Aplicar límite
            $query->limit($limit);

            // Obtener encuestas
            $encuestas = $query->orderBy('created_at', 'desc')->get();

            if ($encuestas->isEmpty()) {
                $this->warn('❌ No se encontraron encuestas que coincidan con los criterios.');
                return 1;
            }

            $this->info("✅ Se encontraron {$encuestas->count()} encuestas para procesar");
            $this->line('');

            // Mostrar información de las encuestas
            $this->mostrarInformacionEncuestas($encuestas);

            // Calcular y mostrar estadísticas
            $estadisticas = $this->calcularEstadisticasMasivas($encuestas);
            $this->mostrarEstadisticas($estadisticas);

            // Mostrar relaciones que se eliminarán
            $this->mostrarRelacionesMasivas($encuestas);

            if ($dryRun) {
                $this->warn('🔍 MODO DRY-RUN: No se eliminarán las encuestas');
                $this->info('Para eliminar realmente, ejecuta sin --dry-run');
                return 0;
            }

            // Confirmar eliminación
            if (!$this->confirm("¿Estás seguro de que quieres eliminar {$encuestas->count()} encuestas?")) {
                $this->info('❌ Eliminación cancelada por el usuario.');
                return 0;
            }

            // Crear backup antes de eliminar
            $this->crearBackupMasivo($encuestas);

            // Eliminar encuestas
            $this->info('🗑️ Eliminando encuestas...');
            $eliminadas = 0;
            $errores = [];

            $progressBar = $this->output->createProgressBar($encuestas->count());
            $progressBar->start();

            foreach ($encuestas as $encuesta) {
                try {
                    $encuesta->delete();
                    $eliminadas++;

                    Log::info('Encuesta eliminada en proceso masivo (comando)', [
                        'encuesta_id' => $encuesta->id,
                        'titulo' => $encuesta->titulo
                    ]);
                } catch (Exception $e) {
                    $errores[] = "Error eliminando encuesta ID {$encuesta->id}: " . $e->getMessage();

                    Log::error('Error eliminando encuesta en proceso masivo (comando)', [
                        'encuesta_id' => $encuesta->id,
                        'error' => $e->getMessage()
                    ]);
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->line('');

            $this->info("✅ Se eliminaron {$eliminadas} de {$encuestas->count()} encuestas");

            if (!empty($errores)) {
                $this->error("❌ Errores encontrados:");
                foreach ($errores as $error) {
                    $this->error("  - {$error}");
                }
            }

            $this->info('📊 Backup guardado en logs');

            return 0;

        } catch (Exception $e) {
            $this->error("❌ Error: {$e->getMessage()}");
            Log::error('Error en comando probar eliminación masiva', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Mostrar información de las encuestas
     */
    private function mostrarInformacionEncuestas($encuestas)
    {
        $this->info('📋 ENCUESTAS A PROCESAR:');
        $this->line('');

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
    }

    /**
     * Calcular estadísticas masivas
     */
    private function calcularEstadisticasMasivas($encuestas)
    {
        $totales = [
            'encuestas_count' => $encuestas->count(),
            'preguntas_count' => 0,
            'respuestas_count' => 0,
            'respuestas_usuarios_count' => 0,
            'bloques_envio_count' => 0,
            'tokens_acceso_count' => 0,
            'configuraciones_envio_count' => 0,
            'correos_enviados_count' => 0,
        ];

        foreach ($encuestas as $encuesta) {
            $totales['preguntas_count'] += $encuesta->preguntas->count();
            $totales['respuestas_count'] += $encuesta->preguntas->sum(function($p) { return $p->respuestas->count(); });
            $totales['respuestas_usuarios_count'] += $encuesta->respuestasUsuarios->count();
            $totales['bloques_envio_count'] += $encuesta->bloquesEnvio->count();
            $totales['tokens_acceso_count'] += $encuesta->tokensAcceso->count();
            $totales['configuraciones_envio_count'] += $encuesta->configuracionesEnvio->count();
            $totales['correos_enviados_count'] += $encuesta->correosEnviados->count();
        }

        return $totales;
    }

    /**
     * Mostrar estadísticas
     */
    private function mostrarEstadisticas($estadisticas)
    {
        $this->info('📈 ESTADÍSTICAS TOTALES:');
        $this->line('');

        $stats = [
            ['Tipo de dato', 'Cantidad'],
            ['Encuestas', $estadisticas['encuestas_count']],
            ['Preguntas', $estadisticas['preguntas_count']],
            ['Opciones de respuesta', $estadisticas['respuestas_count']],
            ['Respuestas de usuarios', $estadisticas['respuestas_usuarios_count']],
            ['Bloques de envío', $estadisticas['bloques_envio_count']],
            ['Tokens de acceso', $estadisticas['tokens_acceso_count']],
            ['Configuraciones de envío', $estadisticas['configuraciones_envio_count']],
            ['Correos enviados', $estadisticas['correos_enviados_count']],
        ];

        $this->table(['Tipo de dato', 'Cantidad'], array_slice($stats, 1));
        $this->line('');
    }

    /**
     * Mostrar relaciones que se eliminarán
     */
    private function mostrarRelacionesMasivas($encuestas)
    {
        $this->info('🔗 RELACIONES QUE SE ELIMINARÁN:');
        $this->line('');

        $relaciones = [
            ['Tabla', 'Registros', 'Acción'],
            ['encuestas', $encuestas->count(), 'ELIMINAR'],
            ['preguntas', $encuestas->sum(function($e) { return $e->preguntas->count(); }), 'CASCADE DELETE'],
            ['respuestas', $encuestas->sum(function($e) { return $e->preguntas->sum(function($p) { return $p->respuestas->count(); }); }), 'CASCADE DELETE'],
            ['respuestas_usuario', $encuestas->sum(function($e) { return $e->respuestasUsuarios->count(); }), 'CASCADE DELETE'],
            ['bloques_envio', $encuestas->sum(function($e) { return $e->bloquesEnvio->count(); }), 'CASCADE DELETE'],
            ['tokens_encuesta', $encuestas->sum(function($e) { return $e->tokensAcceso->count(); }), 'CASCADE DELETE'],
            ['configuracion_envios', $encuestas->sum(function($e) { return $e->configuracionesEnvio->count(); }), 'CASCADE DELETE'],
            ['sent_mails', $encuestas->sum(function($e) { return $e->correosEnviados->count(); }), 'SET NULL'],
        ];

        $this->table(['Tabla', 'Registros', 'Acción'], array_slice($relaciones, 1));
        $this->line('');
    }

    /**
     * Crear backup masivo
     */
    private function crearBackupMasivo($encuestas)
    {
        $this->info('💾 Creando backup masivo...');

        $backup = [
            'encuestas' => $encuestas->toArray(),
            'fecha_backup' => now()->toDateTimeString(),
            'usuario_backup' => 'comando_artisan',
            'accion' => 'eliminacion_masiva_via_comando',
            'total_encuestas' => $encuestas->count()
        ];

        Log::info('Backup masivo de encuestas antes de eliminar (comando)', [
            'total_encuestas' => $encuestas->count(),
            'encuesta_ids' => $encuestas->pluck('id')->toArray(),
            'backup' => $backup
        ]);

        $this->info('✅ Backup masivo creado exitosamente');
    }
}
