@extends('adminlte::page')

@section('title', 'Políticas de Privacidad')

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Políticas de Privacidad
        <a href="{{ route('politicas-privacidad.create') }}" class="btn btn-success btn-sm float-right"><i class="fas fa-plus"></i> Nueva Política</a>
    </h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="politicasTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Versión</th>
                        <th>Fecha Publicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($politicas as $politica)
                    <tr>
                        <td>{{ $politica->id }}</td>
                        <td>{{ $politica->titulo }}</td>
                        <td>{{ $politica->version }}</td>
                        <td>{{ $politica->fecha_publicacion ? $politica->fecha_publicacion->format('d/m/Y') : '-' }}</td>
                        <td>
                            @if($politica->estado)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('politicas-privacidad.show', $politica) }}" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('politicas-privacidad.edit', $politica) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('politicas-privacidad.destroy', $politica) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro de eliminar esta política?')">
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
    $('#politicasTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        pageLength: 25
    });
});
</script>
@stop
