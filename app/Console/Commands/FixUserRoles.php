<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class FixUserRoles extends Command
{
    protected $signature = 'users:fix-roles';
    protected $description = 'Verifica y arregla los roles de usuarios';

    public function handle()
    {
        $this->info('=== VERIFICACIÓN Y REPARACIÓN DE ROLES DE USUARIOS ===');

        // Verificar roles existentes
        $this->info('1. Verificando roles existentes...');
        $roles = Role::all();
        foreach ($roles as $role) {
            $this->info("✅ Rol encontrado: {$role->name}");
        }

        // Verificar usuarios sin roles
        $this->info('2. Verificando usuarios sin roles...');
        $usersWithoutRoles = User::doesntHave('roles')->get();

        if ($usersWithoutRoles->count() > 0) {
            $this->warn("⚠️  Encontrados {$usersWithoutRoles->count()} usuarios sin roles:");

            foreach ($usersWithoutRoles as $user) {
                $this->line("   - {$user->email}");
            }

            if ($this->confirm('¿Deseas asignar roles a estos usuarios?')) {
                $this->assignRolesToUsers($usersWithoutRoles);
            }
        } else {
            $this->info('✅ Todos los usuarios tienen roles asignados');
        }

        // Mostrar resumen de usuarios con roles
        $this->info('3. Resumen de usuarios con roles:');
        $users = User::with('roles')->get();

        foreach ($users as $user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            $rolesText = !empty($userRoles) ? implode(', ', $userRoles) : 'Sin roles';
            $this->info("   {$user->email} - Roles: {$rolesText}");
        }

        // Información de acceso
        $this->info('4. Información de acceso:');
        $this->info('   Superadmin: rulos26@gmail.com / 0382646740Ju*');
        $this->info('   Superadmin: rulos25@gmail.com / 12345678');
        $this->info('   Admin: rulos24@gmail.com / 12345678');
        $this->info('   Cliente: rulos23@gmail.com / 12345678');

        $this->info('=== VERIFICACIÓN COMPLETADA ===');
        return 0;
    }

    private function assignRolesToUsers($users)
    {
        $roles = Role::all()->pluck('name')->toArray();

        foreach ($users as $user) {
            $role = $this->choice(
                "¿Qué rol asignar a {$user->email}?",
                $roles,
                0
            );

            $user->assignRole($role);
            $this->info("✅ Rol '{$role}' asignado a {$user->email}");
        }
    }
}
