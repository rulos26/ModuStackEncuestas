<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Exports\UsersExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.index'])) {
            $this->logAccessDenied('users.index', ['Superadmin', 'Admin'], ['users.index']);
            return $this->redirectIfNoAccess('No tienes permisos para ver usuarios.');
        }

        $query = User::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%") ;
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(10)->appends($request->all());
        return view('users.index', compact('users'));
    }

    public function export(Request $request)
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.export'])) {
            $this->logAccessDenied('users.export', ['Superadmin', 'Admin'], ['users.export']);
            return $this->redirectIfNoAccess('No tienes permisos para exportar usuarios.');
        }

        $query = User::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%") ;
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderByDesc('created_at')->get();
        $format = $request->get('format', 'csv');

        // Crear directorio temporal si no existe
        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $export = new UsersExport($users);

        if ($format === 'xlsx') {
            $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.xlsx';
            $filePath = $export->exportToExcel($filename);

            return response()->download($filePath, $filename,
               ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } else {
            $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.csv';
            $filePath = $export->exportToCsv($filename);

            return response()->download($filePath, $filename,
               ['Content-Type' => 'text/csv',
            ])->deleteFileAfterSend(true);
        }
    }

    public function create()
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.create'])) {
            $this->logAccessDenied('users.create', ['Superadmin'], ['users.create']);
            return $this->redirectIfNoAccess('No tienes permisos para crear usuarios.');
        }

        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.create'])) {
            $this->logAccessDenied('users.store', ['Superadmin'], ['users.create']);
            return $this->redirectIfNoAccess('No tienes permisos para crear usuarios.');
        }

        try {
            $data = $request->validated();
            $data['password'] = Hash::make($data['password']);
            $rol = $data['role'] ?? null;
            unset($data['role']);

            $user = User::create($data);

            if ($rol) {
                $user->assignRole($rol);
            }

            Log::info('Usuario creado exitosamente', [
                'user_id' => $user->id,
                'created_by' => Auth::id(),
                'role_assigned' => $rol
            ]);

            return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            Log::channel('user_module')->error('Error en creación de usuario', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    public function show(User $user)
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.show'])) {
            $this->logAccessDenied('users.show', ['Superadmin', 'Admin'], ['users.show']);
            return $this->redirectIfNoAccess('No tienes permisos para ver usuarios.');
        }

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.edit'])) {
            $this->logAccessDenied('users.edit', ['Superadmin', 'Admin'], ['users.edit']);
            return $this->redirectIfNoAccess('No tienes permisos para editar usuarios.');
        }

        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.edit'])) {
            $this->logAccessDenied('users.update', ['Superadmin', 'Admin'], ['users.edit']);
            return $this->redirectIfNoAccess('No tienes permisos para editar usuarios.');
        }

        try {
            $data = $request->validated();

            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $rol = $data['role'] ?? null;
            unset($data['role']);

            $user->update($data);

            if ($rol) {
                $user->syncRoles([$rol]);
            }

            Log::info('Usuario actualizado exitosamente', [
                'user_id' => $user->id,
                'updated_by' => Auth::id(),
                'role_assigned' => $rol
            ]);

            return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
        } catch (\Exception $e) {
            Log::channel('user_module')->error('Error en actualización de usuario', [
                'user_id' => $user->id,
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        // Verificar permisos
        if (!$this->checkUserAccess(['users.destroy'])) {
            $this->logAccessDenied('users.destroy', ['Superadmin'], ['users.destroy']);
            return $this->redirectIfNoAccess('No tienes permisos para eliminar usuarios.');
        }

        // Prevenir eliminación del usuario actual
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        try {
            $user->delete();

            Log::info('Usuario eliminado exitosamente', [
                'user_id' => $user->id,
                'deleted_by' => Auth::id()
            ]);

            return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
        } catch (\Exception $e) {
            Log::channel('user_module')->error('Error en eliminación de usuario', [
                'user_id' => $user->id,
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
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

    /**
     * Log de acceso denegado
     */
    private function logAccessDenied(string $action, array $requiredRoles = [], array $requiredPermissions = []): void
    {
        if (!Auth::check()) {
            return;
        }

        $user = Auth::user();

        Log::warning('Acceso denegado a usuarios', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'action' => $action,
            'required_roles' => $requiredRoles,
            'required_permissions' => $requiredPermissions,
            'user_roles' => $user->roles->pluck('name')->toArray(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
        ]);
    }
}
