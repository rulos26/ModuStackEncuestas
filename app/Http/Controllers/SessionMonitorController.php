<?php

namespace App\Http\Controllers;

use App\Models\UserSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SessionMonitorController extends Controller
{
    /**
     * Mostrar el dashboard de monitoreo de sesiones.
     */
    public function index(Request $request)
    {
        // Obtener filtros
        $roleFilter = $request->get('role');
        $statusFilter = $request->get('status', 'active');
        $routeFilter = $request->get('route');

        // Construir consulta base
        $query = UserSession::with(['user.roles'])
            ->select('user_sessions.*')
            ->join('users', 'user_sessions.user_id', '=', 'users.id');

        // Aplicar filtros
        if ($roleFilter) {
            $query->whereHas('user.roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        if ($statusFilter === 'active') {
            $query->where('user_sessions.is_active', true);
        } elseif ($statusFilter === 'inactive') {
            $query->where('user_sessions.is_active', false);
        }

        if ($routeFilter) {
            $query->where('user_sessions.current_route', 'like', "%{$routeFilter}%");
        }

        // Obtener sesiones ordenadas por última actividad
        $sessions = $query->orderBy('user_sessions.last_activity', 'desc')
            ->paginate(20);

        // Estadísticas generales
        $stats = $this->getSessionStats();

        // Obtener roles disponibles para filtro
        $roles = DB::table('roles')->pluck('name', 'name');

        // Obtener rutas únicas para filtro
        $routes = UserSession::whereNotNull('current_route')
            ->distinct()
            ->pluck('current_route');

        return view('session_monitor.index', compact(
            'sessions',
            'stats',
            'roles',
            'routes',
            'roleFilter',
            'statusFilter',
            'routeFilter'
        ));
    }

    /**
     * Obtener estadísticas de sesiones.
     */
    private function getSessionStats(): array
    {
        $now = Carbon::now();
        $timeoutMinutes = config('session.lifetime', 120);

        return [
            'total_active' => UserSession::where('is_active', true)->count(),
            'total_inactive' => UserSession::where('is_active', false)->count(),
            'total_today' => UserSession::whereDate('login_time', $now->toDateString())->count(),
            'total_this_week' => UserSession::whereBetween('login_time', [
                $now->startOfWeek(),
                $now->endOfWeek()
            ])->count(),
            'sessions_expired' => UserSession::where('is_active', true)
                ->where('last_activity', '<', $now->subMinutes($timeoutMinutes))
                ->count(),
            'unique_users_today' => UserSession::whereDate('login_time', $now->toDateString())
                ->distinct('user_id')
                ->count(),
        ];
    }

    /**
     * Cerrar una sesión específica.
     */
    public function closeSession(Request $request, $sessionId)
    {
        $session = UserSession::findOrFail($sessionId);

        // Verificar permisos (solo admin puede cerrar sesiones)
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para cerrar sesiones.'
            ], 403);
        }

        $session->markAsInactive();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    /**
     * Cerrar todas las sesiones de un usuario.
     */
    public function closeAllUserSessions(Request $request, $userId)
    {
        // Verificar permisos
        if (!Auth::user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para cerrar sesiones.'
            ], 403);
        }

        UserSession::where('user_id', $userId)
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'logout_time' => Carbon::now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Todas las sesiones del usuario han sido cerradas.'
        ]);
    }

    /**
     * Cerrar sesiones expiradas.
     */
    public function closeExpiredSessions()
    {
        $timeoutMinutes = config('session.lifetime', 120);
        $expiredSessions = UserSession::where('is_active', true)
            ->where('last_activity', '<', Carbon::now()->subMinutes($timeoutMinutes))
            ->get();

        foreach ($expiredSessions as $session) {
            $session->markAsInactive();
        }

        return response()->json([
            'success' => true,
            'message' => "Se cerraron {$expiredSessions->count()} sesiones expiradas."
        ]);
    }

    /**
     * Obtener datos para actualización en tiempo real (AJAX).
     */
    public function getActiveSessions()
    {
        $sessions = UserSession::with(['user.roles'])
            ->where('is_active', true)
            ->orderBy('last_activity', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'user_name' => $session->user->name,
                    'user_email' => $session->user->email,
                    'role' => $session->user->roles->first()?->name ?? 'Sin rol',
                    'ip_address' => $session->ip_address,
                    'browser_info' => $session->browser_info,
                    'current_route' => $session->current_route,
                    'current_page' => $session->current_page,
                    'last_activity' => $session->last_activity->format('H:i:s'),
                    'inactivity_time' => $session->inactivity_time,
                    'session_duration' => $session->session_duration,
                    'login_time' => $session->login_time->format('d/m/Y H:i:s'),
                ];
            });

        return response()->json($sessions);
    }

    /**
     * Mostrar historial de sesiones.
     */
    public function history(Request $request)
    {
        $query = UserSession::with(['user.roles'])
            ->where('is_active', false);

        // Filtros de fecha
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($startDate) {
            $query->whereDate('login_time', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('login_time', '<=', $endDate);
        }

        $sessions = $query->orderBy('login_time', 'desc')
            ->paginate(50);

        return view('session_monitor.history', compact('sessions', 'startDate', 'endDate'));
    }

    /**
     * Exportar datos de sesiones.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        $type = $request->get('type', 'active'); // active, inactive, all

        $query = UserSession::with(['user.roles']);

        if ($type === 'active') {
            $query->where('is_active', true);
        } elseif ($type === 'inactive') {
            $query->where('is_active', false);
        }

        $sessions = $query->orderBy('last_activity', 'desc')->get();

        if ($format === 'csv') {
            return $this->exportToCsv($sessions);
        } else {
            return $this->exportToJson($sessions);
        }
    }

    /**
     * Exportar a CSV.
     */
    private function exportToCsv($sessions)
    {
        $filename = 'sesiones_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($sessions) {
            $file = fopen('php://output', 'w');

            // Encabezados
            fputcsv($file, [
                'ID', 'Usuario', 'Email', 'Rol', 'IP', 'Navegador', 'Sistema Operativo',
                'Ruta Actual', 'Página Actual', 'Última Actividad', 'Tiempo Inactividad',
                'Hora Login', 'Hora Logout', 'Duración Sesión', 'Estado'
            ]);

            // Datos
            foreach ($sessions as $session) {
                $browserInfo = $session->browser_info;
                fputcsv($file, [
                    $session->id,
                    $session->user->name,
                    $session->user->email,
                    $session->user->roles->first()?->name ?? 'Sin rol',
                    $session->ip_address,
                    $browserInfo['browser'],
                    $browserInfo['os'],
                    $session->current_route,
                    $session->current_page,
                    $session->last_activity->format('d/m/Y H:i:s'),
                    $session->inactivity_time,
                    $session->login_time->format('d/m/Y H:i:s'),
                    $session->logout_time?->format('d/m/Y H:i:s') ?? '',
                    $session->session_duration,
                    $session->is_active ? 'Activa' : 'Inactiva'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exportar a JSON.
     */
    private function exportToJson($sessions)
    {
        $data = $sessions->map(function ($session) {
            return [
                'id' => $session->id,
                'user' => [
                    'name' => $session->user->name,
                    'email' => $session->user->email,
                    'role' => $session->user->roles->first()?->name ?? 'Sin rol'
                ],
                'session' => [
                    'ip_address' => $session->ip_address,
                    'browser' => $session->browser_info['browser'],
                    'os' => $session->browser_info['os'],
                    'current_route' => $session->current_route,
                    'current_page' => $session->current_page,
                    'last_activity' => $session->last_activity->toISOString(),
                    'login_time' => $session->login_time->toISOString(),
                    'logout_time' => $session->logout_time?->toISOString(),
                    'duration' => $session->session_duration,
                    'is_active' => $session->is_active
                ]
            ];
        });

        $filename = 'sesiones_' . date('Y-m-d_H-i-s') . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
}
