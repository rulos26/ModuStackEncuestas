@extends('adminlte::page')

@section('title', 'Departamentos')

@section('content_header')
    <h1><i class="fas fa-map"></i> Departamentos
        <a href="{{ route('departamentos.create') }}" class="btn btn-success btn-sm float-right"><i class="fas fa-plus"></i> Nuevo Departamento</a>
    </h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="departamentosTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>País</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departamentos as $departamento)
                    <tr>
                        <td>{{ $departamento->id }}</td>
                        <td>{{ $departamento->nombre }}</td>
                        <td>{{ $departamento->pais->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('departamentos.edit', $departamento) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('departamentos.destroy', $departamento) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro de eliminar este departamento?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    $('#departamentosTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        pageLength: 25
    });
});
</script>
@stop
