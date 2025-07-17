@extends('adminlte::page')
@section('title', 'Roles')
@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Roles</h1>
@stop
@section('content')
<div class="mb-3">
    <a href="{{ route('roles.create') }}" class="btn btn-success"><i class="fas fa-plus"></i> Nuevo Rol</a>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Permisos</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($roles as $role)
        <tr>
            <td>{{ $role->name }}</td>
            <td>
                @foreach($role->permissions as $perm)
                    <span class="badge bg-info">{{ $perm->name }}</span>
                @endforeach
            </td>
            <td>
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                <form action="{{ route('roles.destroy', $role) }}" method="POST" style="display:inline-block" onsubmit="return confirm('¿Eliminar este rol?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div>{{ $roles->links() }}</div>
@stop
