@extends('adminlte::page')

@section('title', 'Dashboard de Seguimiento')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-chart-line text-primary"></i> Dashboard de Seguimiento
                <small class="text-muted">{{ $encuesta->titulo }}</small>
            </h1>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge badge-{{ $estadisticas['estado_encuesta'] === 'enviada' ? 'success' : 'warning' }} badge-lg mr-2">
                {{ ucfirst($estadisticas['estado_encuesta']) }}
            </span>
            <button type="button" class="btn btn-outline-info btn-sm" onclick="actualizarDatos()" title="Actualizar datos">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
@endsection

@section('content')
    <!-- DEBUG: Mostrar información de debug si está habilitado -->
    @if(config('app.debug'))
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="fas fa-bug"></i> Modo Debug Activo</h5>
            <div class="row">
                <div class="col-md-3">
                    <strong>Encuesta ID:</strong> {{ $encuesta->id }}
                </div>
                <div class="col-md-3">
                    <strong>Estado:</strong> {{ $encuesta->estado }}
                </div>
                <div class="col-md-3">
                    <strong>User ID:</strong> {{ $encuesta->user_id }}
                </div>
                <div class="col-md-3">
                    <strong>Usuario:</strong> {{ auth()->id() }}
                </div>
            </div>
        </div>
    @endif

    <!-- BREADCRUMBS -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb bg-transparent p-0">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}" class="text-decoration-none">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}" class="text-decoration-none">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Dashboard de Seguimiento</li>
        </ol>
    </nav>

    <!-- ALERTAS -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    <!-- RESUMEN EJECUTIVO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <i class="fas fa-tasks mr-2"></i>
                                Resumen de Envío
                            </h4>
                            <p class="mb-0">Monitoreo en tiempo real del progreso de envío de encuestas</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <div class="progress bg-white bg-opacity-25" style="height: 8px;">
                                <div class="progress-bar bg-white" role="progressbar"
                                     style="width: {{ $estadisticas['progreso_porcentaje'] }}%">
                                </div>
                            </div>
                            <small class="mt-2 d-block">{{ $estadisticas['progreso_porcentaje'] }}% Completado</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ESTADÍSTICAS PRINCIPALES -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="bg-info bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-clipboard-list fa-2x text-info"></i>
                        </div>
                    </div>
                    <h3 class="text-info mb-1">{{ $estadisticas['total_encuestas'] }}</h3>
                    <p class="text-muted mb-0">Total de Encuestas</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-paper-plane fa-2x text-success"></i>
                        </div>
                    </div>
                    <h3 class="text-success mb-1">{{ $estadisticas['encuestas_enviadas'] }}</h3>
                    <p class="text-muted mb-0">Encuestas Enviadas</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-clock fa-2x text-warning"></i>
                        </div>
                    </div>
                    <h3 class="text-warning mb-1">{{ $estadisticas['encuestas_pendientes'] }}</h3>
                    <p class="text-muted mb-0">Encuestas Pendientes</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                            <i class="fas fa-check-circle fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h3 class="text-primary mb-1">{{ $estadisticas['encuestas_respondidas'] }}</h3>
                    <p class="text-muted mb-0">Encuestas Respondidas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- PROGRESO DETALLADO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks text-primary"></i> Progreso de Envío
                    </h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-4" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar"
                             style="width: {{ $estadisticas['progreso_porcentaje'] }}%"
                             aria-valuenow="{{ $estadisticas['progreso_porcentaje'] }}"
                             aria-valuemin="0" aria-valuemax="100">
                            <strong class="text-white">{{ $estadisticas['progreso_porcentaje'] }}% Completado</strong>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-check fa-2x text-success mb-2"></i>
                                <h5 class="text-success mb-1">{{ $estadisticas['bloques_enviados'] }}</h5>
                                <small class="text-muted">Enviados</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                                <h5 class="text-warning mb-1">{{ $estadisticas['bloques_pendientes'] }}</h5>
                                <small class="text-muted">Pendientes</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-spinner fa-2x text-info mb-2"></i>
                                <h5 class="text-info mb-1">{{ $estadisticas['bloques_en_proceso'] }}</h5>
                                <small class="text-muted">En Proceso</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="bg-danger bg-opacity-10 p-3 rounded">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                                <h5 class="text-danger mb-1">{{ $estadisticas['bloques_error'] }}</h5>
                                <small class="text-muted">Errores</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="bg-secondary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-ban fa-2x text-secondary mb-2"></i>
                                <h5 class="text-secondary mb-1">{{ $estadisticas['bloques_cancelados'] }}</h5>
                                <small class="text-muted">Cancelados</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                <h5 class="text-primary mb-1">{{ $estadisticas['correos_enviados'] }}</h5>
                                <small class="text-muted">Correos</small>
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs text-primary"></i> Controles de Envío
                    </h5>
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

                        <!-- Botón de prueba para verificar funcionalidad -->
                        <button type="button" class="btn btn-secondary" onclick="probarFuncionalidad()" title="Probar funcionalidad de botones">
                            <i class="fas fa-vial"></i> Probar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CORREOS PENDIENTES -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope-open text-primary"></i> Correos Pendientes de Envío
                        <span class="badge badge-warning ml-2">{{ count($correosPendientes) }}</span>
                    </h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success btn-sm" onclick="enviarCorreosMasivos()">
                            <i class="fas fa-paper-plane"></i> Enviar Todos
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="exportarLista()">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($correosPendientes) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="tablaCorreosPendientes">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%">
                                            <input type="checkbox" id="seleccionarTodos" onchange="seleccionarTodosCorreos()">
                                        </th>
                                        <th width="25%">Destinatario</th>
                                        <th width="20%">Email</th>
                                        <th width="15%">Tipo</th>
                                        <th width="15%">Estado</th>
                                        <th width="20%">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($correosPendientes as $correo)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="correo-checkbox" value="{{ $correo['id'] }}">
                                            </td>
                                            <td>
                                                <strong>{{ $correo['nombre'] ?? $correo['email'] }}</strong>
                                                @if($correo['cargo'])
                                                    <br><small class="text-muted">{{ $correo['cargo'] }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <i class="fas fa-envelope text-info"></i>
                                                {{ $correo['email'] }}
                                            </td>
                                            <td>
                                                @if($correo['tipo'] === 'empleado')
                                                    <span class="badge badge-info">Empleado</span>
                                                @else
                                                    <span class="badge badge-primary">Usuario</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">Pendiente</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                            onclick="enviarCorreoIndividual('{{ $correo['id'] }}', '{{ $correo['email'] }}')"
                                                            title="Enviar correo individual">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-info btn-sm"
                                                            onclick="verDetallesCorreo('{{ $correo['id'] }}')"
                                                            title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- BOTONES DE ACCIÓN MASIVA -->
                        <div class="row mt-3 botones-accion-masiva">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success" onclick="enviarCorreosSeleccionados()">
                                    <i class="fas fa-paper-plane"></i> Enviar Seleccionados
                                    <span class="badge badge-light" id="contadorSeleccionados">0</span>
                                </button>
                                <button type="button" class="btn btn-warning" onclick="programarEnvio()">
                                    <i class="fas fa-clock"></i> Programar Envío
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-secondary" onclick="actualizarLista()">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5 mensaje-no-correos">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-success">¡Todos los correos han sido enviados!</h4>
                            <p class="text-muted">No hay correos pendientes de envío.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- BLOQUES DE ENVÍO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list text-primary"></i> Bloques de Envío
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
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
                                        <td><strong>#{{ $bloque->numero_bloque }}</strong></td>
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
                                        <td>{{ $bloque->fecha_programada ? $bloque->fecha_programada->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>{{ $bloque->fecha_envio ? $bloque->fecha_envio->format('d/m/Y H:i') : 'N/A' }}</td>
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope text-primary"></i> Correos Enviados (Últimos 50)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="thead-light">
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
                                        <td>{{ $correo->created_at->format('d/m/Y H:i') }}</td>
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

    <!-- MODAL PARA DETALLES DE CORREO -->
    <div class="modal fade" id="modalDetallesCorreo" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-envelope"></i> Detalles del Correo
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalDetallesCorreoBody">
                    <!-- Contenido dinámico -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnEnviarDesdeModal">
                        <i class="fas fa-paper-plane"></i> Enviar Correo
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/dashboard-improvements.css') }}">
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.progress-bar {
    transition: width 0.6s ease;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.bg-opacity-10 {
    background-color: rgba(var(--bs-primary-rgb), 0.1) !important;
}

.bg-opacity-25 {
    background-color: rgba(255, 255, 255, 0.25) !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group .btn {
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.rounded-circle {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

/* Animaciones */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }

    .btn-group .btn {
        margin-bottom: 0.25rem;
        margin-right: 0;
    }
}
</style>
@endsection

@section('js')
<script src="{{ asset('js/dashboard-enhancements.js') }}"></script>
<script>
let actualizacionAutomatica;

function actualizarDatos() {
    // Mostrar indicador de carga
    mostrarIndicadorCarga();

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

            // Ocultar indicador de carga
            ocultarIndicadorCarga();

            // Mostrar notificación de éxito
            mostrarNotificacion('Datos actualizados correctamente', 'success');
        },
        error: function(xhr) {
            console.error('Error actualizando datos:', xhr.responseText);
            ocultarIndicadorCarga();
            mostrarNotificacion('Error al actualizar datos', 'error');
        }
    });
}

function actualizarEstadisticas(estadisticas) {
    // Actualizar progreso con animación
    $('.progress-bar').css('width', estadisticas.progreso_porcentaje + '%');
    $('.progress-bar strong').text(estadisticas.progreso_porcentaje + '% Completado');

    // Actualizar contadores con animación
    $('.card-body h3').each(function() {
        const $this = $(this);
        const newValue = estadisticas[getEstadisticaKey($this)];
        if (newValue !== undefined) {
            animateCounter($this, newValue);
        }
    });

    // Actualizar estado
    $('.badge-lg').text(estadisticas.estado_encuesta.charAt(0).toUpperCase() + estadisticas.estado_encuesta.slice(1));
}

function getEstadisticaKey($element) {
    const text = $element.siblings('p').text();
    if (text.includes('Total')) return 'total_encuestas';
    if (text.includes('Enviadas')) return 'encuestas_enviadas';
    if (text.includes('Pendientes')) return 'encuestas_pendientes';
    if (text.includes('Respondidas')) return 'encuestas_respondidas';
}

function animateCounter($element, newValue) {
    const currentValue = parseInt($element.text());
    const increment = (newValue - currentValue) / 20;
    let current = currentValue;

    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= newValue) || (increment < 0 && current <= newValue)) {
            $element.text(newValue);
            clearInterval(timer);
        } else {
            $element.text(Math.floor(current));
        }
    }, 50);
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

