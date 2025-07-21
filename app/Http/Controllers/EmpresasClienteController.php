<?php

namespace App\Http\Controllers;

use App\Models\EmpresasCliente;
use App\Http\Requests\EmpresasClienteRequest;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EmpresasClienteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $empresas = EmpresasCliente::orderByDesc('created_at')->paginate(10);
        return view('empresas_clientes.index', compact('empresas'));
    }

    public function show(EmpresasCliente $empresas_cliente)
    {
        return view('empresas_clientes.show', compact('empresas_cliente'));
    }

    public function create()
    {
        return view('empresas_clientes.create');
    }

    public function store(EmpresasClienteRequest $request)
    {
        $empresa = EmpresasCliente::create($request->validated());
        return redirect()->route('empresas_clientes.show', $empresa)->with('success', 'Empresa cliente registrada correctamente.');
    }

    public function edit(EmpresasCliente $empresas_cliente)
    {
        return view('empresas_clientes.edit', compact('empresas_cliente'));
    }

    public function update(EmpresasClienteRequest $request, EmpresasCliente $empresas_cliente)
    {
        $empresas_cliente->update($request->validated());
        return redirect()->route('empresas_clientes.show', $empresas_cliente)->with('success', 'Empresa cliente actualizada correctamente.');
    }

    public function destroy(EmpresasCliente $empresas_cliente)
    {
        $empresas_cliente->delete();
        return redirect()->route('empresas_clientes.index')->with('success', 'Empresa cliente eliminada correctamente.');
    }

    public function exportPdf(EmpresasCliente $empresas_cliente)
    {
        $pdf = Pdf::loadView('empresas_clientes.pdf', compact('empresas_cliente'));
        return $pdf->download('empresa_cliente_' . $empresas_cliente->id . '.pdf');
    }
}
