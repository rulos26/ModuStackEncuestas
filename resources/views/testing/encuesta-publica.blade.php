@extends('adminlte::page')

@section('title', 'Pruebas - Encuesta Pública')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-vote-yea"></i>
                        Pruebas - Encuesta Pública
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">ID: 13</span>
                        <span class="badge badge-secondary">Slug: encuesta-de-prueba-tester-automatico-2025-07-30-194743</span>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Información de la Encuesta -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-info-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Encuesta de Prueba</span>
                                    <span class="info-box-number">ID: 13</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Slug: encuesta-de-prueba-tester-automatico-2025-07-30-194743
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-link"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">URL de Prueba</span>
                                    <span class="info-box-number">Pública</span>
                                    <div class="progress">
                                        <div class="progress-bar bg-success" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Sin autenticación requerida
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de Pruebas -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-cog"></i>
                                        Configuración de Prueba
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <form id="pruebaForm">
                                        <div class="form-group">
                                            <label for="encuesta_id">ID de Encuesta:</label>
                                            <input type="number" class="form-control" id="encuesta_id" name="encuesta_id" value="13" min="1">
                                            <small class="form-text text-muted">ID de la encuesta a probar</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="slug_encuesta">Slug de Encuesta:</label>
                                            <input type="text" class="form-control" id="slug_encuesta" name="slug_encuesta" value="encuesta-de-prueba-tester-automatico-2025-07-30-194743">
                                            <small class="form-text text-muted">Slug de la encuesta (URL amigable)</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="tipo_prueba">Tipo de Prueba:</label>
                                            <select class="form-control" id="tipo_prueba" name="tipo_prueba">
                                                <option value="mostrar">Mostrar Encuesta (GET)</option>
                                                <option value="responder">Responder Encuesta (POST)</option>
                                                <option value="fin">Página de Fin (GET)</option>
                                                <option value="debug">Debug Completo</option>
                                                <option value="vista_publica">Probar Vista Pública (Sin Token)</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label for="user_agent">User Agent:</label>
                                            <input type="text" class="form-control" id="user_agent" name="user_agent" value="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36">
                                            <small class="form-text text-muted">User Agent para simular</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="ip_address">IP Address:</label>
                                            <input type="text" class="form-control" id="ip_address" name="ip_address" value="127.0.0.1">
                                            <small class="form-text text-muted">IP para simular</small>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-play"></i>
                                            Ejecutar Prueba
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-success">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-external-link-alt"></i>
                                        Enlaces Directos
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="btn-group-vertical w-100">
                                        <a href="/publica/encuesta-de-prueba-tester-automatico-2025-07-30-194743"
                                           target="_blank"
                                           class="btn btn-success mb-2">
                                            <i class="fas fa-eye"></i>
                                            Ver Encuesta Pública
                                        </a>

                                        <a href="/publica/encuesta-de-prueba-tester-automatico-2025-07-30-194743/fin"
                                           target="_blank"
                                           class="btn btn-info mb-2">
                                            <i class="fas fa-check-circle"></i>
                                            Página de Fin
                                        </a>

                                        <button type="button"
                                                class="btn btn-warning mb-2"
                                                onclick="probarDebug()">
                                            <i class="fas fa-bug"></i>
                                            Debug Completo
                                        </button>

                                        <button type="button"
                                                class="btn btn-secondary mb-2"
                                                onclick="verLogs()">
                                            <i class="fas fa-file-alt"></i>
                                            Ver Logs
                                        </button>

                                        <button type="button"
                                                class="btn btn-danger mb-2"
                                                onclick="probarVistaPublica()">
                                            <i class="fas fa-eye"></i>
                                            Probar Vista Pública
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resultados de la Prueba -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-chart-line"></i>
                                        Resultados de la Prueba
                                    </h3>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" onclick="limpiarResultados()">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="resultados" class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        Ejecuta una prueba para ver los resultados aquí...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar logs -->
