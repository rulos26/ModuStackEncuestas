@extends('adminlte::page')

@section('title', 'Resumen de Configuraciones de Envío')

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
        // Implementar edición
        showInfo('Función de edición en desarrollo');
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
</script>
@endsection
