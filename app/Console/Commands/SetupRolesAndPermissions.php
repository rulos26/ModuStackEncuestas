<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupRolesAndPermissions extends Command
{
    protected $signature = 'roles:setup {--fresh : Recrear roles y permisos desde cero}';
    protected $description = 'Configura y verifica el sistema de roles y permisos';

    public function handle()
    {
        $this->info('=== CONFIGURACIÓN DEL SISTEMA DE ROLES Y PERMISOS ===');

        if ($this->option('fresh')) {
            $this->info('Limpiando roles y permisos existentes...');
            $this->clearRolesAndPermissions();
        }

        // Verificar conexión a base de datos
        $this->info('1. Verificando conexión a base de datos...');
        try {
            DB::connection()->getPdo();
            $this->info('✅ Conexión a base de datos exitosa');
        } catch (\Exception $e) {
            $this->error('❌ Error de conexión: ' . $e->getMessage());
            return 1;
        }

        // Verificar tablas de roles y permisos
        $this->info('2. Verificando tablas de roles y permisos...');
        $tables = ['roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions'];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $this->info("✅ Tabla '{$table}' existe");
            } else {
                $this->error("❌ Tabla '{$table}' NO existe");
                return 1;
            }
        }

        // Ejecutar seeders
        $this->info('3. Ejecutando seeders...');
        try {
            $this->call('db:seed', ['--class' => 'roleSeeder']);
            $this->info('✅ Seeder de roles ejecutado correctamente');
        } catch (\Exception $e) {
            $this->error('❌ Error ejecutando seeder de roles: ' . $e->getMessage());
            return 1;
        }

        try {
            $this->call('db:seed', ['--class' => 'UserSeeder']);
            $this->info('✅ Seeder de usuarios ejecutado correctamente');
        } catch (\Exception $e) {
            $this->error('❌ Error ejecutando seeder de usuarios: ' . $e->getMessage());
            return 1;
        }

        // Verificar roles creados
        $this->info('4. Verificando roles creados...');
        $roles = Role::all();
        foreach ($roles as $role) {
            $this->info("✅ Rol '{$role->name}' creado");
        }

        // Verificar permisos creados
        $this->info('5. Verificando permisos creados...');
        $permissions = Permission::all();
        $this->info("✅ {$permissions->count()} permisos creados");

        // Verificar usuarios con roles
        $this->info('6. Verificando usuarios con roles...');
        $users = User::with('roles')->get();
        foreach ($users as $user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            $rolesText = !empty($userRoles) ? implode(', ', $userRoles) : 'Sin roles';
            $this->info("✅ Usuario '{$user->email}' - Roles: {$rolesText}");
        }

        // Probar funcionalidad de roles
        $this->info('7. Probando funcionalidad de roles...');
        $testUser = User::with('roles')->first();
        if ($testUser) {
            try {
                $hasRoles = $testUser->roles->count() > 0;
                $this->info("✅ Usuario de prueba tiene roles: " . ($hasRoles ? 'Sí' : 'No'));

                if ($hasRoles) {
                    $firstRole = $testUser->roles->first()->name;
                    $this->info("✅ Primer rol del usuario: {$firstRole}");
                }
            } catch (\Exception $e) {
                $this->error('❌ Error probando funcionalidad de roles: ' . $e->getMessage());
            }
        }

        // Verificar permisos por rol
        $this->info('8. Verificando permisos por rol...');
        foreach ($roles as $role) {
            $permissions = $role->permissions->pluck('name')->toArray();
            $this->info("✅ Rol '{$role->name}' tiene " . count($permissions) . " permisos");
        }

        $this->info('=== CONFIGURACIÓN COMPLETADA ===');
        $this->info('El sistema de roles y permisos está listo para usar.');

        return 0;
    }

    /**
     * Limpiar roles y permisos existentes
     */
    private function clearRolesAndPermissions(): void
    {
        try {
            // Eliminar relaciones
            DB::table('model_has_roles')->delete();
            DB::table('model_has_permissions')->delete();
            DB::table('role_has_permissions')->delete();

            // Eliminar roles y permisos
            DB::table('roles')->delete();
            DB::table('permissions')->delete();

            $this->info('✅ Roles y permisos eliminados correctamente');
        } catch (\Exception $e) {
            $this->error('❌ Error eliminando roles y permisos: ' . $e->getMessage());
        }
    }
}
