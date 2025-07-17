@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <h1>Gestión de Usuarios</h1>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
<div class="mb-3 d-flex justify-content-between align-items-center">
    <div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
        </a>
    </div>
    <form method="GET" action="{{ route('users.index') }}" class="form-inline">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control mr-2" placeholder="Buscar nombre o email">
        <select name="role" class="form-control mr-2">
            <option value="">Todos los roles</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
            <option value="usuario" {{ request('role') == 'usuario' ? 'selected' : '' }}>Usuario</option>
        </select>
        <button type="submit" class="btn btn-secondary mr-2">Filtrar</button>
        <a href="{{ route('users.export', ['format' => 'csv'] + request()->all()) }}" class="btn btn-success mr-2"><i class="fas fa-file-csv"></i> Exportar CSV</a>
        <a href="{{ route('users.export', ['format' => 'xlsx'] + request()->all()) }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Exportar Excel</a>
    </form>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Creado</th>
                    <th>Roles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @foreach($user->roles as $role)
                            <span class="badge bg-info">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No hay usuarios registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $users->links() }}
    </div>
</div>
@endsection
