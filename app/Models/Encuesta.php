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
        'tiempo_disponible',
        'enviar_por_correo',
        'estado',
        'user_id',
        'slug',
        'habilitada'
    ];

    protected $casts = [
        'tiempo_disponible' => 'datetime',
        'enviar_por_correo' => 'boolean',
        'habilitada' => 'boolean',
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

        if ($this->tiempo_disponible && now()->gt($this->tiempo_disponible)) {
            return false;
        }

        return true;
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
