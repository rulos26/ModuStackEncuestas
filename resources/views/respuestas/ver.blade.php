@extends('adminlte::page')

@section('title', 'Análisis de Respuestas - ' . $encuesta->titulo)

@section('css')
<link rel="stylesheet" href="{{ asset('css/charts.css') }}">
@endsection

@section('content_header')
    <h1>
        <i class="fas fa-chart-bar"></i> Análisis de Respuestas
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

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
                                                <span class="badge badge-{{ $analisisItem->tipo_grafico === 'pastel' ? 'success' : ($analisisItem->tipo_grafico === 'barras' ? 'primary' : 'info') }}">
                                                    <i class="fas fa-{{ $analisisItem->tipo_grafico === 'pastel' ? 'chart-pie' : ($analisisItem->tipo_grafico === 'lineas' ? 'chart-line' : 'chart-bar') }}"></i>
                                                    {{ ucfirst($analisisItem->tipo_grafico) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="chart-wrapper">
                                                        <div class="chart-container" style="position: relative; height:400px; width:100%;">
                                                            <canvas id="chart-{{ $analisisItem->id }}" width="400" height="400"></canvas>
                                                        </div>
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
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
    height: 400px;
    min-height: 400px;
}

.chart-container canvas {
    width: 100% !important;
    height: 100% !important;
}

.analysis-info {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.analysis-info h5, .analysis-info h6 {
    color: #495057;
    margin-bottom: 10px;
}

.card-tools .badge {
    font-size: 0.8rem;
}

/* Asegurar que los contenedores de gráficas tengan dimensiones */
.chart-wrapper {
    position: relative;
    height: 400px;
    width: 100%;
}

/* Estilos para diferentes tipos de gráficas */
.chart-container.bar-chart {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.chart-container.pie-chart {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.chart-container.line-chart {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

/* Debug styles */
.chart-debug {
    border: 2px solid red;
    background: rgba(255, 0, 0, 0.1);
}

.chart-debug::before {
    content: "DEBUG: Chart Container";
    position: absolute;
    top: 5px;
    left: 5px;
    background: red;
    color: white;
    padding: 2px 5px;
    font-size: 10px;
    z-index: 1000;
}
</style>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Iniciando renderizado de gráficas...');

    // Función para mostrar estado de carga
    function showLoading(canvasId) {
        const container = document.getElementById(canvasId).parentElement;
        container.innerHTML = '<div class="chart-loading"></div>';
    }

    // Función para mostrar error
    function showError(canvasId, message) {
        const container = document.getElementById(canvasId).parentElement;
        container.innerHTML = `<div class="chart-error">${message}</div>`;
    }

    // Función para mostrar estado vacío
    function showEmpty(canvasId) {
        const container = document.getElementById(canvasId).parentElement;
        container.innerHTML = '<div class="chart-empty"></div>';
    }

    @foreach($analisis as $analisisItem)
        console.log('📊 Procesando gráfica para análisis ID: {{ $analisisItem->id }}');

        const canvasId = 'chart-{{ $analisisItem->id }}';
        const canvas = document.getElementById(canvasId);

        if (!canvas) {
            console.error('❌ No se encontró el canvas:', canvasId);
            continue;
        }

        // Verificar que el canvas esté visible
        const rect = canvas.getBoundingClientRect();
        if (rect.width === 0 || rect.height === 0) {
            console.warn('⚠️ Canvas no visible, esperando...');
            setTimeout(() => {
                renderChart(canvasId, {{ $analisisItem->id }});
            }, 1000);
            continue;
        }

        renderChart(canvasId, {{ $analisisItem->id }});
    @endforeach

    function renderChart(canvasId, analisisId) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error('❌ Canvas no encontrado:', canvasId);
            return;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            console.error('❌ No se pudo obtener el contexto 2D');
            showError(canvasId, 'Error al obtener contexto 2D');
            return;
        }

        // Obtener datos del análisis
        let data, config;

        @foreach($analisis as $analisisItem)
        if (analisisId === {{ $analisisItem->id }}) {
            data = @json($analisisItem->datos_procesados);
            config = @json($analisisItem->configuracion_grafico);
        }
        @endforeach

        if (!data || !data.labels || !data.datasets) {
            console.error('❌ Datos de gráfica inválidos para análisis ID:', analisisId);
            showEmpty(canvasId);
            return;
        }

        // Validar que hay datos para mostrar
        const hasData = data.datasets.some(dataset =>
            dataset.data && dataset.data.length > 0 &&
            dataset.data.some(value => value > 0)
        );

        if (!hasData) {
            console.warn('⚠️ No hay datos válidos para mostrar');
            showEmpty(canvasId);
            return;
        }

        // Configuración mejorada
        const chartConfig = {
            type: config?.type || 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#495057',
                            font: { size: 12 },
                            usePointStyle: true
                        }
                    },
                    title: {
                        display: true,
                        text: '{{ $analisisItem->pregunta->texto }}',
                        color: '#495057',
                        font: { size: 16, weight: 'bold' }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#ffffff',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.1)' },
                        ticks: { color: '#495057' }
                    },
                    x: {
                        grid: { color: 'rgba(0,0,0,0.1)' },
                        ticks: { color: '#495057' }
                    }
                },
                elements: {
                    point: { radius: 4 },
                    line: { tension: 0.4 },
                    bar: { borderWidth: 1 }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        };

        // Agregar configuración específica según el tipo
        if (chartConfig.type === 'pie' || chartConfig.type === 'doughnut') {
            chartConfig.options.plugins.legend.position = 'bottom';
            chartConfig.options.plugins.legend.labels.usePointStyle = true;
        }

        console.log('🎨 Configuración final:', chartConfig);

        try {
            // Destruir gráfica existente si existe
            if (window.charts && window.charts[canvasId]) {
                window.charts[canvasId].destroy();
            }

            // Crear la gráfica
            const chart = new Chart(ctx, chartConfig);

            // Guardar referencia global
            if (!window.charts) window.charts = {};
            window.charts[canvasId] = chart;

            console.log('✅ Gráfica creada exitosamente para análisis ID:', analisisId);

            // Agregar clase CSS para debugging en desarrollo
            if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
                canvas.parentElement.classList.add('chart-debug');
            }

        } catch (error) {
            console.error('❌ Error creando gráfica para análisis ID:', analisisId, error);
            showError(canvasId, `Error: ${error.message}`);
        }
    }

    console.log('🎉 Renderizado de gráficas completado');
});
</script>
@endsection
