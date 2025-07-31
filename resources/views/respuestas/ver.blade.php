@extends('layouts.app')

@section('title', 'Análisis de Respuestas - ' . $encuesta->titulo)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Análisis de Respuestas: {{ $encuesta->titulo }}
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success">
                            <i class="fas fa-robot"></i>
                            IA Generado
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Preguntas Analizadas</span>
                                    <span class="info-box-number">{{ $analisis->count() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Total de preguntas procesadas por IA
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-chart-pie"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Gráficos Generados</span>
                                    <span class="info-box-number">{{ $analisis->count() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Visualizaciones sugeridas por IA
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($analisis->isNotEmpty())
                        @foreach($analisis as $index => $analisisItem)
                            <div class="row">
                                <div class="col-12">
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-chart-{{ $analisisItem->tipo_grafico === 'pastel' ? 'pie' : 'bar' }}"></i>
                                                Pregunta {{ $index + 1 }}: {{ $analisisItem->pregunta->texto }}
                                            </h3>
                                            <div class="card-tools">
                                                <span class="badge badge-{{ $this->getTipoGraficoColor($analisisItem->tipo_grafico) }}">
                                                    <i class="fas fa-{{ $this->getTipoGraficoIcon($analisisItem->tipo_grafico) }}"></i>
                                                    {{ ucfirst($analisisItem->tipo_grafico) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="chart-container" style="position: relative; height:400px;">
                                                        <canvas id="chart-{{ $analisisItem->id }}"></canvas>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="analysis-info">
                                                        <h5><i class="fas fa-brain"></i> Análisis de IA</h5>
                                                        <div class="alert alert-info">
                                                            <p>{{ $analisisItem->analisis_ia }}</p>
                                                        </div>

                                                        <h6><i class="fas fa-info-circle"></i> Detalles</h6>
                                                        <ul class="list-unstyled">
                                                            <li><strong>Tipo de Gráfico:</strong> {{ ucfirst($analisisItem->tipo_grafico) }}</li>
                                                            <li><strong>Fecha de Análisis:</strong> {{ $analisisItem->fecha_analisis->format('d/m/Y H:i') }}</li>
                                                            <li><strong>Estado:</strong>
                                                                <span class="badge badge-success">{{ ucfirst($analisisItem->estado) }}</span>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Sin Análisis</h5>
                                    No hay análisis disponibles para esta encuesta.
                                    <a href="{{ route('respuestas.index') }}" class="btn btn-primary btn-sm ml-2">
                                        <i class="fas fa-robot"></i>
                                        Generar Análisis
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-cogs"></i>
                                        Información de la Encuesta
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>ID:</strong></td>
                                                    <td>{{ $encuesta->id }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Título:</strong></td>
                                                    <td>{{ $encuesta->titulo }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Empresa:</strong></td>
                                                    <td>{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Estado:</strong></td>
                                                    <td>
                                                        <span class="badge badge-{{ $encuesta->estado === 'publicada' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($encuesta->estado) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Preguntas:</strong></td>
                                                    <td>{{ $encuesta->preguntas->count() }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Respuestas:</strong></td>
                                                    <td>{{ $encuesta->encuestas_respondidas }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Pendientes:</strong></td>
                                                    <td>{{ $encuesta->encuestas_pendientes }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Creada:</strong></td>
                                                    <td>{{ $encuesta->created_at->format('d/m/Y H:i') }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('respuestas.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Análisis
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-secondary">
                        <i class="fas fa-home"></i>
                        Inicio
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chart-container {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}
.analysis-info {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}
.analysis-info h5, .analysis-info h6 {
    color: #495057;
    margin-bottom: 10px;
}
.card-tools .badge {
    font-size: 0.8rem;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    @foreach($analisis as $analisisItem)
        var ctx{{ $analisisItem->id }} = document.getElementById('chart-{{ $analisisItem->id }}').getContext('2d');
        var data{{ $analisisItem->id }} = @json($analisisItem->datos_procesados);
        var config{{ $analisisItem->id }} = @json($analisisItem->configuracion_grafico);

        new Chart(ctx{{ $analisisItem->id }}, {
            type: config{{ $analisisItem->id }}.type || 'bar',
            data: data{{ $analisisItem->id }},
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: '{{ $analisisItem->pregunta->texto }}'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    @endforeach
});
</script>
@endsection
