<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Pais;
use App\Http\Requests\DepartamentoRequest;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $departamentos = Departamento::with('pais')->orderBy('nombre')->get();
        return view('departamentos.index', compact('departamentos'));
    }

    public function create()
    {
        $paises = Pais::orderBy('name')->get();
        return view('departamentos.create', compact('paises'));
    }

    public function store(DepartamentoRequest $request)
    {
        Departamento::create($request->validated());
        return redirect()->route('departamentos.index')->with('success', 'Departamento creado correctamente.');
    }

    public function edit(Departamento $departamento)
    {
        $paises = Pais::orderBy('name')->get();
        return view('departamentos.edit', compact('departamento', 'paises'));
    }

    public function update(DepartamentoRequest $request, Departamento $departamento)
    {
        $departamento->update($request->validated());
        return redirect()->route('departamentos.index')->with('success', 'Departamento actualizado correctamente.');
    }

    public function destroy(Departamento $departamento)
    {
        $departamento->delete();
        return redirect()->route('departamentos.index')->with('success', 'Departamento eliminado correctamente.');
    }
}
