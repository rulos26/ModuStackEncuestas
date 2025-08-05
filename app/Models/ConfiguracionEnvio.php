<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\EmpresasCliente;

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
        'activo',
        // Nuevos campos para envío programado
        'fecha_envio',
        'hora_envio',
        'tipo_destinatario',
        'numero_bloques',
        'correo_prueba',
        'modo_prueba',
        'estado_programacion'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'modo_prueba' => 'boolean',
        'fecha_envio' => 'date',
        'hora_envio' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para tipos de envío (simplificadas)
    const TIPO_MANUAL = 'manual';
    const TIPO_PROGRAMADO = 'programado';

    // Constantes para tipos de destinatario
    const DESTINATARIO_EMPLEADOS = 'empleados';
    const DESTINATARIO_CLIENTES = 'clientes';
    const DESTINATARIO_PROVEEDORES = 'proveedores';
    const DESTINATARIO_PERSONALIZADO = 'personalizado';

    // Constantes para estado de programación
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_CANCELADO = 'cancelado';

    /**
     * Obtener la empresa asociada
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(EmpresasCliente::class, 'empresa_id');
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
     * Scope para configuraciones programadas pendientes
     */
    public function scopeProgramadasPendientes($query)
    {
        return $query->where('tipo_envio', self::TIPO_PROGRAMADO)
                    ->where('estado_programacion', self::ESTADO_PENDIENTE)
                    ->where('activo', true);
    }

    /**
     * Obtener tipos de envío disponibles (simplificados)
     */
    public static function getTiposEnvio(): array
    {
        return [
            self::TIPO_MANUAL => 'Manual',
            self::TIPO_PROGRAMADO => 'Programado'
        ];
    }

    /**
     * Obtener tipos de destinatario disponibles
     */
    public static function getTiposDestinatario(): array
    {
        return [
            self::DESTINATARIO_EMPLEADOS => 'Empleados',
            self::DESTINATARIO_CLIENTES => 'Clientes',
            self::DESTINATARIO_PROVEEDORES => 'Proveedores',
            self::DESTINATARIO_PERSONALIZADO => 'Lista Personalizada'
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
     * Verificar si es envío programado
     */
    public function esProgramado(): bool
    {
        return $this->tipo_envio === self::TIPO_PROGRAMADO;
    }

    /**
     * Verificar si es envío manual
     */
    public function esManual(): bool
    {
        return $this->tipo_envio === self::TIPO_MANUAL;
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

    /**
     * Obtener el tipo de destinatario como texto
     */
    public function getTipoDestinatarioTextoAttribute(): string
    {
        $tipos = self::getTiposDestinatario();
        return $tipos[$this->tipo_destinatario] ?? 'No definido';
    }

    /**
     * Calcular fecha y hora completa de envío
     */
    public function getFechaHoraEnvioAttribute()
    {
        if (!$this->hora_envio) {
            return null;
        }

        // hora_envio ya es un datetime completo
        return $this->hora_envio;
    }

    /**
     * Verificar si el envío programado está listo para ejecutarse
     */
    public function estaListoParaEnvio(): bool
    {
        if (!$this->esProgramado() || $this->estado_programacion !== self::ESTADO_PENDIENTE) {
            return false;
        }

        $fechaHoraEnvio = $this->fecha_hora_envio;
        if (!$fechaHoraEnvio) {
            return false;
        }

        return now()->gte($fechaHoraEnvio);
    }

    /**
     * Marcar como en proceso
     */
    public function marcarEnProceso(): void
    {
        $this->update(['estado_programacion' => self::ESTADO_EN_PROCESO]);
    }

    /**
     * Marcar como completado
     */
    public function marcarCompletado(): void
    {
        $this->update(['estado_programacion' => self::ESTADO_COMPLETADO]);
    }

    /**
     * Marcar como cancelado
     */
    public function marcarCancelado(): void
    {
        $this->update(['estado_programacion' => self::ESTADO_CANCELADO]);
    }

    /**
     * Calcular número de bloques sugerido basado en número de destinatarios
     */
    public static function calcularBloquesSugeridos(int $numeroDestinatarios): int
    {
        if ($numeroDestinatarios <= 50) {
            return 1;
        }

        // 1 bloque por cada 100 destinatarios, mínimo 2 bloques
        $bloques = max(2, ceil($numeroDestinatarios / 100));

        // Máximo 10 bloques para evitar sobrecarga
        return min($bloques, 10);
    }
}
