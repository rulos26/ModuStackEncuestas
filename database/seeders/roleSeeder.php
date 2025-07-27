<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class roleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles principales
        $superadmin = Role::create(['name' => 'Superadmin', 'guard_name' => 'web']);
        $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $cliente = Role::create(['name' => 'Cliente', 'guard_name' => 'web']);

        // Permisos básicos del sistema
        $this->createBasicPermissions($superadmin, $admin, $cliente);

        // Permisos de usuarios
        $this->createUserPermissions($superadmin, $admin);

        // Permisos de encuestas
        $this->createSurveyPermissions($superadmin, $admin, $cliente);

        // Permisos de empresa
        $this->createCompanyPermissions($superadmin, $admin);

        // Permisos de empleados
        $this->createEmployeePermissions($superadmin, $admin);

        // Permisos de configuración
        $this->createConfigurationPermissions($superadmin, $admin);

        // Permisos de logs y monitoreo
        $this->createLogPermissions($superadmin, $admin);

        // Permisos de menús
        $this->createMenuPermissions($superadmin, $admin, $cliente);
    }

    /**
     * Crear permisos básicos del sistema
     */
    private function createBasicPermissions($superadmin, $admin, $cliente): void
    {
        $permissions = [
            'home' => [$superadmin, $admin, $cliente],
            'dashboard' => [$superadmin, $admin, $cliente],
            'profile.view' => [$superadmin, $admin, $cliente],
            'profile.edit' => [$superadmin, $admin, $cliente],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    /**
     * Crear permisos de usuarios
     */
    private function createUserPermissions($superadmin, $admin): void
    {
        $permissions = [
            'users.index' => [$superadmin, $admin],
            'users.create' => [$superadmin],
            'users.show' => [$superadmin, $admin],
            'users.edit' => [$superadmin, $admin],
            'users.destroy' => [$superadmin],
            'users.export' => [$superadmin, $admin],
            'users.roles.manage' => [$superadmin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    /**
     * Crear permisos de encuestas
     */
    private function createSurveyPermissions($superadmin, $admin, $cliente): void
    {
        $permissions = [
            // Gestión de encuestas
            'encuestas.index' => [$superadmin, $admin, $cliente],
            'encuestas.create' => [$superadmin, $admin, $cliente],
            'encuestas.show' => [$superadmin, $admin, $cliente],
            'encuestas.edit' => [$superadmin, $admin, $cliente],
            'encuestas.destroy' => [$superadmin, $admin],
            'encuestas.clone' => [$superadmin, $admin, $cliente],
            'encuestas.publish' => [$superadmin, $admin, $cliente],

            // Gestión de preguntas
            'preguntas.create' => [$superadmin, $admin, $cliente],
            'preguntas.store' => [$superadmin, $admin, $cliente],
            'preguntas.destroy' => [$superadmin, $admin, $cliente],

            // Gestión de respuestas
            'respuestas.create' => [$superadmin, $admin, $cliente],
            'respuestas.store' => [$superadmin, $admin, $cliente],

            // Configuración de lógica
            'logica.create' => [$superadmin, $admin, $cliente],
            'logica.store' => [$superadmin, $admin, $cliente],

            // Vista previa
            'encuestas.preview' => [$superadmin, $admin, $cliente],

            // Respuestas públicas
            'encuestas.publica' => [], // Acceso público sin autenticación
            'encuestas.responder' => [], // Acceso público sin autenticación

            // Reportes de encuestas
            'encuestas.reports' => [$superadmin, $admin],
            'encuestas.export' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    /**
     * Crear permisos de empresa
     */
    private function createCompanyPermissions($superadmin, $admin): void
    {
        $permissions = [
            'empresa.show' => [$superadmin, $admin],
            'empresa.create' => [$superadmin, $admin],
            'empresa.store' => [$superadmin, $admin],
            'empresa.edit' => [$superadmin, $admin],
            'empresa.update' => [$superadmin, $admin],
            'empresa.export.pdf' => [$superadmin, $admin],

            // Ubicación geográfica
            'paises.index' => [$superadmin, $admin],
            'paises.create' => [$superadmin],
            'paises.edit' => [$superadmin, $admin],
            'paises.destroy' => [$superadmin],

            'departamentos.index' => [$superadmin, $admin],
            'departamentos.create' => [$superadmin],
            'departamentos.edit' => [$superadmin, $admin],
            'departamentos.destroy' => [$superadmin],

            'municipios.index' => [$superadmin, $admin],
            'municipios.create' => [$superadmin],
            'municipios.edit' => [$superadmin, $admin],
            'municipios.destroy' => [$superadmin],

            // Empresas clientes
            'empresas_clientes.index' => [$superadmin, $admin],
            'empresas_clientes.create' => [$superadmin, $admin],
            'empresas_clientes.show' => [$superadmin, $admin],
            'empresas_clientes.edit' => [$superadmin, $admin],
            'empresas_clientes.destroy' => [$superadmin],
            'empresas_clientes.export.pdf' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    /**
     * Crear permisos de empleados
     */
    private function createEmployeePermissions($superadmin, $admin): void
    {
        $permissions = [
            'empleados.index' => [$superadmin, $admin],
            'empleados.create' => [$superadmin, $admin],
            'empleados.show' => [$superadmin, $admin],
            'empleados.edit' => [$superadmin, $admin],
            'empleados.destroy' => [$superadmin],
            'empleados.import' => [$superadmin, $admin],
            'empleados.export' => [$superadmin, $admin],
            'empleados.plantillas' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    /**
     * Crear permisos de configuración
     */
    private function createConfigurationPermissions($superadmin, $admin): void
    {
        $permissions = [
            'settings.images' => [$superadmin, $admin],
            'settings.images.update' => [$superadmin, $admin],
            'settings.images.manual' => [$superadmin, $admin],

            'politicas_privacidad.index' => [$superadmin, $admin],
            'politicas_privacidad.create' => [$superadmin],
            'politicas_privacidad.show' => [$superadmin, $admin],
            'politicas_privacidad.edit' => [$superadmin],
            'politicas_privacidad.destroy' => [$superadmin],

            'system.optimizer.index' => [$superadmin],
            'system.optimizer.clear-caches' => [$superadmin],
            'system.optimizer.dump-autoload' => [$superadmin],
            'system.optimizer.optimize-routes' => [$superadmin],
            'system.optimizer.clear-temp-files' => [$superadmin],
            'system.optimizer.optimize-all' => [$superadmin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    /**
     * Crear permisos de logs y monitoreo
     */
    private function createLogPermissions($superadmin, $admin): void
    {
        $permissions = [
            'logs.index' => [$superadmin, $admin],
            'logs.module' => [$superadmin, $admin],
            'logs.module.user' => [$superadmin, $admin],
            'logs.module.role' => [$superadmin, $admin],

            'session.monitor.index' => [$superadmin, $admin],
            'session.monitor.history' => [$superadmin, $admin],
            'session.monitor.active' => [$superadmin, $admin],
            'session.monitor.close' => [$superadmin, $admin],
            'session.monitor.close-user' => [$superadmin, $admin],
            'session.monitor.close-expired' => [$superadmin, $admin],
            'session.monitor.export' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }

    /**
     * Crear permisos de menús
     */
    private function createMenuPermissions($superadmin, $admin, $cliente): void
    {
        $permissions = [
            // Menús principales
            'menu.dashboard' => [$superadmin, $admin, $cliente],
            'menu.encuestas' => [$superadmin, $admin, $cliente],
            'menu.empresa' => [$superadmin, $admin],
            'menu.empleados' => [$superadmin, $admin],
            'menu.users' => [$superadmin, $admin],
            'menu.settings' => [$superadmin, $admin],
            'menu.logs' => [$superadmin, $admin],
            'menu.system' => [$superadmin],

            // Menús específicos
            'menu.ayuda' => [$superadmin, $admin, $cliente],
            'menu.testing' => [$superadmin, $admin],
            'menu.correos' => [$superadmin, $admin],
        ];

        foreach ($permissions as $permission => $roles) {
            Permission::create(['name' => $permission, 'guard_name' => 'web'])->syncRoles($roles);
        }
    }
}
