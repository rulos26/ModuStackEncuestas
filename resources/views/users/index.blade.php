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
<div class="mb-3">
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Nuevo Usuario
    </a>
    <a href="{{ route('users.export', ['format' => 'csv']) }}" class="btn btn-success mr-2"><i class="fas fa-file-csv"></i> Exportar CSV</a>
    <a href="{{ route('users.export', ['format' => 'xlsx']) }}" class="btn btn-success"><i class="fas fa-file-excel"></i> Exportar Excel</a>
</div>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0" id="users-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Creado</th>
                    <th>Roles</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
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
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#users-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        }
    });
});
</script>
@endpush

@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
@endpush
