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

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%") ;
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        $users = $query->orderByDesc('created_at')->paginate(10)->appends($request->all());
        return view('users.index', compact('users'));
    }

    public function export(Request $request)
    {
        $query = User::query();
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%") ;
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        $users = $query->orderByDesc('created_at')->get(['id','name','email','role','created_at']);
        $format = $request->get('format', 'csv');

        // Crear directorio temporal si no existe
        if (!Storage::exists('temp')) {
            Storage::makeDirectory('temp');
        }

        $export = new UsersExport($users);

        if ($format === 'xlsx') {         $filename = 'usuarios_' . date('Y-m-d_H-i-s') . '.xlsx';
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
        return view('users.create');
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $rol = $data['role'] ?? null;
        unset($data['role']);
        $user = User::create($data);
        if ($rol) {
            $user->assignRole($rol);
        }
        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        if (!empty($data['password'])) {
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
        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
