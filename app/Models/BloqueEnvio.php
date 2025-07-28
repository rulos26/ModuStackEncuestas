<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloqueEnvio extends Model
{
    protected $table = 'bloques_envio';

    protected $fillable = [
        'encuesta_id',
        'numero_bloque',
        'cantidad_correos',
        'estado',
        'fecha_programada',
        'fecha_envio',
        'correos_enviados',
        'correos_fallidos',
        'errores'
    ];

    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_envio' => 'datetime',
        'errores' => 'array'
    ];

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    /**
     * Marcar bloque como en proceso
     */
    public function marcarEnProceso(): void
    {
        $this->update([
            'estado' => 'en_proceso',
            'fecha_envio' => now()
        ]);
    }

    /**
     * Marcar bloque como enviado
     */
    public function marcarEnviado(int $enviados, int $fallidos = 0, array $errores = []): void
    {
        $this->update([
            'estado' => 'enviado',
            'correos_enviados' => $enviados,
            'correos_fallidos' => $fallidos,
            'errores' => $errores
        ]);
    }

    /**
     * Marcar bloque como error
     */
    public function marcarError(array $errores = []): void
    {
        $this->update([
            'estado' => 'error',
            'errores' => $errores
        ]);
    }

    /**
     * Verificar si el bloque está listo para envío
     */
    public function estaListoParaEnvio(): bool
    {
        return $this->estado === 'pendiente' &&
               $this->fecha_programada->lte(now());
    }

    /**
     * Obtener tiempo restante hasta el envío
     */
    public function tiempoRestante(): int
    {
        return max(0, now()->diffInMinutes($this->fecha_programada));
    }
}
