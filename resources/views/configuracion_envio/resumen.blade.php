@extends('adminlte::page')

@section('title', 'Resumen de Configuraciones de Envío')

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@push('css')
<style>
    /* Mejorar legibilidad de la tabla en tema oscuro */
    .table {
        color: #ffffff !important;
    }

    .table thead th {
        background-color: #343a40 !important;
        color: #ffffff !important;
        border-color: #495057 !important;
    }

    .table tbody td {
        background-color: #2d3748 !important;
        color: #ffffff !important;
        border-color: #495057 !important;
    }

    .table tbody tr:hover {
        background-color: #3a4149 !important;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #2d3748 !important;
    }

    .table-striped tbody tr:nth-of-type(even) {
        background-color: #343a40 !important;
    }

    /* Mejorar legibilidad de badges */
    .badge {
        font-weight: 600;
    }

    /* Mejorar legibilidad de enlaces */
    .text-info {
        color: #17a2b8 !important;
    }

    .text-light {
        color: #f8f9fa !important;
    }

    .text-white {
        color: #ffffff !important;
    }

    /* Mejorar botones de acción */
    .btn-group .btn {
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    /* Mejorar hover de botones */
    .btn-info:hover {
        background-color: #138496 !important;
        border-color: #117a8b !important;
    }

    .btn-warning:hover {
        background-color: #e0a800 !important;
        border-color: #d39e00 !important;
    }

    .btn-danger:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
    }

    .btn-success:hover {
        background-color: #218838 !important;
        border-color: #1e7e34 !important;
    }

    /* Estilos para el modal de destinatarios */
    .modal-content {
        background-color: #2d3748 !important;
        color: #ffffff !important;
        border-color: #495057 !important;
    }

    .modal-header {
        background-color: #343a40 !important;
        border-bottom-color: #495057 !important;
    }

    .modal-footer {
        background-color: #343a40 !important;
        border-top-color: #495057 !important;
    }

    .empleados-list {
        background-color: #2d3748 !important;
        border: 1px solid #495057 !important;
        border-radius: 5px;
        padding: 15px;
    }

    .custom-control-label {
        color: #ffffff !important;
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #007bff !important;
        border-color: #007bff !important;
    }

    .form-control {
        background-color: #495057 !important;
        border-color: #6c757d !important;
        color: #ffffff !important;
    }

    .form-control:focus {
        background-color: #495057 !important;
        border-color: #007bff !important;
        color: #ffffff !important;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
    }
</style>
@endpush

