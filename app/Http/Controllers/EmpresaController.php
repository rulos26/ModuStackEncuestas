<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Pais;
use App\Models\Departamento;
use App\Models\Municipio;
use App\Http\Requests\EmpresaRequest;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EmpresaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
    {
        $empresa = Empresa::first();
        return view('empresa.show', compact('empresa'));
    }

    public function create()
    {
        if (Empresa::count() > 0) {
            return redirect()->route('empresa.edit');
        }
        $paises = Pais::all();
        return view('empresa.create', compact('paises'));
    }

    public function store(EmpresaRequest $request)
    {
        if (Empresa::count() > 0) {
            return redirect()->route('empresa.edit');
        }
        $empresa = Empresa::create($request->validated());
        return redirect()->route('empresa.show')->with('success', 'Información de la empresa registrada correctamente.');
    }

    public function edit()
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            return redirect()->route('empresa.create');
        }
        $paises = Pais::all();
        $departamentos = Departamento::where('pais_id', $empresa->pais_id)->get();
        $municipios = Municipio::where('departamento_id', $empresa->departamento_id)->get();
        return view('empresa.edit', compact('empresa', 'paises', 'departamentos', 'municipios'));
    }

    public function update(EmpresaRequest $request)
    {
        $empresa = Empresa::first();
        if (!$empresa) {
            return redirect()->route('empresa.create');
        }
        $empresa->update($request->validated());
        return redirect()->route('empresa.show')->with('success', 'Información de la empresa actualizada correctamente.');
    }

    public function exportPdf()
    {
        $empresa = Empresa::with(['pais', 'departamento', 'municipio'])->first();
        if (!$empresa) {
            return redirect()->route('empresa.show')->with('error', 'No hay información de empresa para exportar.');
        }
        $pdf = Pdf::loadView('empresa.pdf', compact('empresa'));
        return $pdf->download('informacion_empresa.pdf');
    }

    // AJAX para selects encadenados
    public function getDepartamentos($pais_id)
    {
        $departamentos = Departamento::where('pais_id', $pais_id)->get();
        return response()->json($departamentos);
    }

    public function getMunicipios($departamento_id)
    {
        $municipios = Municipio::where('departamento_id', $departamento_id)->get();
        return response()->json($municipios);
    }
}
