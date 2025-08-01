@extends('adminlte::page')

@section('title', 'Agregar Respuestas')

@push('css')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content_header')
    <h1>
        <i class="fas fa-list-check"></i> Agregar Respuestas
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- DASHBOARD DE ESTADÍSTICAS -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPreguntas }}</h3>
                    <p>Total de Preguntas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-question-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $preguntasConRespuestas->count() }}</h3>
                    <p>Preguntas con Respuestas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $preguntasSinRespuestas->count() }}</h3>
                    <p>Preguntas sin Respuestas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box {{ $puedeConfigurarLogica ? 'bg-primary' : 'bg-secondary' }}">
                <div class="inner">
                    <h3>{{ $puedeConfigurarLogica ? 'Sí' : 'No' }}</h3>
                    <p>Puede Configurar Lógica</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Agregar Respuestas</li>
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
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    @if($preguntasSinRespuestas->isNotEmpty())
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Preguntas que necesitan respuestas
                </h3>
            </div>
            <div class="card-body">
                @foreach($preguntasSinRespuestas as $pregunta)
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>{{ $loop->iteration }}. {{ $pregunta->texto }}</strong>
                            <span class="badge badge-{{ $pregunta->obligatoria ? 'danger' : 'info' }} ml-2">
                                {{ $pregunta->obligatoria ? 'Obligatoria' : 'Opcional' }}
                            </span>
                            <span class="badge badge-secondary ml-2">
                                {{ $pregunta->getNombreTipo() }}
                            </span>
                            @if(!$pregunta->necesitaRespuestas())
                                <span class="badge badge-warning ml-2">
                                    <i class="fas fa-info-circle"></i> No necesita respuestas
                                </span>
                            @endif
                            <span class="badge badge-secondary ml-2">{{ $pregunta->getNombreTipo() }}</span>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('encuestas.respuestas.store', $encuesta->id) }}" method="POST">
                                @csrf
                                <div class="respuestas-container" data-pregunta-id="{{ $pregunta->id }}">
                                    <div class="respuesta-item mb-2">
                                        <div class="input-group">
                                            <input type="text" name="respuestas[{{ $pregunta->id }}][0][texto]"
                                                   class="form-control" placeholder="Escriba la respuesta" required>
                                            <input type="hidden" name="respuestas[{{ $pregunta->id }}][0][orden]" value="1">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success agregar-respuesta"
                                                        data-pregunta-id="{{ $pregunta->id }}">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2">
                                    <i class="fas fa-save"></i> Guardar Respuestas
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($preguntasConRespuestas->isNotEmpty())
        <div class="card card-info mt-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-check-circle"></i> Preguntas con respuestas configuradas
                </h3>
            </div>
            <div class="card-body">
                @foreach($preguntasConRespuestas as $pregunta)
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>{{ $loop->iteration }}. {{ $pregunta->texto }}</strong>
                            <span class="badge badge-success ml-2">{{ $pregunta->respuestas->count() }} respuestas</span>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($pregunta->respuestas as $respuesta)
                                    <li class="list-group-item">
                                        <i class="fas fa-circle text-primary"></i> {{ $respuesta->texto }}
                                    </li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn btn-warning btn-sm mt-2 editar-respuestas"
                                    data-pregunta-id="{{ $pregunta->id }}">
                                <i class="fas fa-edit"></i> Editar Respuestas
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- BOTONES DE NAVEGACIÓN -->
    <div class="mt-4">
        @if($puedeConfigurarLogica)
            <a href="{{ route('encuestas.logica.create', $encuesta->id) }}" class="btn btn-primary">
                <i class="fas fa-cogs"></i> Configurar Lógica
            </a>
        @else
            <button type="button" class="btn btn-secondary" disabled title="Primero completa todas las respuestas">
                <i class="fas fa-cogs"></i> Configurar Lógica
            </button>
        @endif

        <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info">
            <i class="fas fa-arrow-left"></i> Volver a la Encuesta
        </a>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Agregar nueva respuesta
    $('.agregar-respuesta').click(function() {
        var preguntaId = $(this).data('pregunta-id');
        var container = $('.respuestas-container[data-pregunta-id="' + preguntaId + '"]');
        var itemCount = container.find('.respuesta-item').length;

        var newItem = `
            <div class="respuesta-item mb-2">
                <div class="input-group">
                    <input type="text" name="respuestas[${preguntaId}][${itemCount}][texto]"
                           class="form-control" placeholder="Escriba la respuesta" required>
                    <input type="hidden" name="respuestas[${preguntaId}][${itemCount}][orden]" value="${itemCount + 1}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger eliminar-respuesta">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.append(newItem);
    });

    // Eliminar respuesta
    $(document).on('click', '.eliminar-respuesta', function() {
        $(this).closest('.respuesta-item').remove();
    });

    // Editar respuestas
    $('.editar-respuestas').click(function() {
        var preguntaId = $(this).data('pregunta-id');
        var preguntaTexto = $(this).closest('.card').find('.card-header strong').text();

        // Mostrar modal de edición
        mostrarModalEdicion(preguntaId, preguntaTexto);
    });
});

// Función para mostrar modal de edición
function mostrarModalEdicion(preguntaId, preguntaTexto) {
    // Crear modal dinámicamente
    var modalHtml = `
        <div class="modal fade" id="modalEditarRespuestas" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit"></i> Editar Respuestas
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form id="formEditarRespuestas" method="POST" action="{{ route('encuestas.respuestas.editar', ':preguntaId') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label><strong>Pregunta:</strong></label>
                                <p class="form-control-static">${preguntaTexto}</p>
                            </div>
                            <div class="form-group">
                                <label><strong>Respuestas:</strong></label>
                                <div id="respuestasContainer">
                                    <!-- Las respuestas se cargarán aquí -->
                                </div>
                                <button type="button" class="btn btn-success btn-sm mt-2" id="agregarNuevaRespuesta">
                                    <i class="fas fa-plus"></i> Agregar Respuesta
                                </button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;

    // Remover modal anterior si existe
    $('#modalEditarRespuestas').remove();

        // Agregar nuevo modal al body
    $('body').append(modalHtml);

    // Actualizar la acción del formulario con el ID correcto
    $('#formEditarRespuestas').attr('action', "{{ route('encuestas.respuestas.editar', ':preguntaId') }}".replace(':preguntaId', preguntaId));

    // Cargar respuestas actuales
    cargarRespuestasActuales(preguntaId);

    // Mostrar modal
    $('#modalEditarRespuestas').modal('show');
}

// Función para cargar respuestas actuales
function cargarRespuestasActuales(preguntaId) {
    $.ajax({
        url: "{{ route('encuestas.respuestas.obtener', ':preguntaId') }}".replace(':preguntaId', preguntaId),
        method: 'GET',
        success: function(response) {
            var container = $('#respuestasContainer');
            container.empty();

            response.respuestas.forEach(function(respuesta, index) {
                var respuestaHtml = `
                    <div class="respuesta-item mb-2">
                        <div class="input-group">
                            <input type="text" name="respuestas[${index}][texto]"
                                   class="form-control" value="${respuesta.texto}" required>
                            <input type="hidden" name="respuestas[${index}][id]" value="${respuesta.id}">
                            <input type="hidden" name="respuestas[${index}][orden]" value="${respuesta.orden}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-danger eliminar-respuesta-modal">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                container.append(respuestaHtml);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            alert('Error al cargar las respuestas: ' + (xhr.responseJSON?.error || error));
        }
    });
}

// Eventos del modal
$(document).on('click', '#agregarNuevaRespuesta', function() {
    var container = $('#respuestasContainer');
    var itemCount = container.find('.respuesta-item').length;

    var newItem = `
        <div class="respuesta-item mb-2">
            <div class="input-group">
                <input type="text" name="respuestas[${itemCount}][texto]"
                       class="form-control" placeholder="Escriba la respuesta" required>
                <input type="hidden" name="respuestas[${itemCount}][orden]" value="${itemCount + 1}">
                <div class="input-group-append">
                    <button type="button" class="btn btn-danger eliminar-respuesta-modal">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    container.append(newItem);
});

$(document).on('click', '.eliminar-respuesta-modal', function() {
    var respuestaItem = $(this).closest('.respuesta-item');
    var respuestaTexto = respuestaItem.find('input[name*="[texto]"]').val();

    if (confirm('¿Está seguro de que desea eliminar la respuesta "' + respuestaTexto + '"?')) {
        // Ocultar en lugar de eliminar para que no se envíe
        respuestaItem.hide();
        respuestaItem.addClass('eliminada');

        console.log('🗑️ Respuesta ocultada:', respuestaTexto);
    }
});

// Manejar envío del formulario de edición
$(document).on('submit', '#formEditarRespuestas', function(e) {
    e.preventDefault();

    // Solo enviar respuestas que están visibles (no eliminadas)
    var respuestasVisibles = $('#respuestasContainer .respuesta-item:visible');
    if (respuestasVisibles.length === 0) {
        alert('Debe agregar al menos una respuesta');
        return;
    }

    // Crear FormData solo con respuestas visibles
    var formData = new FormData();
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

    respuestasVisibles.each(function(index) {
        var inputs = $(this).find('input');
        inputs.each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            if (name && value !== undefined) {
                // Ajustar el índice para que sea secuencial
                var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                formData.append(newName, value);
            }
        });
    });

    var url = $(this).attr('action');

    console.log('🔧 Enviando datos de edición:');
    console.log('URL:', url);
    console.log('Respuestas visibles:', respuestasVisibles.length);

    // Mostrar datos que se van a enviar
    for (var pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    // Mostrar indicador de carga
    $('#formEditarRespuestas button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

    $.ajax({
        url: url,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('✅ Respuesta exitosa:', response);

            if (response.success) {
                $('#modalEditarRespuestas').modal('hide');

                // Mostrar mensaje de éxito con detalles
                var mensaje = 'Respuestas actualizadas exitosamente\n';
                if (response.data) {
                    mensaje += '• Actualizadas: ' + response.data.actualizadas + '\n';
                    mensaje += '• Creadas: ' + response.data.creadas + '\n';
                    mensaje += '• Eliminadas: ' + response.data.eliminadas;
                }
                alert(mensaje);

                // Recargar página para mostrar cambios
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            console.error('❌ Error al guardar:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseJSON);
            console.error('ResponseText:', xhr.responseText);

            // Habilitar botón nuevamente
            $('#formEditarRespuestas button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Cambios');

            var errorMsg = 'Error al guardar los cambios';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg += ': ' + xhr.responseJSON.error;
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg += ': ' + xhr.responseJSON.message;
            } else {
                errorMsg += ': ' + error;
            }

            alert(errorMsg);
        }
    });
});
</script>
@endsection
