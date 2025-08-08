<?php

namespace App\Http\Middleware;

use App\Models\Encuesta;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerificarTokenEncuesta
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
        $token = $request->query('token');
        $slug = $request->route('slug');

        if (!$token) {
            return $this->responderError('Token de acceso requerido.');
        }

        // Buscar encuesta por slug
        $encuesta = Encuesta::where('slug', $slug)
            ->where('habilitada', true)
            ->where('estado', 'publicada')
            ->first();

        if (!$encuesta) {
            return $this->responderError('Encuesta no encontrada o no disponible.');
        }

        // Verificar si la encuesta está disponible
        if (!$encuesta->estaDisponible()) {
            return $this->responderError('Esta encuesta no está disponible en este momento.');
        }

        // Verificar token
        if (!$encuesta->tokenValido($token)) {
            if ($encuesta->enlaceVencido($token)) {
                return $this->responderError('El enlace ha expirado. Contacta al administrador para obtener un nuevo enlace.');
            } else {
                return $this->responderError('Token de acceso inválido.');
            }
        }

        // Obtener el token específico
        $tokenEncuesta = $encuesta->obtenerToken($token);
        if (!$tokenEncuesta) {
            return $this->responderError('Token de acceso no encontrado.');
        }

        // Solo marcar como usado si no es un token general (para envío masivo)
        // Los tokens generales (general@encuesta.com) no se marcan como usados
        if ($tokenEncuesta->email_destinatario !== 'general@encuesta.com') {
            $tokenEncuesta->marcarUsado($request->ip(), $request->userAgent());
        }

        // Agregar encuesta al request para uso posterior
        $request->attributes->add(['encuesta' => $encuesta]);

        Log::info('Acceso a encuesta pública', [
            'encuesta_id' => $encuesta->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return $next($request);
    }

    /**
     * Responder con error
     */
    private function responderError(string $mensaje)
    {
        return view('encuestas.publica', [
            'encuesta' => null,
            'error' => $mensaje
        ]);
    }
}
