<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use App\Http\Requests\PaisRequest;
use Illuminate\Http\Request;

class PaisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $paises = Pais::orderBy('name')->get();
        return view('paises.index', compact('paises'));
    }

    public function create()
    {
        return view('paises.create');
    }

    public function store(PaisRequest $request)
    {
        Pais::create($request->validated());
        return redirect()->route('paises.index')->with('success', 'País creado correctamente.');
    }

    public function edit(Pais $pais)
    {
        return view('paises.edit', compact('pais'));
    }

    public function update(PaisRequest $request, Pais $pais)
    {
        $pais->update($request->validated());
        return redirect()->route('paises.index')->with('success', 'País actualizado correctamente.');
    }

    public function destroy(Pais $pais)
    {
        $pais->delete();
        return redirect()->route('paises.index')->with('success', 'País eliminado correctamente.');
    }
}
