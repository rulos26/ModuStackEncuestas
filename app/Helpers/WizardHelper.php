<?php

namespace App\Helpers;

use App\Models\Encuesta;
use Illuminate\Support\Facades\Session;

class WizardHelper
{
    /**
     * Obtener el número real de preguntas en una encuesta
     */
    public static function getPreguntasCount($encuestaId)
    {
        $encuesta = Encuesta::find($encuestaId);
        return $encuesta ? $encuesta->preguntas->count() : 0;
    }

    /**
     * Obtener el número de preguntas creadas en la sesión actual
     */
    public static function getPreguntasEnSesion()
    {
        return Session::get('wizard_preguntas_count', 0);
    }

    /**
     * Obtener información completa del estado del wizard
     */
    public static function getWizardStatus()
    {
        $encuestaId = Session::get('wizard_encuesta_id');

        if (!$encuestaId) {
            return null;
        }

        $encuesta = Encuesta::find($encuestaId);
        if (!$encuesta) {
            return null;
        }

        return [
            'encuesta_id' => $encuestaId,
            'encuesta_titulo' => $encuesta->titulo,
            'preguntas_en_bd' => $encuesta->preguntas->count(),
            'preguntas_en_sesion' => Session::get('wizard_preguntas_count', 0),
            'total_real' => $encuesta->preguntas->count()
        ];
    }

    /**
     * Verificar si hay una sesión activa del wizard
     */
    public static function hasActiveSession()
    {
        return Session::has('wizard_encuesta_id') && Session::has('wizard_preguntas_count');
    }

    /**
     * Limpiar completamente el estado del wizard
     */
    public static function clearWizardSession()
    {
        Session::forget(['wizard_encuesta_id', 'wizard_preguntas_count']);
    }
}
