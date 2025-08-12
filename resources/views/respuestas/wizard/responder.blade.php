@extends('adminlte::page')

@section('title', 'Configurar Respuestas - Pregunta ' . ($preguntaIndex + 1))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- PROGRESO DEL WIZARD -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width: {{ (($preguntaIndex + 1) / $totalPreguntas) * 100 }}%;"
                             aria-valuenow="{{ $preguntaIndex + 1 }}" aria-valuemin="0" aria-valuemax="{{ $totalPreguntas }}">
                            <strong><i class="fas fa-cogs"></i> Pregunta {{ $preguntaIndex + 1 }} de {{ $totalPreguntas }}</strong>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Progreso: {{ round((($preguntaIndex + 1) / $totalPreguntas) * 100) }}% completado
                        </small>
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN DE LA ENCUESTA -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-poll"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Encuesta</span>
                            <span class="info-box-number">{{ $encuesta->titulo }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                ID: {{ $encuesta->id }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pregunta Actual</span>
                            <span class="info-box-number">{{ $preguntaIndex + 1 }} / {{ $totalPreguntas }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $preguntaActual->tipo === 'seleccion_unica' ? 'Selección Única' : 'Casillas de Verificación' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-cogs"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas Configuradas</span>
                            <span class="info-box-number">{{ Session::get('wizard_respuestas_count', 0) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                En esta sesión
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sesión Activa</span>
                            <span class="info-box-number">{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $encuesta->estado }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO PRINCIPAL -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Configurar Respuestas para Pregunta
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            <i class="fas fa-info-circle"></i> Uso Administrativo
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- INFORMACIÓN DE LA PREGUNTA -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-question-circle"></i> Pregunta a Configurar:</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-1"><strong>{{ $preguntaActual->pregunta }}</strong></p>
                                @if($preguntaActual->descripcion)
                                    <p class="mb-0 text-muted"><small>{{ $preguntaActual->descripcion }}</small></p>
                                @endif
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="badge badge-{{ $preguntaActual->tipo === 'seleccion_unica' ? 'primary' : 'success' }}">
                                    <i class="fas fa-{{ $preguntaActual->tipo === 'seleccion_unica' ? 'dot-circle' : 'check-square' }}"></i>
                                    {{ $preguntaActual->tipo === 'seleccion_unica' ? 'Selección Única' : 'Casillas de Verificación' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- FORMULARIO DE RESPUESTAS -->
                    <form action="{{ route('respuestas.wizard.store') }}" method="POST" id="respuestasForm">
                        @csrf
                        <input type="hidden" name="pregunta_id" value="{{ $preguntaActual->id }}">

                        <div class="form-group">
                            <label for="respuestas">
                                <i class="fas fa-list"></i> Opciones de Respuesta
                                <span class="text-danger">*</span>
                            </label>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> Agrega las opciones de respuesta que estarán disponibles para los usuarios.
                                @if($preguntaActual->tipo === 'seleccion_unica')
                                    Los usuarios podrán seleccionar <strong>una sola opción</strong>.
                                @else
                                    Los usuarios podrán seleccionar <strong>múltiples opciones</strong>.
                                @endif
                            </div>

                            <div id="respuestasContainer">
                                <div class="row mb-2 respuesta-item">
                                    <div class="col-md-8">
                                        <input type="text" name="respuestas[0][texto]" class="form-control"
                                               placeholder="Escribe la opción de respuesta..." required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" name="respuestas[0][orden]" class="form-control"
                                               placeholder="Orden" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger btn-block btn-remove-respuesta" disabled>
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="button" class="btn btn-success" id="btnAgregarRespuesta">
                                    <i class="fas fa-plus"></i> Agregar Otra Opción
                                </button>
                            </div>
                        </div>

                        <!-- BOTONES DE ACCIÓN -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="{{ route('respuestas.wizard.cancel') }}" class="btn btn-outline-danger btn-lg btn-block">
                                    <i class="fas fa-times"></i> Cancelar Wizard
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-save"></i> Guardar Respuestas y Continuar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let respuestaIndex = 1;

    // Agregar nueva respuesta
    $('#btnAgregarRespuesta').click(function() {
        const newRespuesta = `
            <div class="row mb-2 respuesta-item">
                <div class="col-md-8">
                    <input type="text" name="respuestas[${respuestaIndex}][texto]" class="form-control"
                           placeholder="Escribe la opción de respuesta..." required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="respuestas[${respuestaIndex}][orden]" class="form-control"
                           placeholder="Orden" value="${respuestaIndex + 1}" min="1" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-block btn-remove-respuesta">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        `;

        $('#respuestasContainer').append(newRespuesta);
        respuestaIndex++;

        // Habilitar botón eliminar de la primera respuesta si hay más de una
        if ($('.respuesta-item').length > 1) {
            $('.btn-remove-respuesta').prop('disabled', false);
        }
    });

    // Eliminar respuesta
    $(document).on('click', '.btn-remove-respuesta', function() {
        $(this).closest('.respuesta-item').remove();

        // Deshabilitar botón eliminar si solo queda una respuesta
        if ($('.respuesta-item').length === 1) {
            $('.btn-remove-respuesta').prop('disabled', true);
        }

        // Reindexar los campos
        $('.respuesta-item').each(function(index) {
            $(this).find('input[name*="[texto]"]').attr('name', `respuestas[${index}][texto]`);
            $(this).find('input[name*="[orden]"]').attr('name', `respuestas[${index}][orden]`);
        });

        respuestaIndex = $('.respuesta-item').length;
    });

    // Validación del formulario
    $('#respuestasForm').submit(function(e) {
        const respuestas = $('input[name*="[texto]"]').filter(function() {
            return $(this).val().trim() !== '';
        });

        if (respuestas.length === 0) {
            e.preventDefault();
            alert('Debes agregar al menos una opción de respuesta.');
            return false;
        }

        // Validar que no haya textos duplicados
        const textos = [];
        let hayDuplicados = false;

        respuestas.each(function() {
            const texto = $(this).val().trim().toLowerCase();
            if (textos.includes(texto)) {
                hayDuplicados = true;
                return false;
            }
            textos.push(texto);
        });

        if (hayDuplicados) {
            e.preventDefault();
            alert('No puedes tener opciones de respuesta duplicadas.');
            return false;
        }
    });

    // Confirmación antes de cancelar
    $('a[href*="cancel"]').click(function(e) {
        if (!confirm('¿Estás seguro de que quieres cancelar el wizard? Las respuestas agregadas se guardarán.')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