function mostrarIndicadorCarga() {
    // Mostrar spinner en el botón de actualizar
    $('button[onclick="actualizarDatos()"] i').removeClass('fa-sync-alt').addClass('fa-spinner fa-spin');
}

function ocultarIndicadorCarga() {
    // Restaurar icono del botón
    $('button[onclick="actualizarDatos()"] i').removeClass('fa-spinner fa-spin').addClass('fa-sync-alt');
}

function mostrarNotificacion(mensaje, tipo) {
    const icon = tipo === 'success' ? 'fas fa-check' : 'fas fa-exclamation-triangle';
    const bgClass = tipo === 'success' ? 'bg-success' : 'bg-danger';

    const notificacion = $(`
        <div class="alert ${bgClass} alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="${icon}"></i> ${mensaje}
        </div>
    `);

    $('body').append(notificacion);

    // Auto-ocultar después de 3 segundos
    setTimeout(function() {
        notificacion.fadeOut();
    }, 3000);
}

// FUNCIONES PARA CORREOS PENDIENTES
function seleccionarTodosCorreos() {
    const checked = $('#seleccionarTodos').is(':checked');
    $('.correo-checkbox').prop('checked', checked);
    actualizarContadorSeleccionados();
}

function actualizarContadorSeleccionados() {
    const seleccionados = $('.correo-checkbox:checked').length;
    $('#contadorSeleccionados').text(seleccionados);
}

