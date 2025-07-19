<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use App\Imports\EmpleadosImport;
use Maatwebsite\Excel\Facades\Excel;

class EmpleadoController extends Controller
{
    public function index()
    {
        $empleados = Empleado::all();
        return view('empleados.index', compact('empleados'));
    }

    public function create()
    {
        return view('empleados.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', function($attribute, $value, $fail) {
                if (str_word_count($value) > 10) {
                    $fail('El nombre no debe tener más de 10 palabras.');
                }
            }],
            'cargo' => ['required', 'string', 'max:255', function($attribute, $value, $fail) {
                if (str_word_count($value) > 10) {
                    $fail('El cargo no debe tener más de 10 palabras.');
                }
            }],
            'telefono' => ['required', 'digits:10', 'regex:/^[0-9]{10}$/'],
            'correo_electronico' => 'required|email|unique:empleados,correo_electronico',
        ]);

        Empleado::create($validated);

        return redirect()->route('empleados.index')->with('success', 'Empleado registrado correctamente.');
    }

    public function importForm()
    {
        return view('empleados.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls',
        ]);

        try {
            $importador = new \App\Imports\EmpleadosImport();
            $importador->importar($request->file('file'));
            return redirect()->route('empleados.index')->with('success', 'Empleados importados correctamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error al importar el archivo: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $empleado = Empleado::findOrFail($id);
        return view('empleados.show', compact('empleado'));
    }

    public function edit($id)
    {
        $empleado = Empleado::findOrFail($id);
        return view('empleados.edit', compact('empleado'));
    }

    public function update(Request $request, $id)
    {
        $empleado = Empleado::findOrFail($id);
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', function($attribute, $value, $fail) {
                if (str_word_count($value) > 10) {
                    $fail('El nombre no debe tener más de 10 palabras.');
                }
            }],
            'cargo' => ['required', 'string', 'max:255', function($attribute, $value, $fail) {
                if (str_word_count($value) > 10) {
                    $fail('El cargo no debe tener más de 10 palabras.');
                }
            }],
            'telefono' => ['required', 'digits:10', 'regex:/^[0-9]{10}$/'],
            'correo_electronico' => 'required|email|unique:empleados,correo_electronico,' . $empleado->id,
        ]);
        $empleado->update($validated);
        return redirect()->route('empleados.index')->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();
        return redirect()->route('empleados.index')->with('success', 'Empleado eliminado correctamente.');
    }
}
