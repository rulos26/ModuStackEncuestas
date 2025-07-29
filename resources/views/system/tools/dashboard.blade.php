@extends('adminlte::page')

@section('title', 'Herramientas del Sistema')

@section('content_header')
    <h1><i class="fas fa-tools"></i> Herramientas del Sistema</h1>
@endsection

@section('content')
<div class="row">
    <!-- Información del Sistema -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Sistema</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td>{{ $systemInfo['php_version'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Laravel Version:</strong></td>
                        <td>{{ $systemInfo['laravel_version'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Base de Datos:</strong></td>
                        <td>{{ $systemInfo['database_connection'] }} - {{ $systemInfo['database_name'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Entorno:</strong></td>
                        <td>
                            <span class="badge badge-{{ $systemInfo['app_environment'] === 'production' ? 'danger' : 'warning' }}">
                                {{ $systemInfo['app_environment'] }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Debug:</strong></td>
                        <td>
                            <span class="badge badge-{{ $systemInfo['app_debug'] ? 'danger' : 'success' }}">
                                {{ $systemInfo['app_debug'] ? 'Activado' : 'Desactivado' }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Timezone:</strong></td>
                        <td>{{ $systemInfo['timezone'] }}</td>
                    </tr>
                    <tr>
                        <td><strong>Locale:</strong></td>
                        <td>{{ $systemInfo['locale'] }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Estadísticas de la Base de Datos -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-database"></i> Estadísticas de la Base de Datos</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($databaseStats as $nombre => $count)
                    <div class="col-6 mb-2">
                        <div class="info-box">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-table"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text">{{ $nombre }}</span>
                                <span class="info-box-number">{{ $count }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Herramientas Disponibles -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Herramientas Disponibles</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Diagnóstico de Encuestas -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-check fa-3x mb-3"></i>
                                <h5>Diagnóstico de Encuestas</h5>
                                <p class="card-text">Verifica el estado y funcionamiento del módulo de encuestas</p>
                                <a href="{{ route('system.tools.diagnosticar-encuestas') }}" class="btn btn-light">
                                    <i class="fas fa-play"></i> Ejecutar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnóstico de Preguntas -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-success">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle fa-3x mb-3"></i>
                                <h5>Diagnóstico de Preguntas</h5>
                                <p class="card-text">Verifica el estado y funcionamiento del módulo de preguntas</p>
                                <a href="{{ route('system.tools.diagnosticar-preguntas') }}" class="btn btn-light">
                                    <i class="fas fa-play"></i> Ejecutar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Migraciones -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-3x mb-3"></i>
                                <h5>Gestión de Migraciones</h5>
                                <p class="card-text">Ejecuta y gestiona las migraciones de la base de datos</p>
                                <a href="{{ route('system.tools.migraciones') }}" class="btn btn-light">
                                    <i class="fas fa-play"></i> Ejecutar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Seeders -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-info">
                            <div class="card-body text-center">
                                <i class="fas fa-seedling fa-3x mb-3"></i>
                                <h5>Gestión de Seeders</h5>
                                <p class="card-text">Ejecuta seeders para poblar la base de datos con datos de prueba</p>
                                <a href="{{ route('system.tools.seeders') }}" class="btn btn-light">
                                    <i class="fas fa-play"></i> Ejecutar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Pruebas del Sistema -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-danger">
                            <div class="card-body text-center">
                                <i class="fas fa-vial fa-3x mb-3"></i>
                                <h5>Pruebas del Sistema</h5>
                                <p class="card-text">Ejecuta pruebas automatizadas para verificar el funcionamiento</p>
                                <a href="{{ route('system.tools.pruebas') }}" class="btn btn-light">
                                    <i class="fas fa-play"></i> Ejecutar
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Logs del Sistema -->
                    <div class="col-md-4 mb-3">
                        <div class="card bg-secondary">
                            <div class="card-body text-center">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <h5>Logs del Sistema</h5>
                                <p class="card-text">Visualiza los logs del sistema para diagnóstico</p>
                                <a href="{{ route('logs.index') }}" class="btn btn-light">
                                    <i class="fas fa-eye"></i> Ver Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estado de Migraciones -->
@if($migrationStatus['success'])
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Estado de Migraciones</h3>
            </div>
            <div class="card-body">
                <pre class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">{{ $migrationStatus['output'] }}</pre>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('css')
<style>
.info-box {
    min-height: 80px;
}
.info-box-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.info-box-content {
    padding: 5px 10px;
}
.info-box-text {
    font-size: 12px;
    margin-bottom: 0;
}
.info-box-number {
    font-size: 18px;
    font-weight: bold;
}
</style>
@endsection
