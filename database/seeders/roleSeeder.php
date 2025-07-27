<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class roleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles principales
        $superadmin = Role::create(['name' => 'Superadmin', 'guard_name' => 'web']);
        $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $cliente = Role::create(['name' => 'Cliente', 'guard_name' => 'web']);

        // Crear permisos básicos
        $this->createBasicPermissions($superadmin, $admin, $cliente);

        // Crear permisos de usuarios
        $this->createUserPermissions($superadmin, $admin);

        // Crear permisos de encuestas
        $this->createSurveyPermissions($superadmin, $admin, $cliente);

        // Crear permisos de empresa
        $this->createCompanyPermissions($superadmin, $admin, $cliente);

        // Crear permisos de empleados
        $this->createEmployeePermissions($superadmin, $admin, $cliente);

        // Crear permisos de configuración
        $this->createConfigurationPermissions($superadmin, $admin);

        // Crear permisos de logs
        $this->createLogPermissions($superadmin, $admin);

        // Crear permisos de menús
        $this->createMenuPermissions($superadmin, $admin, $cliente);

        // Crear permisos de gestión del sistema
        $this->createSystemManagementPermissions($superadmin, $admin);
    }

    private function createBasicPermissions($superadmin, $admin, $cliente): void
    {
        $permissions = [
            'home' => [$superadmin, $admin, $cliente],
            'profile' => [$superadmin, $admin, $cliente],
            'profile.update' => [$superadmin, $admin, $cliente],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    private function createUserPermissions($superadmin, $admin): void
    {
        $permissions = [
            'users.index' => [$superadmin, $admin],
            'users.create' => [$superadmin, $admin],
            'users.store' => [$superadmin, $admin],
            'users.show' => [$superadmin, $admin],
            'users.edit' => [$superadmin, $admin],
            'users.update' => [$superadmin, $admin],
            'users.destroy' => [$superadmin, $admin],
            'users.export' => [$superadmin, $admin],
            'users.roles.manage' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    private function createSurveyPermissions($superadmin, $admin, $cliente): void
    {
        $permissions = [
            'encuestas.index' => [$superadmin, $admin, $cliente],
            'encuestas.create' => [$superadmin, $admin, $cliente],
            'encuestas.show' => [$superadmin, $admin, $cliente],
            'encuestas.edit' => [$superadmin, $admin, $cliente],
            'encuestas.destroy' => [$superadmin, $admin],
            'encuestas.clone' => [$superadmin, $admin, $cliente],
            'encuestas.publish' => [$superadmin, $admin, $cliente],
            'preguntas.create' => [$superadmin, $admin, $cliente],
            'preguntas.store' => [$superadmin, $admin, $cliente],
            'preguntas.destroy' => [$superadmin, $admin, $cliente],
            'respuestas.create' => [$superadmin, $admin, $cliente],
            'respuestas.store' => [$superadmin, $admin, $cliente],
            'logica.create' => [$superadmin, $admin, $cliente],
            'logica.store' => [$superadmin, $admin, $cliente],
            'encuestas.preview' => [$superadmin, $admin, $cliente],
            'encuestas.reports' => [$superadmin, $admin],
            'encuestas.export' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    private function createCompanyPermissions($superadmin, $admin, $cliente): void
    {
        $permissions = [
            'empresa.show' => [$superadmin, $admin, $cliente],
            'empresa.create' => [$superadmin, $admin],
            'empresa.store' => [$superadmin, $admin],
            'empresa.edit' => [$superadmin, $admin],
            'empresa.update' => [$superadmin, $admin],
            'empresa.export.pdf' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    private function createEmployeePermissions($superadmin, $admin, $cliente): void
    {
        $permissions = [
            'empleados.index' => [$superadmin, $admin, $cliente],
            'empleados.create' => [$superadmin, $admin],
            'empleados.store' => [$superadmin, $admin],
            'empleados.show' => [$superadmin, $admin, $cliente],
            'empleados.edit' => [$superadmin, $admin],
            'empleados.update' => [$superadmin, $admin],
            'empleados.destroy' => [$superadmin, $admin],
            'empleados.import' => [$superadmin, $admin],
            'empleados.export' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    private function createConfigurationPermissions($superadmin, $admin): void
    {
        $permissions = [
            'settings.images' => [$superadmin, $admin],
            'settings.images.update' => [$superadmin, $admin],
            'settings.images.manual' => [$superadmin, $admin],
            'system.optimizer.index' => [$superadmin, $admin],
            'system.optimizer.clear-caches' => [$superadmin, $admin],
            'system.optimizer.dump-autoload' => [$superadmin, $admin],
            'system.optimizer.optimize-routes' => [$superadmin, $admin],
            'system.optimizer.clear-temp-files' => [$superadmin, $admin],
            'system.optimizer.optimize-all' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    private function createLogPermissions($superadmin, $admin): void
    {
        $permissions = [
            'logs.index' => [$superadmin, $admin],
            'logs.module' => [$superadmin, $admin],
            'logs.module.user' => [$superadmin, $admin],
            'logs.module.role' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    private function createMenuPermissions($superadmin, $admin, $cliente): void
    {
        $permissions = [
            'menu.dashboard' => [$superadmin, $admin, $cliente],
            'menu.encuestas' => [$superadmin, $admin, $cliente],
            'menu.empleados' => [$superadmin, $admin, $cliente],
            'menu.empresa' => [$superadmin, $admin, $cliente],
            'menu.users' => [$superadmin, $admin],
            'menu.logs' => [$superadmin, $admin],
            'menu.settings' => [$superadmin, $admin],
            'menu.system' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    private function createSystemManagementPermissions($superadmin, $admin): void
    {
        $permissions = [
            'system.manage' => [$superadmin, $admin],
            'system.user-roles' => [$superadmin, $admin],
            'system.assign-role' => [$superadmin, $admin],
            'system.assign-default-roles' => [$superadmin, $admin],
            'system.companies' => [$superadmin, $admin],
            'system.create-test-company' => [$superadmin, $admin],
            'system.setup-roles' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }
}
