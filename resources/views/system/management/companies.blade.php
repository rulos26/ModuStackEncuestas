@extends('adminlte::page')

@section('title', 'Gestión de Empresas')

@section('content_header')
    <h1>Gestión de Empresas</h1>
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

    <!-- Acciones -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools"></i> Acciones
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <form action="{{ route('system.create-test-company') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-block"
                                        onclick="return confirm('¿Estás seguro de que quieres crear una empresa de prueba?')">
                                    <i class="fas fa-plus"></i> Crear Empresa de Prueba
                                </button>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('empresa.create') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-plus-circle"></i> Crear Nueva Empresa
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('empresa.show') }}" class="btn btn-info btn-block">
                                <i class="fas fa-eye"></i> Ver Empresa Principal
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('system.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver al Panel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Empresas -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> Empresas Registradas
                    </h3>
                </div>
                <div class="card-body">
                    @if($companies->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre Legal</th>
                                        <th>NIT</th>
                                        <th>Representante</th>
                                        <th>Email</th>
                                        <th>Teléfono</th>
                                        <th>Ubicación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($companies as $company)
                                    <tr>
                                        <td>{{ $company->id }}</td>
                                        <td>
                                            <strong>{{ $company->nombre_legal }}</strong>
                                            @if($company->descripcion)
                                                <br><small class="text-muted">{{ Str::limit($company->descripcion, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $company->nit }}</td>
                                        <td>{{ $company->representante_legal }}</td>
                                        <td>
                                            <a href="mailto:{{ $company->email }}">{{ $company->email }}</a>
                                        </td>
                                        <td>
                                            <a href="tel:{{ $company->telefono }}">{{ $company->telefono }}</a>
                                        </td>
                                        <td>
                                            @if($company->pais && $company->departamento && $company->municipio)
                                                <small>
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    {{ $company->municipio->nombre }}, {{ $company->departamento->nombre }}, {{ $company->pais->name }}
                                                </small>
                                            @else
                                                <span class="text-muted">Sin ubicación</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('empresa.show') }}" class="btn btn-sm btn-info"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('empresa.edit') }}" class="btn btn-sm btn-warning"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('empresa.export.pdf') }}" class="btn btn-sm btn-success"
                                                   title="Exportar PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No hay empresas registradas</h4>
                            <p class="text-muted">Crea una empresa para comenzar a usar el sistema de encuestas.</p>
                            <div class="mt-3">
                                <form action="{{ route('system.create-test-company') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-plus"></i> Crear Empresa de Prueba
                                    </button>
                                </form>
                                <a href="{{ route('empresa.create') }}" class="btn btn-primary btn-lg ml-2">
                                    <i class="fas fa-plus-circle"></i> Crear Nueva Empresa
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    @if($companies->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información Adicional
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-exclamation-triangle text-warning"></i> Importante</h5>
                            <ul>
                                <li>Se requiere al menos una empresa para crear encuestas</li>
                                <li>La empresa principal se usa como predeterminada en las encuestas</li>
                                <li>Puedes crear múltiples empresas para diferentes clientes</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-lightbulb text-info"></i> Consejos</h5>
                            <ul>
                                <li>Usa la "Empresa de Prueba" para desarrollo y testing</li>
                                <li>Configura la ubicación completa para mejor organización</li>
                                <li>Exporta la información en PDF para respaldos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@stop

@section('css')
<style>
    .table th {
        background-color: #f4f6f9;
    }
    .btn-group .btn {
        margin-right: 2px;
    }
    .text-muted {
        color: #6c757d !important;
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
        $('[title]').tooltip();
    });
</script>
@stop