function enviarCorreosMasivos() {
    if (confirm('¿Estás seguro de enviar todos los correos pendientes?')) {
        mostrarLoading('Enviando todos los correos...');

        $.ajax({
            url: '{{ route("encuestas.seguimiento.enviar-masivo", $encuesta->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                ocultarLoading();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message
                    }).then(() => {
                        actualizarTablaCorreosPendientes();
                        actualizarEstadisticas();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                ocultarLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al enviar correos masivos'
                });
            }
        });
    }
}

function enviarCorreosSeleccionados() {
    const seleccionados = $('.correo-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (seleccionados.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Por favor selecciona al menos un correo para enviar'
        });
        return;
    }

    if (confirm(`¿Estás seguro de enviar ${seleccionados.length} correos seleccionados?`)) {
        mostrarLoading(`Enviando ${seleccionados.length} correos...`);

        $.ajax({
            url: '{{ route("encuestas.seguimiento.enviar-seleccionados", $encuesta->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                correos: seleccionados
            },
            success: function(response) {
                ocultarLoading();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message
                    }).then(() => {
                        actualizarTablaCorreosPendientes();
                        actualizarEstadisticas();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                ocultarLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al enviar correos seleccionados'
                });
            }
        });
    }
}

function enviarCorreoIndividual(correoId, email) {
    if (confirm(`¿Estás seguro de enviar el correo a ${email}?`)) {
        mostrarLoading(`Enviando correo a ${email}...`);

        $.ajax({
            url: '{{ route("encuestas.seguimiento.enviar-individual", $encuesta->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                correo_id: correoId
            },
            success: function(response) {
                ocultarLoading();
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message
                    }).then(() => {
                        actualizarTablaCorreosPendientes();
                        actualizarEstadisticas();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                ocultarLoading();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al enviar correo individual'
                });
            }
        });
    }
}

