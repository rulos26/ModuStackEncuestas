@extends('layouts.app')

@section('title', 'Gestión del Sistema')

@section('content_header')
    <h1>Panel de Gestión del Sistema</h1>
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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-info"></i> Información</h5>
            {{ session('info') }}
        </div>
    @endif

    <!-- Estadísticas del Sistema -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['users'] }}</h3>
                    <p>Usuarios Totales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('system.user-roles') }}" class="small-box-footer">
                    Gestionar Roles <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['users_with_roles'] }}</h3>
                    <p>Usuarios con Roles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('system.user-roles') }}" class="small-box-footer">
                    Ver Detalles <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['users_without_roles'] }}</h3>
                    <p>Usuarios sin Roles</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-times"></i>
                </div>
                <a href="{{ route('system.user-roles') }}" class="small-box-footer">
                    Asignar Roles <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['companies'] }}</h3>
                    <p>Empresas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <a href="{{ route('system.companies') }}" class="small-box-footer">
                    Gestionar Empresas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Herramientas de Gestión -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users-cog"></i> Gestión de Usuarios y Roles
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('system.user-roles') }}" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-user-tag"></i> Gestionar Roles de Usuarios
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('system.assign-default-roles') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block mb-2"
                                        onclick="return confirm('¿Estás seguro de que quieres asignar roles por defecto a todos los usuarios?')">
                                    <i class="fas fa-magic"></i> Asignar Roles por Defecto
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('system.setup-roles') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-block"
                                        onclick="return confirm('¿Estás seguro de que quieres reconfigurar el sistema de roles?')">
                                    <i class="fas fa-cogs"></i> Configurar Sistema de Roles
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('users.index') }}" class="btn btn-info btn-block">
                                <i class="fas fa-list"></i> Ver Todos los Usuarios
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> Gestión de Empresas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('system.companies') }}" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-list"></i> Ver Empresas
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('system.create-test-company') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block mb-2"
                                        onclick="return confirm('¿Estás seguro de que quieres crear una empresa de prueba?')">
                                    <i class="fas fa-plus"></i> Crear Empresa de Prueba
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('empresa.create') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-plus-circle"></i> Crear Nueva Empresa
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('empresa.show') }}" class="btn btn-info btn-block">
                                <i class="fas fa-eye"></i> Ver Empresa Principal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Sistema -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información del Sistema
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-shield-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Roles</span>
                                    <span class="info-box-number">{{ $stats['roles'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-key"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Permisos</span>
                                    <span class="info-box-number">{{ $stats['permissions'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-map-marker-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Países</span>
                                    <span class="info-box-number">{{ $stats['paises'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-map"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Departamentos</span>
                                    <span class="info-box-number">{{ $stats['departamentos'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Accesos Rápidos -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-rocket"></i> Accesos Rápidos
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <a href="{{ route('encuestas.index') }}" class="btn btn-outline-primary btn-block mb-2">
                                <i class="fas fa-clipboard-list"></i><br>Encuestas
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('empleados.index') }}" class="btn btn-outline-success btn-block mb-2">
                                <i class="fas fa-user-tie"></i><br>Empleados
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('users.index') }}" class="btn btn-outline-info btn-block mb-2">
                                <i class="fas fa-users"></i><br>Usuarios
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('logs.index') }}" class="btn btn-outline-warning btn-block mb-2">
                                <i class="fas fa-file-alt"></i><br>Logs
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('system.optimizer.index') }}" class="btn btn-outline-danger btn-block mb-2">
                                <i class="fas fa-tools"></i><br>Optimizador
                            </a>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-block mb-2">
                                <i class="fas fa-home"></i><br>Inicio
                            </a>
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
    .small-box {
        margin-bottom: 20px;
    }
    .info-box {
        margin-bottom: 20px;
    }
    .btn-block {
        margin-bottom: 10px;
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
    });
</script>
@stop
