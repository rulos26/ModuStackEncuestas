@extends('adminlte::page')

@section('content')
<div class="container">
    <h2>Listado de Encuestas</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('encuestas.create') }}" class="btn btn-primary mb-3">Crear nueva encuesta</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Título</th>
                <th>Empresa</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($encuestas as $encuesta)
                <tr>
                    <td>{{ $encuesta->titulo }}</td>
                    <td>{{ $encuesta->empresa->nombre ?? '-' }}</td>
                    <td>{{ $encuesta->user->name ?? '-' }}</td>
                    <td>
                        @if($encuesta->habilitada)
                            <span class="badge bg-success">Habilitada</span>
                        @else
                            <span class="badge bg-danger">Deshabilitada</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('encuestas.edit', $encuesta->id) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('encuestas.destroy', $encuesta->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar esta encuesta?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay encuestas registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div>
        {{ $encuestas->links() }}
    </div>
</div>
@endsection