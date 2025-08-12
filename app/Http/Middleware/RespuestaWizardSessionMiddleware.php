<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class RespuestaWizardSessionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Si no hay encuesta_id en la sesión, intentar recuperarlo de las cookies
        if (!Session::has('wizard_encuesta_id') && $request->hasCookie('wizard_encuesta_id')) {
            $encuestaId = $request->cookie('wizard_encuesta_id');
            Session::put('wizard_encuesta_id', $encuestaId);
        }

        // Si no hay índice de pregunta en la sesión, intentar recuperarlo de las cookies
        if (!Session::has('wizard_pregunta_index') && $request->hasCookie('wizard_pregunta_index')) {
            $preguntaIndex = $request->cookie('wizard_pregunta_index');
            Session::put('wizard_pregunta_index', $preguntaIndex);
        }

        // Si no hay contador de respuestas en la sesión, intentar recuperarlo de las cookies
        if (!Session::has('wizard_respuestas_count') && $request->hasCookie('wizard_respuestas_count')) {
            $respuestasCount = $request->cookie('wizard_respuestas_count');
            Session::put('wizard_respuestas_count', $respuestasCount);
        }

        $response = $next($request);

        // Guardar el estado del wizard en cookies como respaldo
        if (Session::has('wizard_encuesta_id')) {
            $response->cookie('wizard_encuesta_id', Session::get('wizard_encuesta_id'), 60); // 1 hora
        }

        if (Session::has('wizard_pregunta_index')) {
            $response->cookie('wizard_pregunta_index', Session::get('wizard_pregunta_index'), 60); // 1 hora
        }

        if (Session::has('wizard_respuestas_count')) {
            $response->cookie('wizard_respuestas_count', Session::get('wizard_respuestas_count'), 60); // 1 hora
        }

        return $response;
    }
}
