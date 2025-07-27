@extends('layouts.app')

@section('title', 'Configurar Sistema de Roles')

@section('content_header')
    <h1>Configurar Sistema de Roles</h1>
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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Configuración del Sistema de Roles
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Información</h5>
                        <p>Esta herramienta te permite reconfigurar completamente el sistema de roles y permisos.
                        Se ejecutará el comando <code>php artisan roles:setup</code> que:</p>
                        <ul>
                            <li>Verifica la conexión a la base de datos</li>
                            <li>Comprueba que existan todas las tablas necesarias</li>
                            <li>Ejecuta los seeders de roles y usuarios</li>
                            <li>Verifica que los roles y permisos se hayan creado correctamente</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('system.setup-roles') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-lg btn-block"
                                        onclick="return confirm('¿Estás seguro de que quieres reconfigurar el sistema de roles? Esta acción puede tomar unos segundos.')">
                                    <i class="fas fa-cogs"></i> Configurar Sistema de Roles
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('system.index') }}" class="btn btn-secondary btn-lg btn-block">
                                <i class="fas fa-arrow-left"></i> Volver al Panel
                            </a>
                        </div>
                    </div>

                    <hr>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5><i class="fas fa-lightbulb text-info"></i> Consejos:</h5>
                            <ul>
                                <li>Ejecuta esta configuración solo cuando sea necesario</li>
                                <li>Los roles existentes se mantendrán si ya están creados</li>
                                <li>Los usuarios mantendrán sus roles asignados</li>
                                <li>Si hay errores, revisa los logs del sistema</li>
                            </ul>
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
    .alert {
        margin-bottom: 20px;
    }
    .btn-lg {
        padding: 15px 30px;
        font-size: 18px;
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
