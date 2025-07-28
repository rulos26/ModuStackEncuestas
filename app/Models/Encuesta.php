<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
        'tiempo_disponible',
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
        'tiempo_disponible' => 'datetime',
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

        // Verificar tiempo de disponibilidad (legacy)
        if ($this->tiempo_disponible && now()->gt($this->tiempo_disponible)) {
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
     * Genera un token de acceso único para la encuesta
     */
    public function generarTokenAcceso(): string
    {
        return Str::random(32);
    }

    /**
     * Verificar si el token de acceso es válido
     */
    public function tokenValido(string $token): bool
    {
        if ($this->token_acceso !== $token) {
            return false;
        }

        if ($this->token_expiracion && now()->gt($this->token_expiracion)) {
            return false;
        }

        return true;
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
}
