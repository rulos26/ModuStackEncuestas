<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignDefaultRoles extends Command
{
    protected $signature = 'users:assign-default-roles';
    protected $description = 'Asigna roles por defecto a los usuarios';

    public function handle()
    {
        $this->info('=== ASIGNACIÓN DE ROLES POR DEFECTO ===');

        // Mapeo de usuarios específicos con sus roles
        $userRoleMapping = [
            'rulos26@gmail.com' => 'Superadmin',
            'rulos25@gmail.com' => 'Superadmin',
            'rulos24@gmail.com' => 'Admin',
            'rulos23@gmail.com' => 'Cliente',
        ];

        // Asignar roles a usuarios específicos
        foreach ($userRoleMapping as $email => $roleName) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->syncRoles([$roleName]);
                    $this->info("✅ Rol '{$roleName}' asignado a {$email}");
                } else {
                    $this->error("❌ Rol '{$roleName}' no encontrado");
                }
            } else {
                $this->warn("⚠️  Usuario {$email} no encontrado");
            }
        }

        // Asignar roles aleatorios a usuarios restantes
        $remainingUsers = User::doesntHave('roles')->get();
        $roles = Role::all()->pluck('name')->toArray();

        if ($remainingUsers->count() > 0) {
            $this->info("Asignando roles aleatorios a {$remainingUsers->count()} usuarios restantes...");

            foreach ($remainingUsers as $user) {
                $randomRole = $roles[array_rand($roles)];
                $user->assignRole($randomRole);
                $this->info("✅ Rol '{$randomRole}' asignado a {$user->email}");
            }
        }

        // Verificar resultado
        $this->info('=== VERIFICACIÓN FINAL ===');
        $users = User::with('roles')->get();

        foreach ($users as $user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            $rolesText = !empty($userRoles) ? implode(', ', $userRoles) : 'Sin roles';
            $this->info("   {$user->email} - Roles: {$rolesText}");
        }

        $this->info('=== ASIGNACIÓN COMPLETADA ===');
        return 0;
    }
}
