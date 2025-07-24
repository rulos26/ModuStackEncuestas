@extends('adminlte::page')

@section('content')
<div class="container">
    <h2>Listado de Encuestas</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('encuestas.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Crear nueva encuesta
    </a>

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
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
                        <div class="btn-group" role="group">
                            <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info btn-sm" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('encuestas.edit', $encuesta->id) }}" class="btn btn-warning btn-sm" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('encuestas.preview', $encuesta->id) }}" class="btn btn-secondary btn-sm" title="Vista previa">
                                <i class="fas fa-search"></i>
                            </a>
                            <a href="{{ route('encuestas.publica', $encuesta->slug) }}" class="btn btn-success btn-sm" title="Vista pública" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            {{-- Botón para clonar encuesta --}}
                            <form action="{{ route('encuestas.clone', $encuesta->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-dark btn-sm" title="Clonar encuesta" onclick="return confirm('¿Deseas clonar esta encuesta?')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </form>
                            <form action="{{ route('encuestas.destroy', $encuesta->id) }}" method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Seguro que deseas eliminar esta encuesta?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
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