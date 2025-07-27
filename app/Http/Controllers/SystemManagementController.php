<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SystemManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Panel principal de gestión del sistema
     */
    public function index()
    {
        // Verificar permisos de superadmin
        if (!$this->checkUserAccess(['system.manage'])) {
            return $this->redirectIfNoAccess('Solo los superadministradores pueden acceder a esta sección.');
        }

        $stats = $this->getSystemStats();

        return view('system.management.index', compact('stats'));
    }

    /**
     * Gestión de roles de usuarios
     */
    public function userRoles()
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.roles.manage'])) {
            return $this->redirectIfNoAccess('No tienes permisos para gestionar roles de usuarios.');
        }

        $users = User::with('roles')->orderBy('name')->get();
        $roles = Role::all();

        return view('system.management.user-roles', compact('users', 'roles'));
    }

    /**
     * Asignar rol a usuario
     */
    public function assignRole(Request $request)
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.roles.manage'])) {
            return $this->redirectIfNoAccess('No tienes permisos para gestionar roles de usuarios.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        try {
            $user = User::findOrFail($request->user_id);
            $role = Role::findOrFail($request->role_id);

            $user->syncRoles([$role->name]);

            Log::info('Rol asignado manualmente', [
                'assigned_by' => Auth::id(),
                'user_id' => $user->id,
                'role' => $role->name
            ]);

            return redirect()->back()->with('success', "Rol '{$role->name}' asignado correctamente a {$user->name}");
        } catch (\Exception $e) {
            Log::error('Error asignando rol', [
                'user_id' => $request->user_id,
                'role_id' => $request->role_id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al asignar el rol: ' . $e->getMessage());
        }
    }

    /**
     * Asignar roles por defecto a todos los usuarios
     */
    public function assignDefaultRoles()
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.roles.manage'])) {
            return $this->redirectIfNoAccess('No tienes permisos para gestionar roles de usuarios.');
        }

        try {
            // Mapeo de usuarios específicos con sus roles
            $userRoleMapping = [
                'rulos26@gmail.com' => 'Superadmin',
                'rulos25@gmail.com' => 'Superadmin',
                'rulos24@gmail.com' => 'Admin',
                'rulos23@gmail.com' => 'Cliente',
            ];

            $assignedCount = 0;

            // Asignar roles a usuarios específicos
            foreach ($userRoleMapping as $email => $roleName) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $role = Role::where('name', $roleName)->first();
                    if ($role) {
                        $user->syncRoles([$roleName]);
                        $assignedCount++;
                    }
                }
            }

            // Asignar roles aleatorios a usuarios restantes
            $remainingUsers = User::doesntHave('roles')->get();
            $roles = Role::all()->pluck('name')->toArray();

            foreach ($remainingUsers as $user) {
                $randomRole = $roles[array_rand($roles)];
                $user->assignRole($randomRole);
                $assignedCount++;
            }

            Log::info('Roles por defecto asignados masivamente', [
                'assigned_by' => Auth::id(),
                'total_assigned' => $assignedCount
            ]);

            return redirect()->back()->with('success', "Se asignaron roles a {$assignedCount} usuarios correctamente");
        } catch (\Exception $e) {
            Log::error('Error asignando roles por defecto', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al asignar roles por defecto: ' . $e->getMessage());
        }
    }

    /**
     * Gestión de empresas
     */
    public function companies()
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['empresa.show'])) {
            return $this->redirectIfNoAccess('No tienes permisos para ver empresas.');
        }

        $companies = Empresa::with(['pais', 'departamento', 'municipio'])->get();

        return view('system.management.companies', compact('companies'));
    }

    /**
     * Crear empresa de prueba
     */
    public function createTestCompany()
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['empresa.create'])) {
            return $this->redirectIfNoAccess('No tienes permisos para crear empresas.');
        }

        try {
            // Verificar si ya existe una empresa
            $existingCompany = Empresa::first();
            if ($existingCompany) {
                return redirect()->back()->with('info', "Ya existe una empresa: {$existingCompany->nombre_legal}");
            }

            // Crear país de prueba si no existe
            $pais = Pais::firstOrCreate(
                ['name' => 'Colombia'],
                ['alfa2' => 'CO', 'created_at' => now(), 'updated_at' => now()]
            );

            // Crear departamento de prueba si no existe
            $departamento = Departamento::firstOrCreate(
                ['nombre' => 'CUNDINAMARCA', 'pais_id' => $pais->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Crear municipio de prueba si no existe
            $municipio = Municipio::firstOrCreate(
                ['nombre' => 'BOGOTÁ', 'departamento_id' => $departamento->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            // Crear empresa de prueba
            $empresa = new Empresa();
            $empresa->nombre_legal = 'Empresa de Prueba S.A.S';
            $empresa->nit = '900123456-7';
            $empresa->representante_legal = 'Juan Pérez';
            $empresa->telefono = '3001234567';
            $empresa->email = 'contacto@empresaprueba.com';
            $empresa->direccion = 'Calle 123 #45-67, Bogotá';
            $empresa->mision = 'Proporcionar servicios de calidad para satisfacer las necesidades de nuestros clientes';
            $empresa->vision = 'Ser líder en el mercado de servicios empresariales';
            $empresa->descripcion = 'Empresa dedicada a la prestación de servicios empresariales y consultoría';
            $empresa->fecha_creacion = now();
            $empresa->pais_id = $pais->id;
            $empresa->departamento_id = $departamento->id;
            $empresa->municipio_id = $municipio->id;
            $empresa->save();

            Log::info('Empresa de prueba creada', [
                'created_by' => Auth::id(),
                'empresa_id' => $empresa->id
            ]);

            return redirect()->back()->with('success', "Empresa de prueba creada exitosamente: {$empresa->nombre_legal}");
        } catch (\Exception $e) {
            Log::error('Error creando empresa de prueba', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al crear empresa de prueba: ' . $e->getMessage());
        }
    }

    /**
     * Configurar sistema de roles completo
     */
    public function setupRoles()
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['system.manage'])) {
            return $this->redirectIfNoAccess('Solo los superadministradores pueden configurar el sistema.');
        }

        try {
            // Ejecutar comando de configuración
            Artisan::call('roles:setup');

            Log::info('Sistema de roles configurado', [
                'configured_by' => Auth::id()
            ]);

            return redirect()->back()->with('success', 'Sistema de roles configurado correctamente');
        } catch (\Exception $e) {
            Log::error('Error configurando sistema de roles', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al configurar sistema de roles: ' . $e->getMessage());
        }
    }

    /**
     * Página para configurar sistema de roles
     */
    public function setupRolesPage()
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['system.manage'])) {
            return $this->redirectIfNoAccess('Solo los superadministradores pueden configurar el sistema.');
        }

        return view('system.management.setup-roles');
    }

    /**
     * Página para crear empresa de prueba
     */
    public function createTestCompanyPage()
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['empresa.create'])) {
            return $this->redirectIfNoAccess('No tienes permisos para crear empresas.');
        }

        return view('system.management.create-test-company');
    }

    /**
     * Obtener estadísticas del sistema
     */
    private function getSystemStats()
    {
        return [
            'users' => User::count(),
            'users_with_roles' => User::has('roles')->count(),
            'users_without_roles' => User::doesntHave('roles')->count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
            'companies' => Empresa::count(),
            'paises' => Pais::count(),
            'departamentos' => Departamento::count(),
            'municipios' => Municipio::count(),
        ];
    }

    /**
     * Verificar acceso del usuario basado en roles y permisos
     */
    private function checkUserAccess(array $requiredPermissions = []): bool
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        // Superadmin tiene acceso total
        if ($this->userHasRole('Superadmin')) {
            return true;
        }

        // Verificar permisos específicos
        if (!empty($requiredPermissions)) {
            foreach ($requiredPermissions as $permission) {
                if ($this->userHasPermission($permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    private function userHasRole(string $role): bool
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
     * Verificar si el usuario tiene un permiso específico
     */
    private function userHasPermission(string $permission): bool
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
     * Redirigir si no tiene acceso
     */
    private function redirectIfNoAccess(string $message): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('home')->with('error', $message);
    }
}
