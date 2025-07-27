@extends('adminlte::page')

@section('title', 'Crear Empresa de Prueba')

@section('content_header')
    <h1>Crear Empresa de Prueba</h1>
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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> Crear Empresa de Prueba
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Información</h5>
                        <p>Esta herramienta crea automáticamente una empresa de prueba con todos los datos necesarios para que puedas crear encuestas. La empresa incluye:</p>
                        <ul>
                            <li><strong>Datos básicos:</strong> Nombre, NIT, representante legal</li>
                            <li><strong>Información de contacto:</strong> Email, teléfono, dirección</li>
                            <li><strong>Ubicación:</strong> País, departamento y municipio</li>
                            <li><strong>Información corporativa:</strong> Misión, visión, descripción</li>
                        </ul>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('system.create-test-company') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg btn-block"
                                        onclick="return confirm('¿Estás seguro de que quieres crear una empresa de prueba?')">
                                    <i class="fas fa-plus"></i> Crear Empresa de Prueba
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <a href="{{ route('system.companies') }}" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-list"></i> Ver Empresas Existentes
                            </a>
                        </div>
                    </div>

                    <hr>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5><i class="fas fa-lightbulb text-info"></i> ¿Cuándo usar esta herramienta?</h5>
                            <ul>
                                <li>Cuando no hay empresas creadas en el sistema</li>
                                <li>Para desarrollo y testing de encuestas</li>
                                <li>Como punto de partida para configurar el sistema</li>
                                <li>Para demostraciones del sistema</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-exclamation-triangle text-warning"></i> Importante:</h5>
                            <ul>
                                <li>Si ya existe una empresa, no se creará otra</li>
                                <li>La empresa se crea con datos de Colombia por defecto</li>
                                <li>Puedes editar la empresa después de crearla</li>
                                <li>Esta empresa es necesaria para crear encuestas</li>
                            </ul>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <h5><i class="fas fa-arrow-right text-primary"></i> Próximos pasos después de crear la empresa:</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="{{ route('encuestas.create') }}" class="btn btn-outline-primary btn-block">
                                        <i class="fas fa-poll"></i> Crear Encuesta
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('empresa.show') }}" class="btn btn-outline-info btn-block">
                                        <i class="fas fa-building"></i> Ver Empresa
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('system.index') }}" class="btn btn-outline-secondary btn-block">
                                        <i class="fas fa-cogs"></i> Panel de Gestión
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="{{ route('home') }}" class="btn btn-outline-success btn-block">
                                        <i class="fas fa-home"></i> Dashboard
                                    </a>
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
    .alert {
        margin-bottom: 20px;
    }
    .btn-lg {
        padding: 15px 30px;
        font-size: 18px;
    }
    .btn-outline-primary, .btn-outline-info, .btn-outline-secondary, .btn-outline-success {
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
