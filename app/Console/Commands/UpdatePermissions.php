<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdatePermissions extends Command
{
    protected $signature = 'permissions:update';
    protected $description = 'Actualiza los permisos del sistema sin recrear roles';

    public function handle()
    {
        $this->info('=== ACTUALIZANDO PERMISOS DEL SISTEMA ===');

        try {
            // Obtener roles existentes
            $superadmin = Role::where('name', 'Superadmin')->first();
            $admin = Role::where('name', 'Admin')->first();
            $cliente = Role::where('name', 'Cliente')->first();

            if (!$superadmin || !$admin || !$cliente) {
                $this->error('❌ Los roles principales no existen. Ejecuta primero: php artisan roles:setup --fresh');
                return 1;
            }

            $this->info('✅ Roles encontrados correctamente');

            // Crear permisos del sistema de gestión
            $this->createSystemManagementPermissions($superadmin, $admin);

            $this->info('✅ Permisos actualizados correctamente');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error actualizando permisos: ' . $e->getMessage());
            return 1;
        }
    }

    private function createSystemManagementPermissions($superadmin, $admin): void
    {
        $this->info('Creando permisos de gestión del sistema...');

        $permissions = [
            'system.manage' => [$superadmin, $admin],
            'system.user-roles' => [$superadmin, $admin],
            'system.assign-role' => [$superadmin, $admin],
            'system.assign-default-roles' => [$superadmin, $admin],
            'system.companies' => [$superadmin, $admin],
            'system.create-test-company' => [$superadmin, $admin],
            'system.setup-roles' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permissionName => $roles) {
            // Verificar si el permiso ya existe
            $permission = Permission::where('name', $permissionName)->first();

            if (!$permission) {
                $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);
                $this->info("✅ Permiso creado: {$permissionName}");
            } else {
                $this->info("ℹ️  Permiso ya existe: {$permissionName}");
            }

            // Sincronizar roles
            $permission->syncRoles($roles);
        }
    }
}
