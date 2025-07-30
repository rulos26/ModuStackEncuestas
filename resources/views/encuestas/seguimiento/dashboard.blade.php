@extends('adminlte::page')

@section('title', 'Dashboard de Seguimiento')

@section('content_header')
    <h1>
        <i class="fas fa-chart-line"></i> Dashboard de Seguimiento
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- DEBUG: Mostrar información de debug si está habilitado -->
    @if(config('app.debug'))
        <div class="alert alert-info">
            <h5><i class="fas fa-bug"></i> Modo Debug Activo</h5>
            <p><strong>Encuesta ID:</strong> {{ $encuesta->id }}</p>
            <p><strong>Estado Actual:</strong> {{ $encuesta->estado }}</p>
            <p><strong>User ID:</strong> {{ $encuesta->user_id }}</p>
            <p><strong>Usuario Autenticado:</strong> {{ auth()->id() }}</p>
            <p><strong>¿Coinciden?:</strong> {{ $encuesta->user_id == auth()->id() ? 'Sí' : 'No' }}</p>
            <p><strong>Enviar por correo:</strong> {{ $encuesta->enviar_por_correo ? 'Sí' : 'No' }}</p>
            <p><strong>Envío masivo activado:</strong> {{ $encuesta->envio_masivo_activado ? 'Sí' : 'No' }}</p>
            <p><strong>Validación completada:</strong> {{ $encuesta->validacion_completada ? 'Sí' : 'No' }}</p>
        </div>
    @endif

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

    <!-- CORREOS NO ENVIADOS -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-envelope-open"></i> Correos Pendientes de Envío
                        <span class="badge badge-warning ml-2">{{ count($correosPendientes) }}</span>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="enviarCorreosMasivos()">
                            <i class="fas fa-paper-plane"></i> Enviar Todos
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($correosPendientes) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tablaCorreosPendientes">
                                <thead>
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
                                                <button type="button" class="btn btn-primary btn-sm"
                                                        onclick="enviarCorreoIndividual('{{ $correo['id'] }}', '{{ $correo['email'] }}')">
                                                    <i class="fas fa-paper-plane"></i> Enviar
                                                </button>
                                                <button type="button" class="btn btn-info btn-sm"
                                                        onclick="verDetallesCorreo('{{ $correo['id'] }}')">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- BOTONES DE ACCIÓN MASIVA -->
                        <div class="row mt-3">
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
                                <button type="button" class="btn btn-info" onclick="exportarLista()">
                                    <i class="fas fa-download"></i> Exportar Lista
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="actualizarLista()">
                                    <i class="fas fa-sync-alt"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h4 class="mt-3 text-success">¡Todos los correos han sido enviados!</h4>
                            <p class="text-muted">No hay correos pendientes de envío.</p>
                        </div>
                    @endif
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
        $.ajax({
            url: '{{ route("encuestas.seguimiento.enviar-masivo", $encuesta->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message
                    }).then(() => {
                        location.reload();
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
        $.ajax({
            url: '{{ route("encuestas.seguimiento.enviar-seleccionados", $encuesta->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                correos: seleccionados
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message
                    }).then(() => {
                        location.reload();
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
        $.ajax({
            url: '{{ route("encuestas.seguimiento.enviar-individual", $encuesta->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                correo_id: correoId
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message
                    }).then(() => {
                        location.reload();
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
    location.reload();
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
});
</script>
@endsection
