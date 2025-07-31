<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestaUsuario extends Model
{
    protected $table = 'respuestas_usuario';

    protected $fillable = [
        'encuesta_id',
        'pregunta_id',
        'respuesta_id',
        'respuesta_texto',
        'ip_address',
        'user_agent',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con la encuesta
     */
    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    /**
     * Relación con la pregunta
     */
    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(Pregunta::class);
    }

    /**
     * Relación con la respuesta (si es de selección)
     */
    public function respuesta(): BelongsTo
    {
        return $this->belongsTo(Respuesta::class);
    }

    /**
     * Scope para respuestas de texto
     */
    public function scopeTexto($query)
    {
        return $query->whereNotNull('respuesta_texto');
    }

    /**
     * Scope para respuestas de selección
     */
    public function scopeSeleccion($query)
    {
        return $query->whereNotNull('respuesta_id');
    }

    /**
     * Obtener el valor de la respuesta
     */
    public function getValorAttribute()
    {
        if ($this->respuesta_id) {
            return $this->respuesta->texto ?? 'Respuesta no encontrada';
        }

        return $this->respuesta_texto ?? 'Sin respuesta';
    }

    /**
     * Verificar si es respuesta de texto
     */
    public function esTexto(): bool
    {
        return !empty($this->respuesta_texto);
    }

    /**
     * Verificar si es respuesta de selección
     */
    public function esSeleccion(): bool
    {
        return !empty($this->respuesta_id);
    }
}
