@extends('adminlte::page')

@section('title', 'Dashboard de Seguimiento')

@section('content_header')
    <h1>
        <i class="fas fa-chart-line"></i> Dashboard de Seguimiento
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Dashboard de Seguimiento</li>
        </ol>
    </nav>

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
        <!-- ESTADÍSTICAS PRINCIPALES -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['total'] }}</h3>
                    <p>Total Encuestas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas['enviadas'] }}</h3>
                    <p>Enviadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas['respondidas'] }}</h3>
                    <p>Respondidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['pendientes'] }}</h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- PROGRESO GENERAL -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tasks"></i> Progreso del Envío
                    </h3>
                </div>
                <div class="card-body">
                    <!-- BARRA DE PROGRESO PRINCIPAL -->
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: {{ $estadisticas['progreso_porcentaje'] }}%;"
                             aria-valuenow="{{ $estadisticas['progreso_porcentaje'] }}"
                             aria-valuemin="0" aria-valuemax="100">
                            <strong>{{ $estadisticas['progreso_porcentaje'] }}%</strong>
                        </div>
                    </div>

                    <!-- ESTADÍSTICAS DETALLADAS -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-layer-group"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Bloques</span>
                                    <span class="info-box-number">{{ $estadisticas['total_bloques'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Enviados</span>
                                    <span class="info-box-number">{{ $estadisticas['bloques_enviados'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-spinner"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">En Proceso</span>
                                    <span class="info-box-number">{{ $estadisticas['bloques_en_proceso'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pendientes</span>
                                    <span class="info-box-number">{{ $estadisticas['bloques_pendientes'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TIEMPO ESTIMADO -->
                    @if($estadisticas['tiempo_estimado_minutos'] > 0)
                        <div class="alert alert-info">
                            <i class="fas fa-clock"></i>
                            <strong>Tiempo estimado restante:</strong> {{ $estadisticas['tiempo_estimado_minutos'] }} minutos
                            @if($estadisticas['siguiente_envio'])
                                <br><small>Próximo envío: {{ $estadisticas['siguiente_envio']->format('H:i:s') }}</small>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- CONTROLES DE ENVÍO -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Controles
                    </h3>
                </div>
                <div class="card-body">
                    @if($encuesta->envioEnProgreso())
                        <form action="{{ route('encuestas.seguimiento.pausar', $encuesta->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-pause"></i> Pausar Envío
                            </button>
                        </form>

                        <form action="{{ route('encuestas.seguimiento.cancelar', $encuesta->id) }}" method="POST"
                              onsubmit="return confirm('¿Estás seguro de que quieres cancelar el envío?')">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-stop"></i> Cancelar Envío
                            </button>
                        </form>
                    @elseif($encuesta->estado === 'borrador' && $encuesta->envio_masivo_activado)
                        <form action="{{ route('encuestas.seguimiento.reanudar', $encuesta->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-play"></i> Reanudar Envío
                            </button>
                        </form>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            El envío está {{ $encuesta->envioCompletado() ? 'completado' : 'pausado' }}.
                        </div>
                    @endif

                    <hr>

                    <a href="{{ route('encuestas.seguimiento.exportar', $encuesta->id) }}" class="btn btn-info btn-block">
                        <i class="fas fa-download"></i> Exportar Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- BLOQUES DE ENVÍO -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-layer-group"></i> Bloques de Envío
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Bloque</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                    <th>Programado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bloques as $bloque)
                                    <tr>
                                        <td>{{ $bloque->numero_bloque }}</td>
                                        <td>{{ $bloque->cantidad_correos }}</td>
                                        <td>
                                            @if($bloque->estado === 'enviado')
                                                <span class="badge badge-success">Enviado</span>
                                            @elseif($bloque->estado === 'en_proceso')
                                                <span class="badge badge-warning">En Proceso</span>
                                            @elseif($bloque->estado === 'error')
                                                <span class="badge badge-danger">Error</span>
                                            @else
                                                <span class="badge badge-secondary">Pendiente</span>
                                            @endif
                                        </td>
                                        <td>{{ $bloque->fecha_programada->format('H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- HISTORIAL DE ENVÍOS -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Historial de Envíos
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Destinatario</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historialEnvio as $envio)
                                    <tr>
                                        <td>{{ $envio->to }}</td>
                                        <td>{{ $envio->created_at->format('H:i:s') }}</td>
                                        <td>
                                            @if($envio->error_message)
                                                <span class="badge badge-danger">Error</span>
                                            @else
                                                <span class="badge badge-success">Enviado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No hay envíos registrados</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Actualizar datos cada 30 segundos
    setInterval(function() {
        actualizarDatos();
    }, 30000);

    function actualizarDatos() {
        $.ajax({
            url: '{{ route("encuestas.seguimiento.actualizar", $encuesta->id) }}',
            method: 'GET',
            success: function(data) {
                // Actualizar estadísticas
                actualizarEstadisticas(data.estadisticas);
                actualizarBloques(data.bloques);
            },
            error: function(xhr) {
                console.error('Error actualizando datos:', xhr);
            }
        });
    }

    function actualizarEstadisticas(stats) {
        // Actualizar barras de progreso
        $('.progress-bar').css('width', stats.progreso_porcentaje + '%');
        $('.progress-bar strong').text(stats.progreso_porcentaje + '%');

        // Actualizar números
        $('.small-box .inner h3').each(function(index) {
            var values = [stats.total, stats.enviadas, stats.respondidas, stats.pendientes];
            if (values[index] !== undefined) {
                $(this).text(values[index]);
            }
        });
    }

    function actualizarBloques(bloques) {
        // Actualizar tabla de bloques
        // Implementar según necesidad
    }
});
</script>
@endsection
