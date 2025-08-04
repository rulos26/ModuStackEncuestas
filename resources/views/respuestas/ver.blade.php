@extends('layouts.app')

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
                                                    <div class="card bg-light">
                                                        <div class="card-header bg-primary text-white">
                                                            <h5 class="card-title mb-0">
                                                                <i class="fas fa-brain"></i> Análisis de IA
                                                            </h5>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="alert alert-info">
                                                                <p class="mb-0">{{ $analisisItem->analisis_ia }}</p>
                                                            </div>

                                                            <h6 class="text-dark"><i class="fas fa-info-circle"></i> Detalles</h6>
                                                            <ul class="list-unstyled text-dark">
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
/* Configuración específica para gráficas en esta vista */
.chart-container {
    background: #ffffff !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    padding: 20px !important;
    margin-bottom: 20px !important;
    position: relative !important;
    height: 400px !important;
    min-height: 400px !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

body.dark-mode .chart-container {
    background: #343a40 !important;
    border-color: #495057 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3) !important;
}

.chart-container canvas {
    width: 100% !important;
    height: 100% !important;
    border-radius: 4px !important;
}

/* Asegurar que los contenedores de gráficas tengan dimensiones */
.chart-wrapper {
    position: relative !important;
    height: 400px !important;
    width: 100% !important;
    background: transparent !important;
}

/* Estados de carga y error específicos */
.chart-loading {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 400px !important;
    background: #f8f9fa !important;
    border-radius: 8px !important;
    color: #6c757d !important;
    font-size: 16px !important;
    border: 2px dashed #dee2e6 !important;
}

body.dark-mode .chart-loading {
    background: #495057 !important;
    color: #adb5bd !important;
    border-color: #6c757d !important;
}

.chart-error {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 400px !important;
    background: #f8d7da !important;
    border: 1px solid #f5c6cb !important;
    border-radius: 8px !important;
    color: #721c24 !important;
    font-size: 16px !important;
    text-align: center !important;
    padding: 20px !important;
}

body.dark-mode .chart-error {
    background: #2d1b1b !important;
    border-color: #721c24 !important;
    color: #f8d7da !important;
}

.chart-empty {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    height: 400px !important;
    background: #e2e3e5 !important;
    border: 1px solid #d6d8db !important;
    border-radius: 8px !important;
    color: #6c757d !important;
    font-size: 16px !important;
}

body.dark-mode .chart-empty {
    background: #495057 !important;
    border-color: #6c757d !important;
    color: #adb5bd !important;
}

/* Debug styles */
.chart-debug {
    border: 2px solid red !important;
    background: rgba(255, 0, 0, 0.1) !important;
}

.chart-debug::before {
    content: "DEBUG: Chart Container" !important;
    position: absolute !important;
    top: 5px !important;
    left: 5px !important;
    background: red !important;
    color: white !important;
    padding: 2px 5px !important;
    font-size: 10px !important;
    z-index: 1000 !important;
}

/* Asegurar que las gráficas sean visibles */
canvas {
    display: block !important;
    max-width: 100% !important;
    max-height: 100% !important;
}

/* Forzar visibilidad en modo oscuro */
body.dark-mode canvas {
    filter: brightness(1.2) contrast(1.1) !important;
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
        console.log('🎨 Iniciando renderizado para:', canvasId, 'Análisis ID:', analisisId);

        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error('❌ Canvas no encontrado:', canvasId);
            return;
        }

        // Verificar que Chart.js esté disponible
        if (typeof Chart === 'undefined') {
            console.error('❌ Chart.js no está disponible');
            showError(canvasId, 'Chart.js no está disponible');
            return;
        }

        const ctx = canvas.getContext('2d');
        if (!ctx) {
            console.error('❌ No se pudo obtener el contexto 2D');
            showError(canvasId, 'Error al obtener contexto 2D');
            return;
        }

        // Asegurar que el canvas tenga dimensiones
        if (canvas.width === 0 || canvas.height === 0) {
            canvas.width = canvas.offsetWidth || 400;
            canvas.height = canvas.offsetHeight || 400;
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

        // Configuración mejorada con colores dinámicos
        const isDarkMode = document.body.classList.contains('dark-mode') ||
                          (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);

        const chartColors = isDarkMode ? {
            text: '#ffffff',
            grid: 'rgba(255,255,255,0.1)',
            background: '#343a40',
            tooltip: {
                background: 'rgba(255, 255, 255, 0.9)',
                title: '#000000',
                body: '#000000',
                border: '#000000'
            }
        } : {
            text: '#495057',
            grid: 'rgba(0,0,0,0.1)',
            background: '#ffffff',
            tooltip: {
                background: 'rgba(0, 0, 0, 0.8)',
                title: '#ffffff',
                body: '#ffffff',
                border: '#ffffff'
            }
        };

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
                            color: chartColors.text,
                            font: { size: 12 },
                            usePointStyle: true
                        }
                    },
                    title: {
                        display: true,
                        text: '{{ $analisisItem->pregunta->texto }}',
                        color: chartColors.text,
                        font: { size: 16, weight: 'bold' }
                    },
                    tooltip: {
                        backgroundColor: chartColors.tooltip.background,
                        titleColor: chartColors.tooltip.title,
                        bodyColor: chartColors.tooltip.body,
                        borderColor: chartColors.tooltip.border,
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: chartColors.grid },
                        ticks: { color: chartColors.text }
                    },
                    x: {
                        grid: { color: chartColors.grid },
                        ticks: { color: chartColors.text }
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

    // Función para forzar actualización de gráficas
    function forceChartUpdate() {
        if (window.charts) {
            Object.values(window.charts).forEach(chart => {
                if (chart && typeof chart.update === 'function') {
                    chart.update('none');
                }
            });
        }
    }

    // Actualizar gráficas cuando cambie el modo oscuro
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                setTimeout(forceChartUpdate, 100);
            }
        });
    });

    observer.observe(document.body, {
        attributes: true,
        attributeFilter: ['class']
    });

    // También actualizar cuando cambie el tamaño de la ventana
    window.addEventListener('resize', function() {
        setTimeout(forceChartUpdate, 100);
    });

    // Verificar que las gráficas se rendericen correctamente después de un tiempo
    setTimeout(function() {
        console.log('🔍 Verificando estado final de gráficas...');
        if (window.charts) {
            Object.keys(window.charts).forEach(canvasId => {
                const chart = window.charts[canvasId];
                if (chart && chart.canvas) {
                    const rect = chart.canvas.getBoundingClientRect();
                    console.log(`📊 Gráfica ${canvasId}: ${rect.width}x${rect.height}`);
                }
            });
        }
    }, 2000);
});
</script>
@endsection
