@extends('adminlte::page')

@section('title', 'Municipios')

@section('content_header')
    <h1><i class="fas fa-map-marker-alt"></i> Municipios
        <a href="{{ route('municipios.create') }}" class="btn btn-success btn-sm float-right"><i class="fas fa-plus"></i> Nuevo Municipio</a>
    </h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="municipiosTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($municipios as $municipio)
                    <tr>
                        <td>{{ $municipio->id }}</td>
                        <td>{{ $municipio->nombre }}</td>
                        <td>{{ $municipio->departamento->nombre ?? '-' }}</td>
                        <td>
                            @if($municipio->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('municipios.edit', $municipio) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('municipios.destroy', $municipio) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro de eliminar este municipio?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                            </form>
                            <a href="{{ route('municipios.show', $municipio) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
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
    $('#municipiosTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        pageLength: 25
    });
});
</script>
@stop