@section('content_header')
    <h1>
        <i class="fas fa-list"></i> Resumen de Configuraciones de Envío
        <small>{{ $empresa->nombre }}</small>
    </h1>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Información de la empresa -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> Empresa: {{ $empresa->nombre }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('configuracion-envio.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Nueva Configuración
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Configuraciones Activas</span>
                                    <span class="info-box-number">{{ $configuraciones->where('activo', true)->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-pause"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Configuraciones Inactivas</span>
                                    <span class="info-box-number">{{ $configuraciones->where('activo', false)->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-envelope"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Configuraciones</span>
                                    <span class="info-box-number">{{ $configuraciones->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Última Actualización</span>
                                    <span class="info-box-number">{{ $configuraciones->max('updated_at') ? $configuraciones->max('updated_at')->format('d/m/Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if($configuraciones->isNotEmpty())
                <!-- Lista de configuraciones -->
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Configuraciones de Envío
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Encuesta</th>
                                        <th>Remitente</th>
                                        <th>Correo</th>
                                        <th>Asunto</th>
                                        <th>Tipo de Envío</th>
                                        <th>Estado</th>
                                        <th>Última Actualización</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($configuraciones as $configuracion)
                                        <tr>
                                            <td>
                                                <strong class="text-white">{{ $configuracion->encuesta->titulo }}</strong>
                                                <br>
                                                <small class="text-light">{{ $configuracion->encuesta->estado }}</small>
                                                @if(isset($configuracion->destinatarios_info))
                                                    <br>
                                                    <small class="text-info">
                                                        <i class="fas fa-users"></i>
                                                        {{ $configuracion->destinatarios_info['total'] }} destinatarios
                                                        ({{ ucfirst($configuracion->destinatarios_info['tipo']) }})
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-white">{{ $configuracion->nombre_remitente }}</td>
                                            <td>
                                                <a href="mailto:{{ $configuracion->correo_remitente }}" class="text-info">
                                                    {{ $configuracion->correo_remitente }}
                                                </a>
                                            </td>
                                            <td class="text-white">
                                                <span title="{{ $configuracion->asunto }}">
                                                    {{ Str::limit($configuracion->asunto, 50) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $configuracion->tipo_envio === 'automatico' ? 'success' : ($configuracion->tipo_envio === 'manual' ? 'info' : 'warning') }} text-white">
                                                    {{ ucfirst($configuracion->tipo_envio) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $configuracion->activo ? 'success' : 'danger' }} text-white">
                                                    {{ $configuracion->activo ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-light">{{ $configuracion->updated_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-info"
                                                            onclick="verDetalles({{ $configuracion->id }})"
                                                            title="Ver detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                            onclick="editarConfiguracion({{ $configuracion->id }})"
                                                            title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <!-- Debug: tipo_envio = {{ $configuracion->tipo_envio }} -->
                                                    @if($configuracion->tipo_envio === 'programado')
                                                    <button type="button" class="btn btn-sm btn-success"
                                                            onclick="configurarDestinatarios({{ $configuracion->id }})"
                                                            title="Configurar Destinatarios">
                                                        <i class="fas fa-users"></i>
                                                    </button>
                                                    @endif
                                                    <!-- Botón de debug - siempre visible -->
                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                            onclick="configurarDestinatarios({{ $configuracion->id }})"
                                                            title="Configurar Destinatarios (Debug)">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-{{ $configuracion->activo ? 'danger' : 'success' }}"
                                                            onclick="toggleEstado({{ $configuracion->id }}, {{ $configuracion->activo ? 'false' : 'true' }})"
                                                            title="{{ $configuracion->activo ? 'Desactivar' : 'Activar' }}">
                                                        <i class="fas fa-{{ $configuracion->activo ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <!-- Sin configuraciones -->
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle"></i> Sin Configuraciones
                        </h3>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                        <h4>No hay configuraciones de envío</h4>
                        <p class="text-muted">
                            No se han encontrado configuraciones de envío de correos para esta empresa.
                        </p>
                        <a href="{{ route('configuracion-envio.index') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Crear Primera Configuración
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Destinatarios -->
<div class="modal fade" id="destinatariosModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users"></i> Configurar Destinatarios
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="destinatarios-content">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="guardarDestinatarios()">
                    <i class="fas fa-save"></i> Guardar Configuración
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Detalles de la Configuración
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalles-content">
                <!-- El contenido se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edición -->
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i> Editar Configuración
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="editar-content">
                <!-- El formulario se cargará dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-guardar-edicion">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    margin-bottom: 20px;
}

.table th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.btn-group .btn {
    margin-right: 2px;
}

.modal-lg {
    max-width: 800px;
}

/* Estilos para modal en tema oscuro */
.modal-content {
    background-color: #2d3748 !important;
    color: #ffffff !important;
    border-color: #495057 !important;
}

.modal-header {
    background-color: #343a40 !important;
    border-bottom-color: #495057 !important;
}

.modal-header .modal-title {
    color: #ffffff !important;
}

.modal-header .close {
    color: #ffffff !important;
}

.modal-body {
    background-color: #2d3748 !important;
    color: #ffffff !important;
}

.modal-footer {
    background-color: #343a40 !important;
    border-top-color: #495057 !important;
}

.detalles-item {
    margin-bottom: 15px;
    padding: 15px;
    background-color: #3a4149 !important;
    border-radius: 5px;
    border: 1px solid #495057 !important;
}

.detalles-item strong {
    color: #ffffff !important;
    font-weight: 600;
}

.detalles-item {
    color: #f8f9fa !important;
}

.detalles-item a {
    color: #17a2b8 !important;
    text-decoration: none;
}

.detalles-item a:hover {
    color: #138496 !important;
    text-decoration: underline;
}

.cuerpo-mensaje {
    background-color: #3a4149 !important;
    border: 1px solid #495057 !important;
    border-radius: 5px;
    padding: 15px;
    white-space: pre-wrap;
    font-family: inherit;
    max-height: 200px;
    overflow-y: auto;
    color: #f8f9fa !important;
}

/* Mejorar badges en modal */
.modal .badge {
    font-weight: 600;
}

.modal .badge.text-white {
    color: #ffffff !important;
}

/* Mejorar botones en modal */
.modal .btn-secondary {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: #ffffff !important;
}

    .modal .btn-secondary:hover {
        background-color: #5a6268 !important;
        border-color: #545b62 !important;
    }

    /* Mejorar scrollbar en modal */
    .modal .cuerpo-mensaje::-webkit-scrollbar {
        width: 8px;
    }

    .modal .cuerpo-mensaje::-webkit-scrollbar-track {
        background: #3a4149;
    }

    .modal .cuerpo-mensaje::-webkit-scrollbar-thumb {
        background: #6c757d;
        border-radius: 4px;
    }

    .modal .cuerpo-mensaje::-webkit-scrollbar-thumb:hover {
        background: #5a6268;
    }

    /* Mejorar hover de elementos en modal */
    .modal .detalles-item:hover {
        background-color: #4a5159 !important;
    }

    /* Mejorar legibilidad de fechas */
    .modal .detalles-item small {
        color: #adb5bd !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funciones globales
    window.verDetalles = function(configuracionId) {
        fetch(`{{ route('configuracion-envio.get-configuracion') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                encuesta_id: configuracionId,
                empresa_id: {{ $empresa->id }}
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                mostrarDetalles(data.data);
            } else {
                showError('No se pudo cargar los detalles');
            }
        })
        .catch(error => {
            showError('Error de conexión: ' + error.message);
        });
    };

    window.editarConfiguracion = function(configuracionId) {
        // Redirigir a la página de edición
        window.location.href = `{{ route('configuracion-envio.editar', ['id' => ':id']) }}`.replace(':id', configuracionId);
    };

    window.toggleEstado = function(configuracionId, nuevoEstado) {
        const accion = nuevoEstado ? 'activar' : 'desactivar';

        if (confirm(`¿Está seguro de que desea ${accion} esta configuración?`)) {
            // Implementar cambio de estado
            showInfo('Función de cambio de estado en desarrollo');
        }
    };

    function mostrarDetalles(configuracion) {
        const content = document.getElementById('detalles-content');
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Encuesta:</strong><br>
                        ${configuracion.encuesta ? configuracion.encuesta.titulo : 'N/A'}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Estado:</strong><br>
                        <span class="badge badge-${configuracion.activo ? 'success' : 'danger'} text-white">
                            ${configuracion.activo ? 'Activo' : 'Inactivo'}
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Nombre del Remitente:</strong><br>
                        ${configuracion.nombre_remitente}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Correo del Remitente:</strong><br>
                        <a href="mailto:${configuracion.correo_remitente}">${configuracion.correo_remitente}</a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="detalles-item">
                        <strong>Asunto:</strong><br>
                        ${configuracion.asunto}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Tipo de Envío:</strong><br>
                        <span class="badge badge-${configuracion.tipo_envio === 'automatico' ? 'success' : (configuracion.tipo_envio === 'manual' ? 'info' : 'warning')} text-white">
                            ${configuracion.tipo_envio.charAt(0).toUpperCase() + configuracion.tipo_envio.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Plantilla:</strong><br>
                        ${configuracion.plantilla || 'Sin plantilla'}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Destinatarios:</strong><br>
                        ${configuracion.destinatarios_info ?
                            `${configuracion.destinatarios_info.total} ${configuracion.destinatarios_info.tipo}` :
                            'No especificado'}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Programación:</strong><br>
                        ${configuracion.tipo_envio === 'programado' ?
                            `Fecha: ${configuracion.fecha_envio}<br>Hora: ${configuracion.hora_envio}` :
                            'Envío manual'}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="detalles-item">
                        <strong>Cuerpo del Mensaje:</strong><br>
                        <div class="cuerpo-mensaje">${configuracion.cuerpo_mensaje}</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Creado:</strong><br>
                        ${new Date(configuracion.created_at).toLocaleString()}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="detalles-item">
                        <strong>Última Actualización:</strong><br>
                        ${new Date(configuracion.updated_at).toLocaleString()}
                    </div>
                </div>
            </div>
        `;

        $('#detallesModal').modal('show');
    };

    function showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: message,
                timer: 3000
            });
        } else {
            alert('Éxito: ' + message);
        }
    }

    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        } else {
            alert('Error: ' + message);
        }
    }

    function showInfo(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Información',
                text: message
            });
        } else {
            alert('Info: ' + message);
        }
    }

});

// Funciones de notificación (fuera del document.ready para acceso global)
function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        alert('Éxito: ' + message);
    }
}

function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            timer: 5000,
            showConfirmButton: true
        });
    } else {
        alert('Error: ' + message);
    }
}

