<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\TokenEncuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class EncuestaRenovarController extends Controller
{
    /**
     * Mostrar formulario para renovar enlace
     */
    public function mostrarFormularioRenovacion(Request $request)
    {
        $token = $request->query('token');
        $slug = $request->route('slug');

        if (!$token) {
            return view('encuestas.error', [
                'titulo' => 'Error',
                'mensaje' => 'Token de acceso requerido.'
            ]);
        }

        // Buscar encuesta por slug
        $encuesta = Encuesta::where('slug', $slug)
            ->where('habilitada', true)
            ->where('estado', 'publicada')
            ->first();

        if (!$encuesta) {
            return view('encuestas.error', [
                'titulo' => 'Error',
                'mensaje' => 'Encuesta no encontrada o no disponible.'
            ]);
        }

        // Buscar token
        $tokenEncuesta = $encuesta->obtenerToken($token);
        if (!$tokenEncuesta) {
            return view('encuestas.error', [
                'titulo' => 'Error',
                'mensaje' => 'Token de acceso no encontrado.'
            ]);
        }

        return view('encuestas.renovar', compact('encuesta', 'tokenEncuesta'));
    }

    /**
     * Renovar enlace vencido
     */
    public function renovarEnlace(Request $request, $slug)
    {
        try {
            $token = $request->input('token');
            $email = $request->input('email');

            // Validar datos
            $request->validate([
                'token' => 'required|string',
                'email' => 'required|email'
            ]);

            // Buscar encuesta
            $encuesta = Encuesta::where('slug', $slug)
                ->where('habilitada', true)
                ->where('estado', 'publicada')
                ->first();

            if (!$encuesta) {
                return redirect()->back()
                    ->with('error', 'Encuesta no encontrada o no disponible.');
            }

            // Buscar token
            $tokenEncuesta = $encuesta->obtenerToken($token);
            if (!$tokenEncuesta) {
                return redirect()->back()
                    ->with('error', 'Token de acceso no encontrado.');
            }

            // Verificar que el email coincida
            if ($tokenEncuesta->email_destinatario !== $email) {
                return redirect()->back()
                    ->with('error', 'El email no coincide con el token proporcionado.');
            }

            // Verificar que el token haya expirado
            if (!$tokenEncuesta->haExpirado()) {
                return redirect()->back()
                    ->with('warning', 'El enlace aún no ha expirado.');
            }

            // Generar nuevo token
            $nuevoToken = $encuesta->generarTokenParaDestinatario($email, 24);
            $nuevoEnlace = $nuevoToken->obtenerEnlace();

            Log::info('Enlace renovado', [
                'encuesta_id' => $encuesta->id,
                'email' => $email,
                'token_anterior' => $token,
                'token_nuevo' => $nuevoToken->token_acceso
            ]);

            return redirect()->back()
                ->with('success', 'Enlace renovado exitosamente.')
                ->with('nuevo_enlace', $nuevoEnlace);

        } catch (Exception $e) {
            Log::error('Error renovando enlace', [
                'error' => $e->getMessage(),
                'token' => $token ?? null,
                'email' => $email ?? null
            ]);

            return redirect()->back()
                ->with('error', 'Error al renovar el enlace. Inténtalo de nuevo.');
        }
    }

    /**
     * Verificar estado de un token
     */
    public function verificarToken(Request $request)
    {
        $token = $request->input('token');
        $slug = $request->input('slug');

        if (!$token || !$slug) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'Token y slug requeridos.'
            ]);
        }

        // Buscar encuesta
        $encuesta = Encuesta::where('slug', $slug)
            ->where('habilitada', true)
            ->where('estado', 'publicada')
            ->first();

        if (!$encuesta) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'Encuesta no encontrada.'
            ]);
        }

        // Buscar token
        $tokenEncuesta = $encuesta->obtenerToken($token);
        if (!$tokenEncuesta) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'Token no encontrado.'
            ]);
        }

        return response()->json([
            'valido' => $tokenEncuesta->esValido(),
            'usado' => $tokenEncuesta->usado,
            'expirado' => $tokenEncuesta->haExpirado(),
            'tiempo_restante' => $tokenEncuesta->tiempoRestante(),
            'email' => $tokenEncuesta->email_destinatario
        ]);
    }
}
