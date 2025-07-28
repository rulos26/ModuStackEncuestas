<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Encuesta extends Model
{
    protected $table = 'encuestas';

    protected $fillable = [
        'titulo',
        'empresa_id',
        'numero_encuestas',
        'encuestas_enviadas',
        'encuestas_respondidas',
        'encuestas_pendientes',
        'fecha_inicio',
        'fecha_fin',
        'enviar_por_correo',
        'plantilla_correo',
        'asunto_correo',
        'envio_masivo_activado',
        'estado',
        'user_id',
        'slug',
        'token_acceso',
        'token_expiracion',
        'habilitada',
        'validacion_completada',
        'errores_validacion'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'token_expiracion' => 'datetime',
        'enviar_por_correo' => 'boolean',
        'habilitada' => 'boolean',
        'envio_masivo_activado' => 'boolean',
        'validacion_completada' => 'boolean',
        'encuestas_enviadas' => 'integer',
        'encuestas_respondidas' => 'integer',
        'encuestas_pendientes' => 'integer',
        'errores_validacion' => 'array'
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(Pregunta::class)->orderBy('orden');
    }

    /**
     * Obtiene las preguntas con sus respuestas ordenadas
     */
    public function preguntasConRespuestas(): HasMany
    {
        return $this->hasMany(Pregunta::class)
            ->with(['respuestas' => function($query) {
                $query->orderBy('orden');
            }])
            ->orderBy('orden');
    }

    /**
     * Obtiene los bloques de envío de la encuesta
     */
    public function bloquesEnvio(): HasMany
    {
        return $this->hasMany(BloqueEnvio::class)->orderBy('numero_bloque');
    }

    /**
     * Obtiene los tokens de acceso de la encuesta
     */
    public function tokensAcceso(): HasMany
    {
        return $this->hasMany(TokenEncuesta::class)->orderBy('created_at', 'desc');
    }

    /**
     * Scope para encuestas habilitadas
     */
    public function scopeHabilitadas($query)
    {
        return $query->where('habilitada', true);
    }

    /**
     * Scope para encuestas por estado
     */
    public function scopePorEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Verifica si la encuesta está disponible
     */
    public function estaDisponible(): bool
    {
        if (!$this->habilitada) {
            return false;
        }

        // Verificar fecha de inicio
        if ($this->fecha_inicio && now()->lt($this->fecha_inicio)) {
            return false;
        }

        // Verificar fecha de fin
        if ($this->fecha_fin && now()->gt($this->fecha_fin)) {
            return false;
        }

        return true;
    }

    /**
     * Validar integridad de la encuesta antes de cambiar estado
     */
    public function validarIntegridad(): array
    {
        $errores = [];

        // Verificar que tenga preguntas
        if ($this->preguntas->isEmpty()) {
            $errores[] = 'La encuesta no tiene preguntas configuradas.';
        }

        // Verificar que las preguntas obligatorias tengan respuestas
        $preguntasObligatorias = $this->preguntas()->where('obligatoria', true)->get();
        foreach ($preguntasObligatorias as $pregunta) {
            if ($pregunta->necesitaRespuestas() && $pregunta->respuestas->isEmpty()) {
                $errores[] = "La pregunta '{$pregunta->texto}' es obligatoria pero no tiene respuestas configuradas.";
            }
        }

        // Verificar fechas si están configuradas
        if ($this->fecha_inicio && $this->fecha_fin && $this->fecha_inicio->gt($this->fecha_fin)) {
            $errores[] = 'La fecha de inicio no puede ser posterior a la fecha de fin.';
        }

        // Verificar número de encuestas
        if ($this->numero_encuestas <= 0) {
            $errores[] = 'El número de encuestas debe ser mayor a 0.';
        }

        // Verificar empresa
        if (!$this->empresa_id) {
            $errores[] = 'Debe seleccionar una empresa.';
        }

        return $errores;
    }

    /**
     * Verificar si puede cambiar a estado "Enviada" o "Publicada"
     */
    public function puedeCambiarEstado(string $nuevoEstado): bool
    {
        if (in_array($nuevoEstado, ['enviada', 'publicada'])) {
            $errores = $this->validarIntegridad();
            return empty($errores);
        }
        return true;
    }

    /**
     * Generar token único para un destinatario específico
     */
    public function generarTokenParaDestinatario(string $emailDestinatario, int $horasValidez = 24): TokenEncuesta
    {
        return TokenEncuesta::generarToken($emailDestinatario, $this->id, $horasValidez);
    }

    /**
     * Verificar si un token es válido para esta encuesta
     */
    public function tokenValido(string $token): bool
    {
        $tokenEncuesta = $this->tokensAcceso()
            ->where('token_acceso', $token)
            ->first();

        return $tokenEncuesta && $tokenEncuesta->esValido();
    }

    /**
     * Obtener token por valor
     */
    public function obtenerToken(string $token): ?TokenEncuesta
    {
        return $this->tokensAcceso()
            ->where('token_acceso', $token)
            ->first();
    }

    /**
     * Calcular estadísticas de envío
     */
    public function calcularEstadisticasEnvio(): array
    {
        $total = $this->numero_encuestas;
        $enviadas = $this->encuestas_enviadas;
        $respondidas = $this->encuestas_respondidas;
        $pendientes = $total - $enviadas;

        return [
            'total' => $total,
            'enviadas' => $enviadas,
            'respondidas' => $respondidas,
            'pendientes' => $pendientes,
            'porcentaje_respuesta' => $total > 0 ? round(($respondidas / $total) * 100, 2) : 0
        ];
    }

    /**
     * Verificar si puede enviarse masivamente
     */
    public function puedeEnviarseMasivamente(): bool
    {
        return $this->enviar_por_correo &&
               $this->envio_masivo_activado &&
               $this->estado === 'borrador' &&
               $this->validacion_completada;
    }

    /**
     * Crear bloques de envío en la base de datos
     */
    public function crearBloquesEnvio(int $minutosEntreBloques = 7): void
    {
        // Eliminar bloques existentes si los hay
        $this->bloquesEnvio()->delete();

        $totalEncuestas = $this->numero_encuestas;
        $tamanoBloque = 100;
        $totalBloques = ceil($totalEncuestas / $tamanoBloque);

        $bloques = [];
        for ($i = 0; $i < $totalBloques; $i++) {
            $inicio = $i * $tamanoBloque;
            $fin = min(($i + 1) * $tamanoBloque, $totalEncuestas);
            $cantidad = $fin - $inicio;

            $bloques[] = [
                'encuesta_id' => $this->id,
                'numero_bloque' => $i + 1,
                'cantidad_correos' => $cantidad,
                'estado' => 'pendiente',
                'fecha_programada' => now()->addMinutes(($i + 1) * $minutosEntreBloques),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // Insertar todos los bloques
        DB::table('bloques_envio')->insert($bloques);
    }

    /**
     * Obtener bloques de envío desde la base de datos
     */
    public function obtenerBloquesEnvio(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->bloquesEnvio()->orderBy('numero_bloque')->get();
    }

    /**
     * Calcular bloques de envío (método legacy para compatibilidad)
     */
    public function calcularBloquesEnvio(): array
    {
        $bloques = $this->obtenerBloquesEnvio();

        return $bloques->map(function($bloque) {
            return [
                'numero' => $bloque->numero_bloque,
                'inicio' => ($bloque->numero_bloque - 1) * 100,
                'fin' => min($bloque->numero_bloque * 100, $this->numero_encuestas),
                'cantidad' => $bloque->cantidad_correos,
                'estado' => $bloque->estado,
                'fecha_programada' => $bloque->fecha_programada
            ];
        })->toArray();
    }

    /**
     * Obtener estadísticas detalladas del envío
     */
    public function obtenerEstadisticasEnvioDetalladas(): array
    {
        $stats = $this->calcularEstadisticasEnvio();
        $bloques = $this->obtenerBloquesEnvio();

        $bloquesEnviados = $bloques->where('estado', 'enviado')->count();
        $bloquesPendientes = $bloques->where('estado', 'pendiente')->count();
        $bloquesEnProceso = $bloques->where('estado', 'en_proceso')->count();
        $bloquesError = $bloques->where('estado', 'error')->count();

        // Calcular tiempo estimado basado en bloques pendientes
        $siguienteBloque = $bloques->where('estado', 'pendiente')->first();
        $tiempoEstimado = $siguienteBloque ? $siguienteBloque->tiempoRestante() : 0;

        return array_merge($stats, [
            'total_bloques' => $bloques->count(),
            'bloques_enviados' => $bloquesEnviados,
            'bloques_pendientes' => $bloquesPendientes,
            'bloques_en_proceso' => $bloquesEnProceso,
            'bloques_error' => $bloquesError,
            'progreso_porcentaje' => $bloques->count() > 0 ? round(($bloquesEnviados / $bloques->count()) * 100, 2) : 0,
            'tiempo_estimado_minutos' => $tiempoEstimado,
            'siguiente_envio' => $siguienteBloque ? $siguienteBloque->fecha_programada : null
        ]);
    }

    /**
     * Verificar si el envío está en progreso
     */
    public function envioEnProgreso(): bool
    {
        return $this->estado === 'enviada' && $this->encuestas_enviadas < $this->numero_encuestas;
    }

    /**
     * Verificar si el envío está completado
     */
    public function envioCompletado(): bool
    {
        return $this->encuestas_enviadas >= $this->numero_encuestas;
    }

    /**
     * Obtener el siguiente bloque a enviar
     */
    public function obtenerSiguienteBloque(): ?BloqueEnvio
    {
        return $this->bloquesEnvio()
            ->where('estado', 'pendiente')
            ->where('fecha_programada', '<=', now())
            ->orderBy('numero_bloque')
            ->first();
    }

    /**
     * Marcar bloque como enviado
     */
    public function marcarBloqueEnviado(int $numeroBloque): void
    {
        $bloque = $this->bloquesEnvio()
            ->where('numero_bloque', $numeroBloque)
            ->first();

        if ($bloque) {
            $this->encuestas_enviadas += $bloque->cantidad_correos;
            $this->encuestas_pendientes = max(0, $this->numero_encuestas - $this->encuestas_enviadas);
            $this->save();
        }
    }

    /**
     * Generar enlace dinámico para un destinatario específico
     */
    public function generarEnlaceDinamico(string $emailDestinatario, int $horasValidez = 24): string
    {
        $token = $this->generarTokenParaDestinatario($emailDestinatario, $horasValidez);
        return $token->obtenerEnlace();
    }

    /**
     * Verificar si un enlace está vencido
     */
    public function enlaceVencido(string $token): bool
    {
        $tokenEncuesta = $this->obtenerToken($token);
        return $tokenEncuesta ? $tokenEncuesta->haExpirado() : true;
    }

    /**
     * Renovar enlace vencido para un destinatario específico
     */
    public function renovarEnlace(string $emailDestinatario, int $horasValidez = 24): string
    {
        return $this->generarEnlaceDinamico($emailDestinatario, $horasValidez);
    }

    /**
     * Genera un slug único para la encuesta
     */
    public function generarSlug(): string
    {
        $baseSlug = Str::slug($this->titulo);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($encuesta) {
            if (empty($encuesta->slug)) {
                $encuesta->slug = $encuesta->generarSlug();
            }
        });

        static::updating(function ($encuesta) {
            if ($encuesta->isDirty('titulo') && empty($encuesta->slug)) {
                $encuesta->slug = $encuesta->generarSlug();
            }
        });
    }

    /**
     * Verificar si la encuesta está lista para el siguiente paso
     */
    public function puedeAvanzarA(string $paso): bool
    {
        switch ($paso) {
            case 'preguntas':
                return !empty($this->titulo) && !empty($this->empresa_id);

            case 'respuestas':
                return $this->preguntas()->count() > 0;

            case 'logica':
                $preguntasConRespuestas = $this->preguntas()
                    ->necesitaRespuestas()
                    ->whereHas('respuestas')
                    ->count();
                $totalPreguntasNecesitanRespuestas = $this->preguntas()
                    ->necesitaRespuestas()
                    ->count();
                return $totalPreguntasNecesitanRespuestas === 0 || $preguntasConRespuestas === $totalPreguntasNecesitanRespuestas;

            case 'preview':
                return $this->preguntas()->count() > 0;

            case 'envio':
                return $this->preguntas()->count() > 0 && $this->estado !== 'borrador';

            default:
                return false;
        }
    }

    /**
     * Obtener el progreso del flujo de configuración
     */
    public function obtenerProgresoConfiguracion(): array
    {
        $pasos = [
            'crear_encuesta' => [
                'nombre' => 'Crear Encuesta',
                'completado' => !empty($this->titulo) && !empty($this->empresa_id),
                'ruta' => route('encuestas.edit', $this->id),
                'icono' => 'fas fa-clipboard-list'
            ],
            'agregar_preguntas' => [
                'nombre' => 'Agregar Preguntas',
                'completado' => $this->preguntas()->count() > 0,
                'ruta' => route('encuestas.preguntas.create', $this->id),
                'icono' => 'fas fa-question-circle',
                'cantidad' => $this->preguntas()->count()
            ],
            'configurar_respuestas' => [
                'nombre' => 'Configurar Respuestas',
                'completado' => $this->puedeAvanzarA('logica'),
                'ruta' => route('encuestas.respuestas.create', $this->id),
                'icono' => 'fas fa-list-check',
                'cantidad' => $this->preguntas()->necesitaRespuestas()->whereHas('respuestas')->count()
            ],
            'configurar_logica' => [
                'nombre' => 'Configurar Lógica',
                'completado' => $this->preguntas()->whereHas('logica')->count() > 0,
                'ruta' => route('encuestas.logica.create', $this->id),
                'icono' => 'fas fa-cogs'
            ],
            'vista_previa' => [
                'nombre' => 'Vista Previa',
                'completado' => $this->preguntas()->count() > 0,
                'ruta' => route('encuestas.preview', $this->id),
                'icono' => 'fas fa-eye'
            ],
            'configurar_envio' => [
                'nombre' => 'Configurar Envío',
                'completado' => $this->estado !== 'borrador',
                'ruta' => route('encuestas.envio.create', $this->id),
                'icono' => 'fas fa-paper-plane'
            ]
        ];

        $pasosCompletados = collect($pasos)->where('completado', true)->count();
        $totalPasos = count($pasos);

        return [
            'pasos' => $pasos,
            'completados' => $pasosCompletados,
            'total' => $totalPasos,
            'porcentaje' => $totalPasos > 0 ? round(($pasosCompletados / $totalPasos) * 100, 2) : 0,
            'siguiente_paso' => $this->obtenerSiguientePaso($pasos)
        ];
    }

    /**
     * Obtener el siguiente paso a completar
     */
    private function obtenerSiguientePaso(array $pasos): ?string
    {
        foreach ($pasos as $clave => $paso) {
            if (!$paso['completado']) {
                return $clave;
            }
        }
        return null;
    }

    /**
     * Verificar si la encuesta está completamente configurada
     */
    public function estaCompletamenteConfigurada(): bool
    {
        return $this->preguntas()->count() > 0 &&
               $this->puedeAvanzarA('logica') &&
               $this->estado !== 'borrador';
    }

    /**
     * Obtener estadísticas de configuración
     */
    public function obtenerEstadisticasConfiguracion(): array
    {
        $totalPreguntas = $this->preguntas()->count();
        $preguntasObligatorias = $this->preguntas()->where('obligatoria', true)->count();
        $preguntasConRespuestas = $this->preguntas()->necesitaRespuestas()->whereHas('respuestas')->count();
        $preguntasSinRespuestas = $this->preguntas()->necesitaRespuestas()->whereDoesntHave('respuestas')->count();
        $preguntasConLogica = $this->preguntas()->whereHas('logica')->count();

        return [
            'total_preguntas' => $totalPreguntas,
            'preguntas_obligatorias' => $preguntasObligatorias,
            'preguntas_opcionales' => $totalPreguntas - $preguntasObligatorias,
            'preguntas_con_respuestas' => $preguntasConRespuestas,
            'preguntas_sin_respuestas' => $preguntasSinRespuestas,
            'preguntas_con_logica' => $preguntasConLogica,
            'completada' => $this->estaCompletamenteConfigurada()
        ];
    }
}