function showWarning(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: message,
            timer: 4000,
            showConfirmButton: false
        });
    } else {
        alert('Atención: ' + message);
    }
}

// Función para configurar destinatarios (fuera del document.ready para acceso global)
function configurarDestinatarios(configuracionId) {
    console.log('Configurando destinatarios para configuración:', configuracionId);

    // Mostrar loading
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Cargando empleados...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

            // Cargar empleados de la empresa
        $.get(`{{ route('configuracion-envio.obtener-empleados', ['id' => ':id']) }}`.replace(':id', configuracionId), function(response) {
        console.log('Respuesta del servidor:', response);

        if (typeof Swal !== 'undefined') {
            Swal.close();
        }

        if (response.success) {
            console.log('Empresa:', response.configuracion.empresa_nombre);
            console.log('Empleados encontrados:', response.empleados.length);
            mostrarModalDestinatarios(configuracionId, response.empleados, response.configuracion);
        } else {
            showError('Error al cargar empleados: ' + response.message);
        }
    }).fail(function(xhr, status, error) {
        console.error('Error en la petición:', error);
        if (typeof Swal !== 'undefined') {
            Swal.close();
        }
        showError('Error al cargar empleados: ' + error);
    });
}

// Función para mostrar el modal de destinatarios
function mostrarModalDestinatarios(configuracionId, empleados, configuracion) {
        console.log('Mostrando modal con:', {configuracionId, empleados, configuracion});

        let empleadosHtml = '';

        empleados.forEach(function(empleado) {
            const isSelected = configuracion.destinatarios_seleccionados &&
                             configuracion.destinatarios_seleccionados.includes(empleado.id);

            empleadosHtml += `
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input"
                           id="empleado_${empleado.id}"
                           value="${empleado.id}"
                           ${isSelected ? 'checked' : ''}>
                    <label class="custom-control-label" for="empleado_${empleado.id}">
                        <strong>${empleado.nombre}</strong><br>
                        <small class="text-muted">${empleado.correo_electronico}</small>
                    </label>
                </div>
            `;
        });

        const modalContent = `
            <div class="row">
                <div class="col-md-12">
                    <h6><i class="fas fa-building"></i> Empresa: ${configuracion.empresa_nombre}</h6>
                    <h6><i class="fas fa-poll"></i> Encuesta: ${configuracion.encuesta_titulo}</h6>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <h6><i class="fas fa-users"></i> Seleccionar Destinatarios</h6>
                    <p class="text-muted">Selecciona los empleados que recibirán el correo:</p>

                    <div class="empleados-list" style="max-height: 300px; overflow-y: auto;">
                        ${empleadosHtml}
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="seleccionarTodos()">
                            <i class="fas fa-check-double"></i> Seleccionar Todos
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deseleccionarTodos()">
                            <i class="fas fa-times"></i> Deseleccionar Todos
                        </button>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha de Envío</label>
                        <input type="date" class="form-control" id="fecha_envio"
                               value="${configuracion.fecha_envio || ''}"
                               min="${new Date().toISOString().split('T')[0]}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Hora de Envío</label>
                        <input type="time" class="form-control" id="hora_envio"
                               value="${configuracion.hora_envio || '09:00'}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-layer-group"></i> Número de Bloques</label>
                        <input type="number" class="form-control" id="numero_bloques"
                               value="${configuracion.numero_bloques || 1}" min="1" max="10">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label><i class="fas fa-vial"></i> Correo de Prueba</label>
                        <input type="email" class="form-control" id="correo_prueba"
                               value="${configuracion.correo_prueba || ''}"
                               placeholder="correo@ejemplo.com">
                    </div>
                </div>
            </div>
        `;

        $('#destinatarios-content').html(modalContent);
        // Guardar el ID de configuración en el modal para usarlo al guardar
        $('#destinatariosModal').data('configuracion-id', configuracionId);
        $('#destinatariosModal').modal('show');
    }

