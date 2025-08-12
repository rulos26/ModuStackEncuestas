<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class LogicaWizardSessionMiddleware
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
        // Sincronizar datos de sesión con cookies para persistencia
        $this->sincronizarSesionConCookies();

        return $next($request);
    }

    /**
     * Sincronizar datos de sesión con cookies
     */
    private function sincronizarSesionConCookies()
    {
        // Variables de sesión del wizard de lógica
        $variablesSesion = [
            'wizard_encuesta_id',
            'wizard_pregunta_index',
            'wizard_logica_count'
        ];

        foreach ($variablesSesion as $variable) {
            // Si existe en sesión pero no en cookie, crear cookie
            if (Session::has($variable) && !Cookie::has($variable)) {
                Cookie::queue($variable, Session::get($variable), 60 * 24); // 24 horas
            }
            // Si existe en cookie pero no en sesión, restaurar sesión
            elseif (Cookie::has($variable) && !Session::has($variable)) {
                Session::put($variable, Cookie::get($variable));
            }
            // Si existe en ambos, sincronizar (sesión tiene prioridad)
            elseif (Session::has($variable) && Cookie::has($variable)) {
                if (Session::get($variable) !== Cookie::get($variable)) {
                    Cookie::queue($variable, Session::get($variable), 60 * 24); // 24 horas
                }
            }
        }
    }
}
