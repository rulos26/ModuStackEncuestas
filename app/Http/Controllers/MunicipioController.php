<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use App\Models\Departamento;
use App\Http\Requests\MunicipioRequest;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $municipios = Municipio::with('departamento')->orderBy('nombre')->get();
        return view('municipios.index', compact('municipios'));
    }

    public function create()
    {
        $departamentos = Departamento::orderBy('nombre')->get();
        return view('municipios.create', compact('departamentos'));
    }

    public function store(MunicipioRequest $request)
    {
        Municipio::create($request->validated());
        return redirect()->route('municipios.index')->with('success', 'Municipio creado correctamente.');
    }

    public function edit(Municipio $municipio)
    {
        $departamentos = Departamento::orderBy('nombre')->get();
        return view('municipios.edit', compact('municipio', 'departamentos'));
    }

    public function update(MunicipioRequest $request, Municipio $municipio)
    {
        $municipio->update($request->validated());
        return redirect()->route('municipios.index')->with('success', 'Municipio actualizado correctamente.');
    }

    public function destroy(Municipio $municipio)
    {
        $municipio->delete();
        return redirect()->route('municipios.index')->with('success', 'Municipio eliminado correctamente.');
    }
}
