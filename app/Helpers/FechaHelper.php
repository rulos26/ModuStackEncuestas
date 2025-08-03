<?php

namespace App\Helpers;

use Carbon\Carbon;

class FechaHelper
{
    /**
     * Obtener la fecha actual en la zona horaria configurada
     */
    public static function hoy(): Carbon
    {
        return Carbon::now()->startOfDay();
    }

    /**
     * Obtener la fecha actual formateada para mostrar
     */
    public static function hoyFormateada(): string
    {
        return self::hoy()->format('d/m/Y');
    }

    /**
     * Obtener la fecha actual para validaciones (formato Y-m-d)
     */
    public static function hoyParaValidacion(): string
    {
        return self::hoy()->format('Y-m-d');
    }

    /**
     * Verificar si una fecha es válida para inicio de encuesta
     */
    public static function esFechaInicioValida($fecha): bool
    {
        if (!$fecha) {
            return true; // Las fechas son opcionales
        }

        $fechaInicio = Carbon::parse($fecha);
        $hoy = self::hoy();

        return $fechaInicio->gte($hoy);
    }

    /**
     * Verificar si una fecha de fin es válida respecto a la fecha de inicio
     */
    public static function esFechaFinValida($fechaFin, $fechaInicio): bool
    {
        if (!$fechaFin || !$fechaInicio) {
            return true; // Las fechas son opcionales
        }

        $fin = Carbon::parse($fechaFin);
        $inicio = Carbon::parse($fechaInicio);

        return $fin->gte($inicio);
    }

    /**
     * Obtener mensaje de error personalizado para fecha de inicio
     */
    public static function getMensajeErrorFechaInicio(): string
    {
        return 'La fecha de inicio debe ser igual o posterior a hoy (' . self::hoyFormateada() . ').';
    }

    /**
     * Obtener mensaje de error personalizado para fecha de fin
     */
    public static function getMensajeErrorFechaFin(): string
    {
        return 'La fecha de fin debe ser igual o posterior a la fecha de inicio.';
    }

    /**
     * Formatear fecha para mostrar en la interfaz
     */
    public static function formatearFecha($fecha): string
    {
        if (!$fecha) {
            return 'No especificada';
        }

        return Carbon::parse($fecha)->format('d/m/Y');
    }

    /**
     * Formatear fecha y hora para mostrar en la interfaz
     */
    public static function formatearFechaHora($fecha): string
    {
        if (!$fecha) {
            return 'No especificada';
        }

        return Carbon::parse($fecha)->format('d/m/Y H:i');
    }

    /**
     * Obtener la zona horaria actual del sistema
     */
    public static function getZonaHoraria(): string
    {
        return config('app.timezone', 'UTC');
    }

    /**
     * Obtener información de la zona horaria para debugging
     */
    public static function getInfoZonaHoraria(): array
    {
        return [
            'zona_horaria' => self::getZonaHoraria(),
            'fecha_actual' => self::hoyFormateada(),
            'timestamp' => Carbon::now()->timestamp,
            'utc' => Carbon::now()->utc()->format('Y-m-d H:i:s'),
            'local' => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }
} 