<div class="modal fade" id="logsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-alt"></i>
                    Logs de Encuesta Pública
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="logsContent" style="max-height: 400px; overflow-y: auto;">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i>
                        Cargando logs...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="actualizarLogs()">Actualizar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
// Funciones optimizadas
function ejecutarPrueba() {
    const datos = {
        encuesta_id: $('#encuesta_id').val(),
        slug_encuesta: $('#slug_encuesta').val(),
        tipo_prueba: $('#tipo_prueba').val(),
        user_agent: $('#user_agent').val(),
        ip_address: $('#ip_address').val(),
        _token: '{{ csrf_token() }}'
    };

    $('#resultados').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Ejecutando prueba...</p>
        </div>
    `);

    $.ajax({
        url: '{{ route("testing.encuesta-publica") }}',
        method: 'POST',
        data: datos,
        success: function(response) {
            mostrarResultados(response);
        },
        error: function(xhr) {
            mostrarError('Error en la petición: ' + xhr.responseText);
        }
    });
}

function mostrarResultados(data) {
    let html = `
        <div class="alert alert-success">
            <h5><i class="fas fa-check-circle"></i> Prueba Completada</h5>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong>Encuesta ID:</strong> ${data.encuesta_id}<br>
                    <strong>Slug:</strong> ${data.slug}<br>
                    <strong>Tipo:</strong> ${data.tipo_prueba}<br>
                    <strong>Estado:</strong> <span class="badge badge-success">${data.estado}</span>
                </div>
                <div class="col-md-6">
                    <strong>Tiempo:</strong> ${data.tiempo}ms<br>
                    <strong>URL:</strong> <a href="${data.url}" target="_blank">${data.url}</a><br>
                    <strong>Logs:</strong> ${data.logs_count} entradas
                </div>
            </div>
            <hr>
            <pre class="bg-dark text-light p-3 rounded">${data.detalles}</pre>
        </div>
    `;
    $('#resultados').html(html);
}

function mostrarError(mensaje) {
    $('#resultados').html(`
        <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-triangle"></i> Error</h5>
            <p>${mensaje}</p>
        </div>
    `);
}

function limpiarResultados() {
    $('#resultados').html(`
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Ejecuta una prueba para ver los resultados aquí...
        </div>
    `);
}

function probarDebug() {
    $('#tipo_prueba').val('debug');
    $('#pruebaForm').submit();
}

function verLogs() {
    $('#logsModal').modal('show');
    actualizarLogs();
}

function actualizarLogs() {
    $('#logsContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin"></i>
            Cargando logs...
        </div>
    `);

    $.ajax({
        url: '{{ route("testing.encuesta-publica-logs") }}',
        method: 'GET',
        success: function(response) {
            $('#logsContent').html(`
                <pre class="bg-dark text-light p-3 rounded" style="font-size: 12px;">${response.logs}</pre>
            `);
        },
        error: function() {
            $('#logsContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    Error al cargar los logs
                </div>
            `);
        }
    });
}

function probarVistaPublica() {
    const encuestaId = $('#encuesta_id').val();
    const slug = $('#slug_encuesta').val();

    $('#resultados').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Preparando vista pública...</p>
        </div>
    `);

    $.ajax({
        url: '{{ route("testing.encuesta-publica") }}',
        method: 'POST',
        data: {
            encuesta_id: encuestaId,
            slug_encuesta: slug,
            tipo_prueba: 'vista_publica',
            user_agent: $('#user_agent').val(),
            ip_address: $('#ip_address').val(),
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.estado === 'completado') {
                const urlVista = response.url_vista;
                window.open(urlVista, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                mostrarResultados(response);
            } else {
                mostrarError('Error: ' + response.detalles);
            }
        },
        error: function(xhr) {
            mostrarError('Error en la petición: ' + xhr.responseText);
        }
    });
}

// Inicialización
$(document).ready(function() {
    $('#pruebaForm').on('submit', function(e) {
        e.preventDefault();
        ejecutarPrueba();
    });
});
</script>
@endpush
