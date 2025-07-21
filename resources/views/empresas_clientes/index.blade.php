@extends('adminlte::page')

@section('title', 'Empresas Clientes')

@section('content_header')
    <h1>Empresas Clientes</h1>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="mb-3">
    <a href="{{ route('empresas_clientes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Nueva Empresa Cliente
    </a>
</div>
<div class="card">
    <table class="table table-hover table-striped mb-0">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>NIT</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Contacto</th>
                <th>Cargo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($empresas as $empresa)
                <tr>
                    <td>{{ $empresa->nombre }}</td>
                    <td>{{ $empresa->nit }}</td>
                    <td>{{ $empresa->telefono }}</td>
                    <td>{{ $empresa->correo_electronico }}</td>
                    <td>{{ $empresa->contacto }}</td>
                    <td>{{ $empresa->cargo_contacto }}</td>
                    <td>
                        <a href="{{ route('empresas_clientes.show', $empresa) }}" class="btn btn-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('empresas_clientes.edit', $empresa) }}" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('empresas_clientes.destroy', $empresa) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro que deseas eliminar esta empresa cliente?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                        <a href="{{ route('empresas_clientes.exportPdf', $empresa) }}" class="btn btn-secondary btn-sm" title="Exportar PDF"><i class="fas fa-file-pdf"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No hay empresas clientes registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $empresas->links() }}</div>
</div>
@endsection
