@extends('adminlte::page')

@section('title', 'Configurar Lógica de Encuesta')

@section('content_header')
    <h1>
        <i class="fas fa-cogs"></i> Configurar Lógica de Encuesta
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Configurar Lógica</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL FLUJO -->
    <div class="progress mb-4" style="height: 25px;">
        <div class="progress-bar bg-success" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <strong>1. Crear Encuesta ✓</strong>
        </div>
        <div class="progress-bar bg-success" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <strong>2. Agregar Preguntas ✓</strong>
        </div>
        <div class="progress-bar bg-success" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <strong>3. Configurar Respuestas ✓</strong>
        </div>
        <div class="progress-bar bg-primary" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <strong>4. Configurar Lógica</strong>
        </div>
    </div>

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

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h5>
            {{ session('warning') }}
        </div>
    @endif

    <div class="row">
        <!-- PANEL IZQUIERDO: CONFIGURACIÓN DE LÓGICA -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-route"></i> Configurar Saltos Lógicos
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">{{ $preguntas->count() }} preguntas</span>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('encuestas.logica.store', $encuestaId) }}" id="logicaForm">
                        @csrf

                        @foreach($preguntas as $pregunta)
                            @if($pregunta->respuestas->isNotEmpty() && $pregunta->permiteLogica())
                                <div class="card mb-3 border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-question-circle"></i>
                                            Pregunta {{ $pregunta->orden }}: {{ $pregunta->texto }}
                                            <span class="badge badge-light ml-2">{{ $pregunta->getNombreTipo() }}</span>
                                        </h5>
                                    </div>
                            @elseif($pregunta->respuestas->isNotEmpty() && !$pregunta->permiteLogica())
                                <div class="card mb-3 border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h5 class="mb-0">
                                            <i class="fas fa-info-circle"></i>
                                            Pregunta {{ $pregunta->orden }}: {{ $pregunta->texto }}
                                            <span class="badge badge-light ml-2">{{ $pregunta->getNombreTipo() }}</span>
                                            <span class="badge badge-warning ml-2">No permite lógica</span>
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Información:</strong> Las preguntas de tipo "{{ $pregunta->getNombreTipo() }}" no permiten configuración de lógica condicional porque son de entrada de datos libre.
                                        </div>
                                    </div>
                                </div>
                            @endif
                                    <div class="card-body">
                                        @foreach($pregunta->respuestas as $respuesta)
                                            <div class="row mb-3 align-items-center">
                                                <div class="col-md-4">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">
                                                                <i class="fas fa-arrow-right"></i>
                                                            </span>
                                                        </div>
                                                        <input type="text" class="form-control" value="{{ $respuesta->texto }}" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <select name="logicas[{{ $pregunta->id }}_{{ $respuesta->id }}][siguiente_pregunta_id]"
                                                            class="form-control logica-select"
                                                            data-pregunta="{{ $pregunta->id }}"
                                                            data-respuesta="{{ $respuesta->id }}">
                                                        <option value="">-- Continuar secuencialmente --</option>
                                                        @foreach($preguntas as $destino)
                                                            @if($destino->id != $pregunta->id)
                                                                <option value="{{ $destino->id }}">{{ $destino->orden }}. {{ $destino->texto }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               name="logicas[{{ $pregunta->id }}_{{ $respuesta->id }}][finalizar]"
                                                               value="1"
                                                               class="form-check-input finalizar-checkbox"
                                                               data-pregunta="{{ $pregunta->id }}"
                                                               data-respuesta="{{ $respuesta->id }}">
                                                        <label class="form-check-label">
                                                            <i class="fas fa-stop-circle"></i> Finalizar
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Campos ocultos -->
                                                <input type="hidden" name="logicas[{{ $pregunta->id }}_{{ $respuesta->id }}][pregunta_id]" value="{{ $pregunta->id }}">
                                                <input type="hidden" name="logicas[{{ $pregunta->id }}_{{ $respuesta->id }}][respuesta_id]" value="{{ $respuesta->id }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Guardar Lógica
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- PANEL DERECHO: RELACIÓN DINÁMICA -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-project-diagram"></i> Relación Dinámica
                    </h3>
                </div>
                <div class="card-body">
                    <div id="relacionDinamica">
                        <div class="text-muted text-center">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p>Configure la lógica para ver las relaciones aquí</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL DE AYUDA -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-arrow-right text-primary"></i> <strong>Continuar secuencialmente:</strong> Sigue al siguiente paso</li>
                        <li><i class="fas fa-route text-success"></i> <strong>Saltar a pregunta:</strong> Va directamente a esa pregunta</li>
                        <li><i class="fas fa-stop-circle text-danger"></i> <strong>Finalizar:</strong> Termina la encuesta</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Actualizar relación dinámica
    function actualizarRelacionDinamica() {
        var relaciones = [];

        $('.logica-select').each(function() {
            var preguntaId = $(this).data('pregunta');
            var respuestaId = $(this).data('respuesta');
            var siguientePreguntaId = $(this).val();
            var finalizar = $('.finalizar-checkbox[data-pregunta="' + preguntaId + '"][data-respuesta="' + respuestaId + '"]').is(':checked');

            if (siguientePreguntaId || finalizar) {
                var preguntaTexto = $(this).closest('.card-header').find('h5').text().replace(/Pregunta \d+: /, '');
                var respuestaTexto = $(this).closest('.row').find('input[readonly]').val();

                var accion = finalizar ? 'Finalizar encuesta' :
                            (siguientePreguntaId ? 'Ir a pregunta ' + siguientePreguntaId : 'Continuar secuencialmente');

                relaciones.push({
                    pregunta: preguntaTexto,
                    respuesta: respuestaTexto,
                    accion: accion
                });
            }
        });

        // Actualizar panel de relación dinámica
        var html = '';
        if (relaciones.length > 0) {
            html = '<div class="list-group">';
            relaciones.forEach(function(relacion) {
                html += '<div class="list-group-item">';
                html += '<div class="d-flex justify-content-between align-items-center">';
                html += '<div><strong>' + relacion.pregunta + '</strong></div>';
                html += '<span class="badge badge-primary">' + relacion.respuesta + '</span>';
                html += '</div>';
                html += '<small class="text-muted">' + relacion.accion + '</small>';
                html += '</div>';
            });
            html += '</div>';
        } else {
            html = '<div class="text-muted text-center"><i class="fas fa-info-circle"></i><p>No hay relaciones configuradas</p></div>';
        }

        $('#relacionDinamica').html(html);
    }

    // Eventos para actualizar relación dinámica
    $('.logica-select, .finalizar-checkbox').on('change', actualizarRelacionDinamica);

    // Deshabilitar select cuando se marca finalizar
    $('.finalizar-checkbox').on('change', function() {
        var preguntaId = $(this).data('pregunta');
        var respuestaId = $(this).data('respuesta');
        var select = $('.logica-select[data-pregunta="' + preguntaId + '"][data-respuesta="' + respuestaId + '"]');

        if ($(this).is(':checked')) {
            select.val('').prop('disabled', true);
        } else {
            select.prop('disabled', false);
        }

        actualizarRelacionDinamica();
    });

    // Validación del formulario
    $('#logicaForm').on('submit', function(e) {
        var tieneConfiguracion = false;

        $('.logica-select').each(function() {
            if ($(this).val() || $(this).closest('.row').find('.finalizar-checkbox').is(':checked')) {
                tieneConfiguracion = true;
            }
        });

        if (!tieneConfiguracion) {
            e.preventDefault();
            alert('Debe configurar al menos una lógica antes de continuar.');
            return false;
        }
    });

    // Inicializar relación dinámica
    actualizarRelacionDinamica();
});
</script>
@endsection
