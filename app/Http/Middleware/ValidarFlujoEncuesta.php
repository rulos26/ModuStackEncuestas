<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Encuesta;
use Illuminate\Support\Facades\Auth;

class ValidarFlujoEncuesta
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $paso = null)
    {
        $encuestaId = $request->route('encuesta') ?? $request->route('encuestaId');

        if (!$encuestaId) {
            return redirect()->route('encuestas.index')
                ->with('error', 'Encuesta no especificada.');
        }

        $encuesta = Encuesta::find($encuestaId);

        if (!$encuesta) {
            return redirect()->route('encuestas.index')
                ->with('error', 'Encuesta no encontrada.');
        }

        // Verificar permisos de usuario
        if ($encuesta->user_id !== Auth::id()) {
            return redirect()->route('encuestas.index')
                ->with('error', 'No tienes permisos para acceder a esta encuesta.');
        }

        // Validar flujo según el paso
        if ($paso && !$this->validarPaso($encuesta, $paso)) {
            return $this->redirigirAPasoCorrecto($encuesta, $paso);
        }

        return $next($request);
    }

    /**
     * Validar si puede acceder al paso especificado
     */
    private function validarPaso(Encuesta $encuesta, string $paso): bool
    {
        switch ($paso) {
            case 'preguntas':
                return $encuesta->puedeAvanzarA('preguntas');

            case 'respuestas':
                return $encuesta->puedeAvanzarA('respuestas');

            case 'logica':
                return $encuesta->puedeAvanzarA('logica');

            case 'preview':
                return $encuesta->puedeAvanzarA('preview');

            case 'envio':
                return $encuesta->puedeAvanzarA('envio');

            default:
                return true;
        }
    }

    /**
     * Redirigir al paso correcto del flujo
     */
    private function redirigirAPasoCorrecto(Encuesta $encuesta, string $pasoActual)
    {
        $progreso = $encuesta->obtenerProgresoConfiguracion();
        $siguientePaso = $progreso['siguiente_paso'];

        if (!$siguientePaso) {
            return redirect()->route('encuestas.show', $encuesta->id)
                ->with('warning', 'La encuesta ya está completamente configurada.');
        }

        $rutaSiguiente = $progreso['pasos'][$siguientePaso]['ruta'];
        $nombreSiguiente = $progreso['pasos'][$siguientePaso]['nombre'];

        return redirect($rutaSiguiente)
            ->with('warning', "Debes completar '{$nombreSiguiente}' antes de continuar.");
    }
}
