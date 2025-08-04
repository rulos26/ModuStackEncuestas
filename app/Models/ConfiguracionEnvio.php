<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionEnvio extends Model
{
    use HasFactory;

    protected $table = 'configuracion_envios';

    protected $fillable = [
        'empresa_id',
        'encuesta_id',
        'nombre_remitente',
        'correo_remitente',
        'asunto',
        'cuerpo_mensaje',
        'tipo_envio',
        'plantilla',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para tipos de envío
    const TIPO_AUTOMATICO = 'automatico';
    const TIPO_MANUAL = 'manual';
    const TIPO_PROGRAMADO = 'programado';

    /**
     * Obtener la empresa asociada
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Obtener la encuesta asociada
     */
    public function encuesta(): BelongsTo
    {
        return $this->belongsTo(Encuesta::class);
    }

    /**
     * Scope para configuraciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para configuraciones por empresa
     */
    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * Scope para configuraciones por encuesta
     */
    public function scopePorEncuesta($query, $encuestaId)
    {
        return $query->where('encuesta_id', $encuestaId);
    }

    /**
     * Obtener tipos de envío disponibles
     */
    public static function getTiposEnvio(): array
    {
        return [
            self::TIPO_AUTOMATICO => 'Automático al finalizar encuesta',
            self::TIPO_MANUAL => 'Manual',
            self::TIPO_PROGRAMADO => 'Programado'
        ];
    }

    /**
     * Verificar si la configuración está activa
     */
    public function estaActiva(): bool
    {
        return $this->activo;
    }

    /**
     * Obtener el estado de configuración como texto
     */
    public function getEstadoConfiguracionAttribute(): string
    {
        return $this->activo ? 'Configurado' : 'No Configurado';
    }

    /**
     * Obtener el tipo de envío como texto
     */
    public function getTipoEnvioTextoAttribute(): string
    {
        $tipos = self::getTiposEnvio();
        return $tipos[$this->tipo_envio] ?? 'Desconocido';
    }
}
