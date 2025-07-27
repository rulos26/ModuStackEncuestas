<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'No autenticado'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar si el usuario tiene al menos uno de los roles requeridos
        $hasRole = false;
        $userRoles = [];

        try {
            $userRoles = $user->roles->pluck('name')->toArray();
            foreach ($roles as $role) {
                if (in_array($role, $userRoles)) {
                    $hasRole = true;
                    break;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error verificando roles del usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        if (!$hasRole) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Acceso denegado'], 403);
            }

            // Log del intento de acceso no autorizado
            Log::warning('Intento de acceso no autorizado', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_roles' => $roles,
                'user_roles' => $userRoles,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('home')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
