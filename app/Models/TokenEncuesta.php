<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TokenEncuesta extends Model
{
    protected $table = 'tokens_encuesta';

    protected $fillable = [
        'encuesta_id',
        'email_destinatario',
        'token_acceso',
        'fecha_expiracion',
        'usado',
        'fecha_uso',
        'ip_acceso',
        'user_agent'
    ];

    protected $casts = [
        'fecha_expiracion' => 'datetime',
        'fecha_uso' => 'datetime',
        'usado' => 'boolean'
    ];

    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    /**
     * Generar token único para un destinatario
     */
    public static function generarToken(string $emailDestinatario, int $encuestaId, int $horasValidez = 24): self
    {
        return self::create([
            'encuesta_id' => $encuestaId,
            'email_destinatario' => $emailDestinatario,
            'token_acceso' => Str::random(64),
            'fecha_expiracion' => now()->addHours($horasValidez)
        ]);
    }

    /**
     * Verificar si el token es válido
     */
    public function esValido(): bool
    {
        return !$this->usado &&
               !$this->fecha_expiracion->isPast();
    }

    /**
     * Marcar token como usado
     */
    public function marcarUsado(string $ip = null, string $userAgent = null): void
    {
        $this->update([
            'usado' => true,
            'fecha_uso' => now(),
            'ip_acceso' => $ip,
            'user_agent' => $userAgent
        ]);
    }

    /**
     * Renovar token (crear uno nuevo)
     */
    public function renovar(int $horasValidez = 24): self
    {
        return self::generarToken(
            $this->email_destinatario,
            $this->encuesta_id,
            $horasValidez
        );
    }

    /**
     * Obtener enlace completo para la encuesta
     */
    public function obtenerEnlace(): string
    {
        return route('encuestas.publica', $this->encuesta->slug) . '?token=' . $this->token_acceso;
    }

    /**
     * Verificar si el token ha expirado
     */
    public function haExpirado(): bool
    {
        return $this->fecha_expiracion->isPast();
    }

    /**
     * Obtener tiempo restante en formato legible
     */
    public function tiempoRestante(): string
    {
        $diff = now()->diff($this->fecha_expiracion);

        if ($diff->invert) {
            return 'Expirado';
        }

        if ($diff->h > 0) {
            return $diff->h . 'h ' . $diff->i . 'm';
        }

        return $diff->i . 'm ' . $diff->s . 's';
    }
}