// Funciones para seleccionar/deseleccionar todos (fuera del document.ready para acceso global)
function seleccionarTodos() {
    $('.empleados-list input[type="checkbox"]').prop('checked', true);
}

function deseleccionarTodos() {
    $('.empleados-list input[type="checkbox"]').prop('checked', false);
}

// Función para guardar la configuración de destinatarios
function guardarDestinatarios() {
        // Recuperar el ID de configuración guardado en el modal
        const configuracionId = $('#destinatariosModal').data('configuracion-id');
        const empleadosSeleccionados = [];

        $('.empleados-list input[type="checkbox"]:checked').each(function() {
            empleadosSeleccionados.push($(this).val());
        });

        const datos = {
            configuracion_id: configuracionId,
            empleados: empleadosSeleccionados,
            fecha_envio: $('#fecha_envio').val(),
            hora_envio: $('#hora_envio').val(),
            numero_bloques: $('#numero_bloques').val(),
            correo_prueba: $('#correo_prueba').val(),
            _token: '{{ csrf_token() }}'
        };

        $.post('{{ route("configuracion-envio.guardar-destinatarios") }}', datos, function(response) {
            if (response.success) {
                showSuccess('Destinatarios configurados correctamente');
                $('#destinatariosModal').modal('hide');
                // Recargar la página para mostrar los cambios
                setTimeout(function() {
                    location.reload();
                }, 1500);
            } else {
                showError('Error: ' + response.message);
            }
        }).fail(function() {
            showError('Error al guardar destinatarios');
        });
    }
</script>
@endsection
