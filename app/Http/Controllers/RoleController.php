<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        try {
            $data = $request->validated();
            $role = Role::create(['name' => $data['name']]);
            if (!empty($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }
            return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
        } catch (\Exception $e) {
            Log::channel('role_module')->error('Error en creación de rol', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Ocurrió un error al crear el rol.');
        }
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        try {
            $data = $request->validated();
            $role->update(['name' => $data['name']]);
            $role->syncPermissions($data['permissions'] ?? []);
            return redirect()->route('roles.index')->with('success', 'Rol actualizado correctamente.');
        } catch (\Exception $e) {
            Log::channel('role_module')->error('Error en actualización de rol', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Ocurrió un error al actualizar el rol.');
        }
    }

    public function destroy(Role $role)
    {
        try {
            $role->delete();
            return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
        } catch (\Exception $e) {
            Log::channel('role_module')->error('Error en eliminación de rol', [
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Ocurrió un error al eliminar el rol.');
        }
    }
}
