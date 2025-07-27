<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleAwareController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Verificar si el usuario actual tiene un rol específico
     */
    protected function userHasRole(string $role): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasRole($role);
        } catch (\Exception $e) {
            Log::error('Error verificando rol del usuario', [
                'user_id' => $user->id,
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar si el usuario actual tiene al menos uno de los roles especificados
     */
    protected function userHasAnyRole(array $roles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasAnyRole($roles);
        } catch (\Exception $e) {
            Log::error('Error verificando roles del usuario', [
                'user_id' => $user->id,
                'roles' => $roles,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar si el usuario actual tiene un permiso específico
     */
    protected function userHasPermission(string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasPermissionTo($permission);
        } catch (\Exception $e) {
            Log::error('Error verificando permiso del usuario', [
                'user_id' => $user->id,
                'permission' => $permission,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar si el usuario es superadmin
     */
    protected function isSuperadmin(): bool
    {
        return $this->userHasRole('Superadmin');
    }

    /**
     * Verificar si el usuario es admin
     */
    protected function isAdmin(): bool
    {
        return $this->userHasAnyRole(['Superadmin', 'Admin']);
    }

    /**
     * Verificar si el usuario es cliente
     */
    protected function isCliente(): bool
    {
        return $this->userHasRole('Cliente');
    }

    /**
     * Obtener el rol principal del usuario
     */
    protected function getUserMainRole(): ?string
    {
        if (!Auth::check()) {
            return null;
        }

        $user = Auth::user();

        try {
            $roles = $user->roles->pluck('name')->toArray();
            return !empty($roles) ? $roles[0] : null;
        } catch (\Exception $e) {
            Log::error('Error obteniendo rol principal del usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Verificar acceso basado en roles
     */
    protected function checkRoleAccess(array $allowedRoles): bool
    {
        return $this->userHasAnyRole($allowedRoles);
    }

    /**
     * Verificar acceso basado en permisos
     */
    protected function checkPermissionAccess(array $allowedPermissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasAnyPermission($allowedPermissions);
        } catch (\Exception $e) {
            Log::error('Error verificando permisos del usuario', [
                'user_id' => $user->id,
                'permissions' => $allowedPermissions,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Redirigir si no tiene acceso
     */
    protected function redirectIfNoAccess(string $message = 'No tienes permisos para acceder a esta sección.'): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('home')->with('error', $message);
    }

    /**
     * Log de acceso denegado
     */
    protected function logAccessDenied(string $action, array $requiredRoles = [], array $requiredPermissions = []): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        Log::warning('Acceso denegado', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => $action,
            'required_roles' => $requiredRoles,
            'required_permissions' => $requiredPermissions,
            'user_roles' => $this->getUserMainRole(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
        ]);
    }
}
