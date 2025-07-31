<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalisisEncuesta extends Model
{
    protected $table = 'analisis_encuesta';

    protected $fillable = [
        'encuesta_id',
        'pregunta_id',
        'tipo_grafico',
        'analisis_ia',
        'configuracion_grafico',
        'datos_procesados',
        'estado',
        'error_mensaje',
        'fecha_analisis'
    ];

    protected $casts = [
        'configuracion_grafico' => 'array',
        'datos_procesados' => 'array',
        'fecha_analisis' => 'datetime'
    ];

    // Estados disponibles
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PROCESANDO = 'procesando';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_ERROR = 'error';

    // Tipos de gráficos disponibles
    const GRAFICO_BARRAS = 'barras';
    const GRAFICO_PASTEL = 'pastel';
    const GRAFICO_LINEAS = 'lineas';
    const GRAFICO_DISPERSION = 'dispersion';
    const GRAFICO_AREA = 'area';
    const GRAFICO_RADAR = 'radar';
    const GRAFICO_HISTOGRAMA = 'histograma';
    const GRAFICO_BOXPLOT = 'boxplot';

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
     * Scope para análisis completados
     */
    public function scopeCompletados($query)
    {
        return $query->where('estado', self::ESTADO_COMPLETADO);
    }

    /**
     * Scope para análisis pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    /**
     * Scope para análisis con errores
     */
    public function scopeConErrores($query)
    {
        return $query->where('estado', self::ESTADO_ERROR);
    }

    /**
     * Verificar si el análisis está completo
     */
    public function estaCompleto(): bool
    {
        return $this->estado === self::ESTADO_COMPLETADO;
    }

    /**
     * Verificar si el análisis tiene error
     */
    public function tieneError(): bool
    {
        return $this->estado === self::ESTADO_ERROR;
    }

    /**
     * Obtener tipos de gráficos disponibles
     */
    public static function getTiposGrafico(): array
    {
        return [
            self::GRAFICO_BARRAS => 'Gráfico de Barras',
            self::GRAFICO_PASTEL => 'Gráfico de Pastel',
            self::GRAFICO_LINEAS => 'Gráfico de Líneas',
            self::GRAFICO_DISPERSION => 'Gráfico de Dispersión',
            self::GRAFICO_AREA => 'Gráfico de Área',
            self::GRAFICO_RADAR => 'Gráfico de Radar',
            self::GRAFICO_HISTOGRAMA => 'Histograma',
            self::GRAFICO_BOXPLOT => 'Diagrama de Caja'
        ];
    }

    /**
     * Obtener configuración del gráfico
     */
    public function getConfiguracionGrafico(): array
    {
        return $this->configuracion_grafico ?? [];
    }

    /**
     * Obtener datos procesados
     */
    public function getDatosProcesados(): array
    {
        return $this->datos_procesados ?? [];
    }

    /**
     * Marcar como completado
     */
    public function marcarCompletado(): void
    {
        $this->update([
            'estado' => self::ESTADO_COMPLETADO,
            'fecha_analisis' => now()
        ]);
    }

    /**
     * Marcar como error
     */
    public function marcarError(string $mensaje): void
    {
        $this->update([
            'estado' => self::ESTADO_ERROR,
            'error_mensaje' => $mensaje
        ]);
    }

    /**
     * Marcar como procesando
     */
    public function marcarProcesando(): void
    {
        $this->update([
            'estado' => self::ESTADO_PROCESANDO
        ]);
    }
}
