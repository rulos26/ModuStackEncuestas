<?php

namespace App\Helpers;

class EstadoHelper
{
    /**
     * Obtener la clase CSS del badge según el estado
     */
    public static function getBadgeClass($estado): string
    {
        return match ($estado) {
            'borrador' => 'badge-secondary',
            'en_progreso' => 'badge-info',
            'enviada' => 'badge-warning',
            'pausada' => 'badge-dark',
            'completada' => 'badge-success',
            'publicada' => 'badge-primary',
            default => 'badge-light'
        };
    }

    /**
     * Obtener el icono según el estado
     */
    public static function getIcon($estado): string
    {
        return match ($estado) {
            'borrador' => 'fas fa-edit',
            'en_progreso' => 'fas fa-spinner fa-spin',
            'enviada' => 'fas fa-paper-plane',
            'pausada' => 'fas fa-pause',
            'completada' => 'fas fa-check-circle',
            'publicada' => 'fas fa-globe',
            default => 'fas fa-question'
        };
    }

    /**
     * Obtener el nombre legible del estado
     */
    public static function getNombreLegible($estado): string
    {
        return match ($estado) {
            'borrador' => 'Borrador',
            'en_progreso' => 'En Progreso',
            'enviada' => 'Enviada',
            'pausada' => 'Pausada',
            'completada' => 'Completada',
            'publicada' => 'Publicada',
            default => ucfirst($estado)
        };
    }

    /**
     * Obtener la descripción del estado
     */
    public static function getDescripcion($estado): string
    {
        return match ($estado) {
            'borrador' => 'Encuesta en estado de borrador, pendiente de configuración',
            'en_progreso' => 'Encuesta en proceso de configuración o envío',
            'enviada' => 'Encuesta enviada a los destinatarios',
            'pausada' => 'Encuesta pausada temporalmente',
            'completada' => 'Encuesta completada por todos los destinatarios',
            'publicada' => 'Encuesta publicada y disponible públicamente',
            default => 'Estado no definido'
        };
    }

    /**
     * Generar el HTML del badge completo
     */
    public static function getBadgeHtml($estado): string
    {
        $clase = self::getBadgeClass($estado);
        $icono = self::getIcon($estado);
        $nombre = self::getNombreLegible($estado);

        return "<span class='badge {$clase}'><i class='{$icono}'></i> {$nombre}</span>";
    }

    /**
     * Verificar si el estado permite edición
     */
    public static function permiteEdicion($estado): bool
    {
        return in_array($estado, ['borrador', 'en_progreso']);
    }

    /**
     * Verificar si el estado permite envío
     */
    public static function permiteEnvio($estado): bool
    {
        return in_array($estado, ['borrador', 'en_progreso', 'pausada']);
    }

    /**
     * Verificar si el estado permite publicación
     */
    public static function permitePublicacion($estado): bool
    {
        return in_array($estado, ['borrador', 'completada']);
    }

    /**
     * Obtener los estados disponibles para un select
     */
    public static function getEstadosDisponibles(): array
    {
        return [
            'borrador' => 'Borrador',
            'en_progreso' => 'En Progreso',
            'enviada' => 'Enviada',
            'pausada' => 'Pausada',
            'completada' => 'Completada',
            'publicada' => 'Publicada'
        ];
    }
}
