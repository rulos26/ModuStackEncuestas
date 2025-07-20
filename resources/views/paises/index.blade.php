@extends('adminlte::page')

@section('title', 'Países')

@section('content_header')
    <h1><i class="fas fa-globe"></i> Países
        <a href="{{ route('paises.create') }}" class="btn btn-success btn-sm float-right"><i class="fas fa-plus"></i> Nuevo País</a>
    </h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="paisesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>ISO</th>
                        <th>Alfa2</th>
                        <th>Alfa3</th>
                        <th>Numérico</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($paises as $pais)
                    <tr>
                        <td>{{ $pais->id }}</td>
                        <td>{{ $pais->name }}</td>
                        <td>{{ $pais->iso_name }}</td>
                        <td>{{ $pais->alfa2 }}</td>
                        <td>{{ $pais->alfa3 }}</td>
                        <td>{{ $pais->numerico }}</td>
                        <td>
                            <a href="{{ route('paises.edit', $pais) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('paises.destroy', $pais) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro de eliminar este país?')">
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
    $('#paisesTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        pageLength: 25
    });
});
</script>
@stop