function verDetallesCorreo(correoId) {
    $.ajax({
        url: '{{ route("encuestas.seguimiento.detalles-correo", $encuesta->id) }}',
        method: 'GET',
        data: {
            correo_id: correoId
        },
        success: function(response) {
            $('#modalDetallesCorreoBody').html(response.html);
            $('#modalDetallesCorreo').modal('show');

            // Configurar botón de envío desde modal
            $('#btnEnviarDesdeModal').off('click').on('click', function() {
                enviarCorreoIndividual(correoId, response.correo.email);
                $('#modalDetallesCorreo').modal('hide');
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar detalles del correo'
            });
        }
    });
}

function actualizarTablaCorreosPendientes() {
    $.ajax({
        url: '{{ route("encuestas.seguimiento.actualizar-correos-pendientes", $encuesta->id) }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Actualizar la tabla
                $('#tablaCorreosPendientes tbody').html(response.html);

                // Actualizar contador
                $('.card-header .badge').text(response.total_pendientes);

                // Actualizar botones de acción masiva
                if (response.total_pendientes === 0) {
                    $('.btn-success, .btn-warning').prop('disabled', true);
                    $('#tablaCorreosPendientes').hide();
                    $('.botones-accion-masiva').hide();
                    $('.mensaje-no-correos').show();
                } else {
                    $('.btn-success, .btn-warning').prop('disabled', false);
                    $('#tablaCorreosPendientes').show();
                    $('.botones-accion-masiva').show();
                    $('.mensaje-no-correos').hide();
                }

                // Reinicializar event listeners
                $('.correo-checkbox').off('change').on('change', actualizarContadorSeleccionados);
                $('#seleccionarTodos').off('change').on('change', seleccionarTodosCorreos);

                // Mostrar notificación de actualización
                mostrarNotificacionActualizacion();
            }
        },
        error: function(xhr) {
            console.error('Error actualizando tabla de correos pendientes:', xhr.responseText);
        }
    });
}

function actualizarEstadisticas() {
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
            console.error('Error actualizando estadísticas:', xhr.responseText);
        }
    });
}

function mostrarLoading(mensaje) {
    Swal.fire({
        title: mensaje,
        html: '<div class="spinner-border text-primary" role="status"><span class="sr-only">Cargando...</span></div>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false
    });
}

function ocultarLoading() {
    Swal.close();
}

function mostrarNotificacionActualizacion() {
    // Mostrar notificación sutil de actualización
    const notificacion = $('<div class="alert alert-info alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">' +
        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
        '<i class="fas fa-sync-alt"></i> Tabla actualizada automáticamente' +
        '</div>');

    $('body').append(notificacion);

    // Auto-ocultar después de 3 segundos
    setTimeout(function() {
        notificacion.fadeOut();
    }, 3000);
}

