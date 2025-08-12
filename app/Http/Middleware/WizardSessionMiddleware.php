<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class WizardSessionMiddleware
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

        // Si no hay contador de preguntas en la sesión, intentar recuperarlo de las cookies
        if (!Session::has('wizard_preguntas_count') && $request->hasCookie('wizard_preguntas_count')) {
            $preguntasCount = $request->cookie('wizard_preguntas_count');
            Session::put('wizard_preguntas_count', $preguntasCount);
        }

        $response = $next($request);

        // Guardar el estado del wizard en cookies como respaldo
        if (Session::has('wizard_encuesta_id')) {
            $response->cookie('wizard_encuesta_id', Session::get('wizard_encuesta_id'), 60); // 1 hora
        }

        if (Session::has('wizard_preguntas_count')) {
            $response->cookie('wizard_preguntas_count', Session::get('wizard_preguntas_count'), 60); // 1 hora
        }

        return $response;
    }
}
