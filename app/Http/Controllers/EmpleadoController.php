<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\EmpresasCliente;
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
        $empresas = EmpresasCliente::orderBy('nombre')->get();
        return view('empleados.create', compact('empresas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', function($attribute, $value, $fail) {
                if (str_word_count($value) > 10) {
                    $fail('El nombre no debe tener más de 10 palabras.');
                }
            }],
            'telefono' => ['required', 'digits:10', 'regex:/^[0-9]{10}$/'],
            'correo_electronico' => 'required|email|unique:empleados,correo_electronico',
            'empresa_id' => 'nullable|exists:empresas_clientes,id',
        ]);

        Empleado::create($validated);

        return redirect()->route('empleados.index')->with('success', 'Empleado cliente registrado correctamente.');
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
            $resultado = $importador->importar($request->file('file'));
            $errores = $resultado['errores'];
            $exitosas = $resultado['exitosas'];
            $fallidas = $resultado['fallidas'];
            if (empty($errores)) {
                return redirect()->route('empleados.index')->with('success', "Empleados importados correctamente. Filas exitosas: $exitosas");
            } else {
                return back()->withErrors([
                    'file' => "Importación finalizada. Filas exitosas: $exitosas, filas fallidas: $fallidas.",
                    'detalles' => $errores
                ]);
            }
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
        $empresas = EmpresasCliente::orderBy('nombre')->get();
        return view('empleados.edit', compact('empleado', 'empresas'));
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
            'telefono' => ['required', 'digits:10', 'regex:/^[0-9]{10}$/'],
            'correo_electronico' => 'required|email|unique:empleados,correo_electronico,' . $empleado->id,
            'empresa_id' => 'nullable|exists:empresas_clientes,id',
        ]);
        $empleado->update($validated);
        return redirect()->route('empleados.index')->with('success', 'Empleado cliente actualizado correctamente.');
    }

    public function destroy($id)
    {
        $empleado = Empleado::findOrFail($id);
        $empleado->delete();
        return redirect()->route('empleados.index')->with('success', 'Empleado cliente eliminado correctamente.');
    }
}
