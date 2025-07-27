<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

trait HasRoleHelpers
{
    /**
     * Verificar si el usuario actual tiene un rol específico
     */
    public static function userHasRole(string $role): bool
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
    public static function userHasAnyRole(array $roles): bool
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
     * Verificar si el usuario actual tiene todos los roles especificados
     */
    public static function userHasAllRoles(array $roles): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasAllRoles($roles);
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
    public static function userHasPermission(string $permission): bool
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
     * Verificar si el usuario actual tiene al menos uno de los permisos especificados
     */
    public static function userHasAnyPermission(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        try {
            return $user->hasAnyPermission($permissions);
        } catch (\Exception $e) {
            Log::error('Error verificando permisos del usuario', [
                'user_id' => $user->id,
                'permissions' => $permissions,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtener todos los roles del usuario actual
     */
    public static function getUserRoles(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();

        try {
            return $user->roles->pluck('name')->toArray();
        } catch (\Exception $e) {
            Log::error('Error obteniendo roles del usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Obtener todos los permisos del usuario actual
     */
    public static function getUserPermissions(): array
    {
        if (!Auth::check()) {
            return [];
        }

        $user = Auth::user();

        try {
            return $user->getAllPermissions()->pluck('name')->toArray();
        } catch (\Exception $e) {
            Log::error('Error obteniendo permisos del usuario', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Verificar si el usuario es superadmin
     */
    public static function isSuperadmin(): bool
    {
        return self::userHasRole('Superadmin');
    }

    /**
     * Verificar si el usuario es admin
     */
    public static function isAdmin(): bool
    {
        return self::userHasAnyRole(['Superadmin', 'Admin']);
    }

    /**
     * Verificar si el usuario es cliente
     */
    public static function isCliente(): bool
    {
        return self::userHasRole('Cliente');
    }

    /**
     * Obtener el rol principal del usuario (el primero)
     */
    public static function getUserMainRole(): ?string
    {
        $roles = self::getUserRoles();
        return !empty($roles) ? $roles[0] : null;
    }
}
