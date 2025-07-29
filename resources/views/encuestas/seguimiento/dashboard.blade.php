@extends('adminlte::page')

@section('title', 'Dashboard de Seguimiento')

@section('content_header')
    <h1>
        <i class="fas fa-chart-line"></i> Dashboard de Seguimiento
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Dashboard de Seguimiento</li>
        </ol>
    </nav>

    <!-- ALERTAS -->
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
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    <!-- ESTADÍSTICAS PRINCIPALES -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['total_encuestas'] }}</h3>
                    <p>Total de Encuestas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $estadisticas['encuestas_enviadas'] }}</h3>
                    <p>Encuestas Enviadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-paper-plane"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas['encuestas_pendientes'] }}</h3>
                    <p>Encuestas Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $estadisticas['encuestas_respondidas'] }}</h3>
                    <p>Encuestas Respondidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- PROGRESO DE ENVÍO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tasks"></i> Progreso de Envío
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $estadisticas['estado_encuesta'] === 'enviada' ? 'success' : 'warning' }}">
                            {{ ucfirst($estadisticas['estado_encuesta']) }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: {{ $estadisticas['progreso_porcentaje'] }}%;"
                             aria-valuenow="{{ $estadisticas['progreso_porcentaje'] }}"
                             aria-valuemin="0" aria-valuemax="100">
                            <strong>{{ $estadisticas['progreso_porcentaje'] }}% Completado</strong>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-md-2">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Enviados</span>
                                    <span class="info-box-number">{{ $estadisticas['bloques_enviados'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pendientes</span>
                                    <span class="info-box-number">{{ $estadisticas['bloques_pendientes'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-spinner"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">En Proceso</span>
                                    <span class="info-box-number">{{ $estadisticas['bloques_en_proceso'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Errores</span>
                                    <span class="info-box-number">{{ $estadisticas['bloques_error'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-secondary">
                                <span class="info-box-icon"><i class="fas fa-ban"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cancelados</span>
                                    <span class="info-box-number">{{ $estadisticas['bloques_cancelados'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-envelope"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Correos</span>
                                    <span class="info-box-number">{{ $estadisticas['correos_enviados'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTROLES DE ENVÍO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Controles de Envío
                    </h3>
                </div>
                <div class="card-body">
                    <div class="btn-group" role="group">
                        @if($estadisticas['estado_encuesta'] === 'enviada')
                            <form method="POST" action="{{ route('encuestas.seguimiento.pausar', $encuesta->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-pause"></i> Pausar Envío
                                </button>
                            </form>
                        @endif

                        @if($estadisticas['estado_encuesta'] === 'pausada')
                            <form method="POST" action="{{ route('encuestas.seguimiento.reanudar', $encuesta->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-play"></i> Reanudar Envío
                                </button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('encuestas.seguimiento.cancelar', $encuesta->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de cancelar el envío?')">
                                <i class="fas fa-stop"></i> Cancelar Envío
                            </button>
                        </form>

                        <button type="button" class="btn btn-info" onclick="actualizarDatos()">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BLOQUES DE ENVÍO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Bloques de Envío
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Bloque</th>
                                    <th>Cantidad</th>
                                    <th>Estado</th>
                                    <th>Fecha Programada</th>
                                    <th>Fecha Envío</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bloques as $bloque)
                                    <tr>
                                        <td>{{ $bloque->numero_bloque }}</td>
                                        <td>{{ $bloque->cantidad_correos }}</td>
                                        <td>
                                            @switch($bloque->estado)
                                                @case('enviado')
                                                    <span class="badge badge-success">Enviado</span>
                                                    @break
                                                @case('pendiente')
                                                    <span class="badge badge-warning">Pendiente</span>
                                                    @break
                                                @case('en_proceso')
                                                    <span class="badge badge-info">En Proceso</span>
                                                    @break
                                                @case('error')
                                                    <span class="badge badge-danger">Error</span>
                                                    @break
                                                @case('cancelado')
                                                    <span class="badge badge-secondary">Cancelado</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-light">{{ $bloque->estado }}</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $bloque->fecha_programada ? $bloque->fecha_programada->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                        <td>{{ $bloque->fecha_envio ? $bloque->fecha_envio->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CORREOS ENVIADOS -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-envelope"></i> Correos Enviados (Últimos 50)
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Destinatario</th>
                                    <th>Asunto</th>
                                    <th>Estado</th>
                                    <th>Fecha Envío</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($correosEnviados as $correo)
                                    <tr>
                                        <td>{{ $correo->to }}</td>
                                        <td>{{ $correo->subject }}</td>
                                        <td>
                                            @if($correo->status === 'sent')
                                                <span class="badge badge-success">Enviado</span>
                                            @elseif($correo->status === 'error')
                                                <span class="badge badge-danger">Error</span>
                                            @else
                                                <span class="badge badge-warning">{{ $correo->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $correo->created_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            @if($correo->error_message)
                                                <span class="text-danger">{{ $correo->error_message }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
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
let actualizacionAutomatica;

function actualizarDatos() {
    $.ajax({
        url: '{{ route("encuestas.seguimiento.actualizar", $encuesta->id) }}',
        method: 'GET',
        success: function(response) {
            // Actualizar estadísticas
            actualizarEstadisticas(response.estadisticas);

            // Actualizar bloques
            actualizarBloques(response.bloques);

            // Actualizar correos
            actualizarCorreos(response.correos_enviados);

            // Mostrar timestamp
            mostrarUltimaActualizacion(response.timestamp);
        },
        error: function(xhr) {
            console.error('Error actualizando datos:', xhr.responseText);
        }
    });
}

function actualizarEstadisticas(estadisticas) {
    // Actualizar progreso
    $('.progress-bar').css('width', estadisticas.progreso_porcentaje + '%');
    $('.progress-bar strong').text(estadisticas.progreso_porcentaje + '% Completado');

    // Actualizar contadores
    $('.info-box-number').each(function() {
        const text = $(this).siblings('.info-box-text').text();
        if (text === 'Enviados') $(this).text(estadisticas.bloques_enviados);
        if (text === 'Pendientes') $(this).text(estadisticas.bloques_pendientes);
        if (text === 'En Proceso') $(this).text(estadisticas.bloques_en_proceso);
        if (text === 'Errores') $(this).text(estadisticas.bloques_error);
        if (text === 'Cancelados') $(this).text(estadisticas.bloques_cancelados);
        if (text === 'Correos') $(this).text(estadisticas.correos_enviados);
    });

    // Actualizar estado
    $('.card-tools .badge').text(estadisticas.estado_encuesta.charAt(0).toUpperCase() + estadisticas.estado_encuesta.slice(1));
}

function actualizarBloques(bloques) {
    // Implementar actualización de tabla de bloques
    console.log('Actualizando bloques:', bloques);
}

function actualizarCorreos(correos) {
    // Implementar actualización de tabla de correos
    console.log('Actualizando correos:', correos);
}

function mostrarUltimaActualizacion(timestamp) {
    // Mostrar timestamp de última actualización
    console.log('Última actualización:', timestamp);
}

// Actualización automática cada 30 segundos
$(document).ready(function() {
    actualizacionAutomatica = setInterval(actualizarDatos, 30000);

    // Detener actualización automática cuando se cierre la página
    $(window).on('beforeunload', function() {
        clearInterval(actualizacionAutomatica);
    });
});
</script>
@endsection
