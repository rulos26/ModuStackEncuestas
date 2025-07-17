<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Exception;
use Spatie\Permission\Models\Role;

class UsuarioTestController extends Controller
{
    public function index()
    {
        return view('test.index');
    }

    public function ejecutar(Request $request)
    {
        try {
            // Prueba CRUD User
            $user = User::create([
                'name' => 'Usuario Test Temporal',
                'email' => 'test_temp_' . uniqid() . '@example.com',
                'password' => Hash::make('password123'),
            ]);
            $found = User::findOrFail($user->id);
            $found->update(['name' => 'Usuario Test Actualizado']);
            $found->delete();

            // Prueba CRUD Role
            $role = Role::create(['name' => 'test_role_' . uniqid()]);
            $roleFound = Role::findOrFail($role->id);
            $roleFound->update(['name' => 'test_role_actualizado_' . uniqid()]);
            $roleFound->delete();

            return view('test.resultado', [
                'success' => true,
                'mensaje' => '✅ Todos los procesos (User y Role) fueron exitosos.'
            ]);
        } catch (Exception $e) {
            return view('test.resultado', [
                'success' => false,
                'mensaje' => '❌ Error en la operación',
                'error' => [
                    'mensaje' => $e->getMessage(),
                    'linea' => $e->getLine(),
                    'archivo' => $e->getFile(),
                ]
            ]);
        }
    }
}
