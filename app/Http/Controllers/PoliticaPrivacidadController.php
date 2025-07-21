<?php

namespace App\Http\Controllers;

use App\Models\PoliticaPrivacidad;
use App\Http\Requests\PoliticaPrivacidadRequest;
use Illuminate\Http\Request;

class PoliticaPrivacidadController extends Controller
{
    public function index()
    {
        $politicas = PoliticaPrivacidad::orderByDesc('fecha_publicacion')->get();
        return view('politicas_privacidad.index', compact('politicas'));
    }

    public function create()
    {
        return view('politicas_privacidad.create');
    }

    public function store(PoliticaPrivacidadRequest $request)
    {
        if ($request->estado) {
            // Desactivar todas las demás políticas
            PoliticaPrivacidad::where('estado', true)->update(['estado' => false]);
        }
        PoliticaPrivacidad::create($request->validated());
        return redirect()->route('politicas-privacidad.index')->with('success', 'Política de privacidad creada correctamente.');
    }

    public function show(PoliticaPrivacidad $politicas_privacidad)
    {
        return view('politicas_privacidad.show', ['politica' => $politicas_privacidad]);
    }

    public function edit(PoliticaPrivacidad $politicas_privacidad)
    {
        return view('politicas_privacidad.edit', ['politica' => $politicas_privacidad]);
    }

    public function update(PoliticaPrivacidadRequest $request, PoliticaPrivacidad $politicas_privacidad)
    {
        if ($request->estado) {
            // Desactivar todas las demás políticas
            PoliticaPrivacidad::where('estado', true)->where('id', '!=', $politicas_privacidad->id)->update(['estado' => false]);
        }
        $politicas_privacidad->update($request->validated());
        return redirect()->route('politicas-privacidad.index')->with('success', 'Política de privacidad actualizada correctamente.');
    }

    public function destroy(PoliticaPrivacidad $politicas_privacidad)
    {
        $politicas_privacidad->delete();
        return redirect()->route('politicas-privacidad.index')->with('success', 'Política de privacidad eliminada correctamente.');
    }
}
