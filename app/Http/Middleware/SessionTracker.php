<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\UserSession;
use Carbon\Carbon;

class SessionTracker
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Solo rastrear si el usuario está autenticado
        if (Auth::check()) {
            $this->trackUserSession($request);
        }

        return $response;
    }

    /**
     * Rastrear la sesión del usuario.
     */
    private function trackUserSession(Request $request): void
    {
        $user = Auth::user();
        $sessionId = Session::getId();
        $currentRoute = $request->route() ? $request->route()->getName() : $request->path();
        $currentPage = $this->getPageName($currentRoute);

        // Buscar sesión existente o crear nueva
        $session = UserSession::where('session_id', $sessionId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($session) {
            // Actualizar sesión existente
            $session->updateActivity($currentRoute, $currentPage);
        } else {
            // Crear nueva sesión
            UserSession::create([
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'current_route' => $currentRoute,
                'current_page' => $currentPage,
                'last_activity' => Carbon::now(),
                'is_active' => true,
                'login_time' => Carbon::now(),
                'additional_data' => [
                    'referer' => $request->header('referer'),
                    'method' => $request->method(),
                ]
            ]);
        }
    }

    /**
     * Obtener nombre legible de la página actual.
     */
    private function getPageName(string $route): string
    {
        $pageNames = [
            'home' => 'Dashboard',
            'users.index' => 'Lista de Usuarios',
            'users.create' => 'Crear Usuario',
            'users.edit' => 'Editar Usuario',
            'users.show' => 'Ver Usuario',
            'roles.index' => 'Lista de Roles',
            'roles.create' => 'Crear Rol',
            'roles.edit' => 'Editar Rol',
            'empleados.index' => 'Lista de Empleados',
            'empleados.create' => 'Crear Empleado',
            'empleados.edit' => 'Editar Empleado',
            'empleados.show' => 'Ver Empleado',
            'empleados.import' => 'Importar Empleados',
            'logs.index' => 'Logs del Sistema',
            'logs.module.user' => 'Errores de Usuario',
            'logs.module.role' => 'Errores de Roles',
            'test.index' => 'Pruebas Internas',
            'admin.correos.index' => 'Panel de Correos',
            'system.optimizer.index' => 'Optimización del Sistema',
            'settings.images' => 'Recursos Visuales',
            'settings.images.manual' => 'Guía de Recursos Visuales',
            'ayuda.usuarios_roles' => 'Manual de Usuarios y Permisos',
        ];

        return $pageNames[$route] ?? ucfirst(str_replace(['.', '-', '_'], ' ', $route));
    }
}
