<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserSession extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'current_route',
        'current_page',
        'last_activity',
        'is_active',
        'login_time',
        'logout_time',
        'additional_data',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'last_activity' => 'datetime',
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
        'is_active' => 'boolean',
        'additional_data' => 'array',
    ];

    /**
     * Relación con el usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para sesiones activas.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para sesiones inactivas.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para sesiones recientes (últimas 24 horas).
     */
    public function scopeRecent($query)
    {
        return $query->where('last_activity', '>=', Carbon::now()->subDay());
    }

    /**
     * Scope para sesiones por usuario.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Obtener el tiempo de inactividad en formato legible.
     */
    public function getInactivityTimeAttribute(): string
    {
        $lastActivity = $this->last_activity;
        $now = Carbon::now();

        $diff = $lastActivity->diff($now);

        if ($diff->days > 0) {
            return $diff->days . ' día(s)';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hora(s)';
        } elseif ($diff->i > 0) {
            return $diff->i . ' minuto(s)';
        } else {
            return $diff->s . ' segundo(s)';
        }
    }

    /**
     * Obtener el tiempo de sesión en formato legible.
     */
    public function getSessionDurationAttribute(): string
    {
        $loginTime = $this->login_time;
        $endTime = $this->logout_time ?? Carbon::now();

        $diff = $loginTime->diff($endTime);

        if ($diff->days > 0) {
            return $diff->days . ' día(s) ' . $diff->h . ' hora(s)';
        } elseif ($diff->h > 0) {
            return $diff->h . ' hora(s) ' . $diff->i . ' minuto(s)';
        } else {
            return $diff->i . ' minuto(s)';
        }
    }

    /**
     * Obtener información del navegador.
     */
    public function getBrowserInfoAttribute(): array
    {
        $userAgent = $this->user_agent;

        // Detectar navegador
        $browser = 'Desconocido';
        if (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            $browser = 'Opera';
        }

        // Detectar sistema operativo
        $os = 'Desconocido';
        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $os = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            $os = 'iOS';
        }

        return [
            'browser' => $browser,
            'os' => $os,
            'user_agent' => $userAgent
        ];
    }

    /**
     * Verificar si la sesión está activa basada en el tiempo de inactividad.
     */
    public function isSessionActive(int $timeoutMinutes = 30): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $timeout = Carbon::now()->subMinutes($timeoutMinutes);
        return $this->last_activity->greaterThan($timeout);
    }

    /**
     * Marcar sesión como inactiva.
     */
    public function markAsInactive(): void
    {
        $this->update([
            'is_active' => false,
            'logout_time' => Carbon::now()
        ]);
    }

    /**
     * Actualizar la actividad de la sesión.
     */
    public function updateActivity(string $route = null, string $page = null): void
    {
        $this->update([
            'last_activity' => Carbon::now(),
            'current_route' => $route,
            'current_page' => $page
        ]);
    }
}