function programarEnvio() {
    Swal.fire({
        title: 'Programar Envío',
        html: `
            <div class="form-group">
                <label>Fecha y Hora de Envío:</label>
                <input type="datetime-local" id="fechaProgramada" class="form-control">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Programar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const fecha = document.getElementById('fechaProgramada').value;
            if (!fecha) {
                Swal.showValidationMessage('Por favor selecciona una fecha y hora');
                return false;
            }
            return fecha;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementar lógica de programación
            console.log('Fecha programada:', result.value);
        }
    });
}

function exportarLista() {
    const seleccionados = $('.correo-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    $.ajax({
        url: '{{ route("encuestas.seguimiento.exportar-lista", $encuesta->id) }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            correos: seleccionados
        },
        success: function(response) {
            if (response.success) {
                // Descargar archivo
                const link = document.createElement('a');
                link.href = response.download_url;
                link.download = response.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al exportar lista'
            });
        }
    });
}

function actualizarLista() {
    mostrarLoading('Actualizando lista de correos...');
    actualizarTablaCorreosPendientes();
    setTimeout(() => {
        ocultarLoading();
    }, 1000);
}

function probarFuncionalidad() {
    Swal.fire({
        title: 'Prueba de Funcionalidad',
        html: `
            <div class="text-left">
                <p><strong>Selecciona qué funcionalidad quieres probar:</strong></p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="testOption" id="testIndividual" value="individual" checked>
                    <label class="form-check-label" for="testIndividual">
                        Envío Individual
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="testOption" id="testMasivo" value="masivo">
                    <label class="form-check-label" for="testMasivo">
                        Envío Masivo
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="testOption" id="testSeleccionados" value="seleccionados">
                    <label class="form-check-label" for="testSeleccionados">
                        Envío Seleccionados
                    </label>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Probar',
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            const selectedOption = document.querySelector('input[name="testOption"]:checked').value;
            return selectedOption;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const option = result.value;

            switch(option) {
                case 'individual':
                    probarEnvioIndividual();
                    break;
                case 'masivo':
                    probarEnvioMasivo();
                    break;
                case 'seleccionados':
                    probarEnvioSeleccionados();
                    break;
            }
        }
    });
}

function probarEnvioIndividual() {
    // Obtener el primer correo pendiente
    const $firstRow = $('#tablaCorreosPendientes tbody tr:first');
    if ($firstRow.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay correos pendientes',
            text: 'No hay correos disponibles para enviar individualmente.'
        });
        return;
    }

    const correoId = $firstRow.find('.correo-checkbox').val();
    const email = $firstRow.find('td:nth-child(3)').text().trim();

    Swal.fire({
        title: 'Probar Envío Individual',
        text: `¿Enviar correo de prueba a ${email}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarLoading('Enviando correo individual...');

            // Simular envío
            setTimeout(() => {
                ocultarLoading();
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: `Correo enviado exitosamente a ${email}`
                });

                // Actualizar la tabla
                actualizarTablaCorreosPendientes();
            }, 2000);
        }
    });
}

function probarEnvioMasivo() {
    const totalPendientes = $('#tablaCorreosPendientes tbody tr').length;

    if (totalPendientes === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay correos pendientes',
            text: 'No hay correos disponibles para envío masivo.'
        });
        return;
    }

    Swal.fire({
        title: 'Probar Envío Masivo',
        text: `¿Enviar ${totalPendientes} correos de prueba?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar todos',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarLoading(`Enviando ${totalPendientes} correos...`);

            // Simular envío masivo
            setTimeout(() => {
                ocultarLoading();
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: `${totalPendientes} correos enviados exitosamente`
                });

                // Actualizar la tabla
                actualizarTablaCorreosPendientes();
            }, 3000);
        }
    });
}

function probarEnvioSeleccionados() {
    const seleccionados = $('.correo-checkbox:checked').length;

    if (seleccionados === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No hay correos seleccionados',
            text: 'Por favor selecciona al menos un correo para enviar.'
        });
        return;
    }

    Swal.fire({
        title: 'Probar Envío Seleccionados',
        text: `¿Enviar ${seleccionados} correos seleccionados?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar seleccionados',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            mostrarLoading(`Enviando ${seleccionados} correos seleccionados...`);

            // Simular envío de seleccionados
            setTimeout(() => {
                ocultarLoading();
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: `${seleccionados} correos seleccionados enviados exitosamente`
                });

                // Actualizar la tabla
                actualizarTablaCorreosPendientes();
            }, 2500);
        }
    });
}

// Event listeners
$(document).ready(function() {
    // Actualizar contador de seleccionados cuando cambien los checkboxes
    $('.correo-checkbox').on('change', actualizarContadorSeleccionados);

    // Actualización automática cada 30 segundos
    actualizacionAutomatica = setInterval(actualizarDatos, 30000);

    // Detener actualización automática cuando se cierre la página
    $(window).on('beforeunload', function() {
        clearInterval(actualizacionAutomatica);
    });

    // Tooltips
    $('[title]').tooltip();
});
</script>
@endsection
