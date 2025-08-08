@extends('adminlte::page')

@section('title', 'Estadísticas de Envío Masivo')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Estadísticas de Envío Masivo
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('envio-masivo.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane mr-1"></i>
                            Nuevo Envío
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Resumen general -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-poll"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Encuestas</span>
                                    <span class="info-box-number">{{ $estadisticas['total_encuestas'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-paper-plane"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Enviadas</span>
                                    <span class="info-box-number">{{ $estadisticas['encuestas_enviadas'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-eye"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Publicadas</span>
                                    <span class="info-box-number">{{ $estadisticas['encuestas_publicadas'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-building"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Empresas Activas</span>
                                    <span class="info-box-number">{{ $estadisticas['empresas_activas'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de encuestas -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                Encuestas Disponibles para Envío
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($encuestas->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Título</th>
                                                <th>Empresa</th>
                                                <th>Estado</th>
                                                <th>Preguntas</th>
                                                <th>Creada</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($encuestas as $encuesta)
                                                <tr>
                                                    <td>{{ $encuesta->id }}</td>
                                                    <td>
                                                        <strong>{{ $encuesta->titulo }}</strong>
                                                    </td>
                                                    <td>
                                                        @if($encuesta->empresa)
                                                            <span class="badge badge-info">{{ $encuesta->empresa->nombre }}</span>
                                                        @else
                                                            <span class="badge badge-secondary">Sin empresa</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($encuesta->estado === 'enviada')
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-paper-plane mr-1"></i>
                                                                Enviada
                                                            </span>
                                                        @elseif($encuesta->estado === 'publicada')
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-eye mr-1"></i>
                                                                Publicada
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ ucfirst($encuesta->estado) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-primary">{{ $encuesta->preguntas->count() }}</span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">{{ $encuesta->created_at->format('d/m/Y H:i') }}</small>
                                                    </td>
                                                    <td>
                                                        @if($encuesta->estado === 'publicada')
                                                            <a href="{{ route('envio-masivo.index') }}?encuesta_id={{ $encuesta->id }}"
                                                               class="btn btn-sm btn-primary">
                                                                <i class="fas fa-paper-plane mr-1"></i>
                                                                Enviar
                                                            </a>
                                                        @elseif($encuesta->estado === 'enviada')
                                                            <span class="text-muted">Ya enviada</span>
                                                        @else
                                                            <span class="text-muted">No disponible</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle mr-1"></i>No hay encuestas disponibles</h6>
                                    <p class="mb-0">No hay encuestas publicadas o enviadas para mostrar estadísticas.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Gráficos de estadísticas -->
                    @if($encuestas->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-chart-pie mr-1"></i>
                                            Distribución por Estado
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="estadoChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-chart-bar mr-1"></i>
                                            Encuestas por Empresa
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="empresaChart" width="400" height="200"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Botones de acción -->
                    <div class="form-group mt-4">
                        <a href="{{ route('envio-masivo.index') }}" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i>
                            Realizar Envío
                        </a>

                        <a href="{{ route('home') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-home mr-1"></i>
                            Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    @if($encuestas->count() > 0)
        // Gráfico de distribución por estado
        const estadoCtx = document.getElementById('estadoChart').getContext('2d');
        const estadoChart = new Chart(estadoCtx, {
            type: 'pie',
            data: {
                labels: ['Enviadas', 'Publicadas'],
                datasets: [{
                    data: [
                        {{ $estadisticas['encuestas_enviadas'] }},
                        {{ $estadisticas['encuestas_publicadas'] }}
                    ],
                    backgroundColor: [
                        '#28a745',
                        '#ffc107'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Gráfico de encuestas por empresa
        const empresaCtx = document.getElementById('empresaChart').getContext('2d');
        const empresaChart = new Chart(empresaCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($encuestas->pluck('empresa.nombre')->filter()->unique()->values()) !!},
                datasets: [{
                    label: 'Encuestas',
                    data: {!! json_encode($encuestas->groupBy('empresa_id')->map->count()->values()) !!},
                    backgroundColor: '#007bff',
                    borderColor: '#0056b3',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    @endif
});
</script>
@endpush
