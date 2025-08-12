@extends('adminlte::page')

@section('title', 'Wizard de Respuestas - Configuración Administrativa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Wizard de Configuración de Respuestas
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">
                            <i class="fas fa-info-circle"></i> Uso Administrativo
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb"></i> ¿Qué hace este wizard?</h5>
                        <p class="mb-0">
                            Este wizard te permite <strong>configurar las respuestas concretas</strong> para las preguntas de tipo
                            "Selección Única", "Casillas de Verificación" y "Selección Múltiple" que ya han sido creadas en las encuestas.
                        </p>
                    </div>

                    @if(Session::get('wizard_respuestas_count', 0) > 0 && Session::get('wizard_encuesta_id'))
                        <div class="alert alert-success">
                            <h5><i class="fas fa-play-circle"></i> Sesión Activa</h5>
                            <p class="mb-0">
                                <strong>{{ Session::get('wizard_respuestas_count', 0) }}</strong> respuesta(s) configurada(s) en esta sesión.
                                <span class="badge badge-primary ml-2">
                                    <i class="fas fa-poll"></i> Encuesta ID: {{ Session::get('wizard_encuesta_id') }}
                                </span>
                                <div class="mt-2">
                                    <a href="{{ route('respuestas.wizard.responder') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Continuar Configurando Respuestas
                                    </a>
                                    <a href="{{ route('respuestas.wizard.cancel') }}" class="btn btn-outline-danger btn-sm ml-2"
                                       onclick="return confirm('¿Estás seguro de que quieres cancelar el wizard? Se perderán los datos de la sesión.')">
                                        <i class="fas fa-times"></i> Cancelar Sesión
                                    </a>
                                </div>
                            </p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <h5><i class="fas fa-list"></i> Encuestas que Requieren Configuración de Respuestas</h5>
                            <p class="text-muted">
                                Selecciona una encuesta para configurar las respuestas de sus preguntas de selección.
                            </p>
                        </div>
                    </div>

                                        <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-list"></i> Seleccionar Encuesta
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('respuestas.wizard.responder') }}" method="GET" id="encuestaForm">
                                        <div class="form-group">
                                            <label for="encuesta_id">
                                                <i class="fas fa-poll"></i> Encuesta a Configurar
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select name="encuesta_id" id="encuesta_id" class="form-control form-control-lg" required>
                                                <option value="">-- Selecciona una encuesta --</option>
                                                @foreach($encuestas as $encuesta)
                                                    @php
                                                        $preguntasSinRespuestas = $encuesta->preguntas->filter(function($p) {
                                                            return $p->respuestas->isEmpty() &&
                                                                   in_array($p->tipo, ['seleccion_unica', 'casillas_verificacion', 'seleccion_multiple']);
                                                        });
                                                    @endphp
                                                    <option value="{{ $encuesta->id }}"
                                                            data-preguntas="{{ $encuesta->preguntas_sin_respuestas }}"
                                                            data-empresa="{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}"
                                                            data-estado="{{ $encuesta->estado }}">
                                                        {{ $encuesta->titulo }}
                                                        ({{ $encuesta->preguntas_sin_respuestas }} preguntas sin configurar)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                Selecciona la encuesta para la cual quieres configurar las respuestas de las preguntas.
                                            </small>
                                        </div>

                                        <!-- Información de la encuesta seleccionada -->
                                        <div id="encuesta-info" class="mt-3" style="display: none;">
                                            <div class="alert alert-info">
                                                <h6><i class="fas fa-info-circle"></i> Información de la Encuesta</h6>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <strong>Empresa:</strong> <span id="empresa-info">-</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong>Estado:</strong> <span id="estado-info">-</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong>Preguntas por configurar:</strong>
                                                        <span id="preguntas-info" class="badge badge-warning">-</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <a href="{{ route('encuestas.index') }}" class="btn btn-outline-secondary btn-lg btn-block">
                                                    <i class="fas fa-arrow-left"></i> Volver
                                                </a>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="submit" class="btn btn-primary btn-lg btn-block" id="btnSiguiente" >
                                                    <i class="fas fa-arrow-right"></i> Siguiente
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Manejar selección de encuesta
    $('#encuesta_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const encuestaId = $(this).val();

        if (encuestaId) {
            // Mostrar información de la encuesta
            $('#empresa-info').text(selectedOption.data('empresa'));
            $('#estado-info').text(selectedOption.data('estado'));
            $('#preguntas-info').text(selectedOption.data('preguntas'));

            // Mostrar el panel de información
            $('#encuesta-info').show();

            // Habilitar botón siguiente
            $('#btnSiguiente').prop('disabled', false);
        } else {
            // Ocultar información y deshabilitar botón
            $('#encuesta-info').hide();
            $('#btnSiguiente').prop('disabled', true);
        }
    });

    // Validación del formulario
    $('#encuestaForm').submit(function(e) {
        const encuestaId = $('#encuesta_id').val();

        if (!encuestaId) {
            e.preventDefault();
            alert('Debes seleccionar una encuesta para continuar.');
            return false;
        }

        return true;
    });

    // Confirmación antes de cancelar
    $('.btn-cancel').click(function(e) {
        if (!confirm('¿Estás seguro de que quieres cancelar el wizard?')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
