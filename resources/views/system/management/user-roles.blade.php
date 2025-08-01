@extends('adminlte::page')

@section('title', 'Gestión de Roles de Usuarios')

@section('content_header')
    <h1>Gestión de Roles de Usuarios</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> ¡Error!</h5>
            {{ session('error') }}
        </div>
    @endif

    <!-- Acciones Masivas -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-magic"></i> Acciones Masivas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form action="{{ route('system.assign-default-roles') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block"
                                        onclick="return confirm('¿Estás seguro de que quieres asignar roles por defecto a todos los usuarios?')">
                                    <i class="fas fa-magic"></i> Asignar Roles por Defecto
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('system.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-arrow-left"></i> Volver al Panel
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('users.index') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-users"></i> Gestionar Usuarios
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Usuarios -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Usuarios y sus Roles
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Roles Actuales</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->roles->count() > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge badge-{{ $role->name === 'Superadmin' ? 'danger' : ($role->name === 'Admin' ? 'warning' : 'info') }}">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="badge badge-warning">Sin roles</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->roles->count() > 0)
                                            <span class="badge badge-success">Con roles</span>
                                        @else
                                            <span class="badge badge-danger">Sin roles</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                data-toggle="modal" data-target="#assignRoleModal{{ $user->id }}">
                                            <i class="fas fa-user-tag"></i> Asignar Rol
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal para asignar rol -->
                                <div class="modal fade" id="assignRoleModal{{ $user->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form action="{{ route('system.assign-role') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="user_id" value="{{ $user->id }}">

                                                <div class="modal-header">
                                                    <h5 class="modal-title">Asignar Rol a {{ $user->name }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>

                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="role_id">Seleccionar Rol:</label>
                                                        <select name="role_id" id="role_id" class="form-control" required>
                                                            <option value="">Selecciona un rol...</option>
                                                            @foreach($roles as $role)
                                                                <option value="{{ $role->id }}"
                                                                        {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                                    {{ $role->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="alert alert-info">
                                                        <strong>Roles disponibles:</strong>
                                                        <ul class="mb-0">
                                                            <li><strong>Superadmin:</strong> Acceso total al sistema</li>
                                                            <li><strong>Admin:</strong> Gestión limitada</li>
                                                            <li><strong>Cliente:</strong> Acceso básico</li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary">Asignar Rol</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Estadísticas de Roles
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Usuarios</span>
                                    <span class="info-box-number">{{ $users->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-user-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Con Roles</span>
                                    <span class="info-box-number">{{ $users->filter(function($user) { return $user->roles->count() > 0; })->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-user-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sin Roles</span>
                                    <span class="info-box-number">{{ $users->filter(function($user) { return $user->roles->count() == 0; })->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-shield-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Roles Disponibles</span>
                                    <span class="info-box-number">{{ $roles->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .badge {
        font-size: 0.8em;
    }
    .table th {
        background-color: #f4f6f9;
    }
    .info-box {
        margin-bottom: 20px;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });

    // Función para obtener el color del badge según el rol
    function getRoleBadgeColor(roleName) {
        switch(roleName) {
            case 'Superadmin':
                return 'danger';
            case 'Admin':
                return 'primary';
            case 'Cliente':
                return 'success';
            default:
                return 'secondary';
        }
    }
</script>
@stop
