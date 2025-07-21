<?php

namespace App\Http\Controllers;

use App\Models\Encuesta;
use App\Models\Empresa;
use App\Http\Requests\EncuestaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EncuestaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $encuestas = Encuesta::with('empresa', 'user')->orderByDesc('created_at')->paginate(10);
        return view('encuestas.index', compact('encuestas'));
    }

    public function show(Encuesta $encuesta)
    {
        return view('encuestas.show', compact('encuesta'));
    }

    public function create()
    {
        $empresas = Empresa::all();
        return view('encuestas.create', compact('empresas'));
    }

    public function store(EncuestaRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $encuesta = Encuesta::create($data);
        return redirect()->route('encuestas.show', $encuesta)->with('success', 'Encuesta creada correctamente.');
    }

    public function edit(Encuesta $encuesta)
    {
        $empresas = Empresa::all();
        return view('encuestas.edit', compact('encuesta', 'empresas'));
    }

    public function update(EncuestaRequest $request, Encuesta $encuesta)
    {
        $encuesta->update($request->validated());
        return redirect()->route('encuestas.show', $encuesta)->with('success', 'Encuesta actualizada correctamente.');
    }

    public function destroy(Encuesta $encuesta)
    {
        $encuesta->delete();
        return redirect()->route('encuestas.index')->with('success', 'Encuesta eliminada correctamente.');
    }
}
