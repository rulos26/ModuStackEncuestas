@extends('adminlte::page')

@section('title', 'Configurar Lógica - Pregunta ' . ($preguntaIndex + 1))

@section('css')
<style>
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.logica-item {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: #f8f9fa;
}

.logica-item:hover {
    background-color: #e9ecef;
}

.progress-bar {
    transition: width 0.3s ease;
}

.arrow-diagram {
    font-size: 1.5rem;
    color: #007bff;
    text-align: center;
    margin: 1rem 0;
}

.badge-tipo {
    font-size: 0.8rem;
}
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Progreso del Wizard -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tasks"></i>
                        Progreso: Pregunta {{ $preguntaIndex + 1 }} de {{ $totalPreguntas }}
                    </h5>
                </div>
                <div class="card-body">
                    <div class="progress">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width: {{ (($preguntaIndex + 1) / $totalPreguntas) * 100 }}%"
                             aria-valuenow="{{ $preguntaIndex + 1 }}"
                             aria-valuemin="0"
                             aria-valuemax="{{ $totalPreguntas }}">
                            {{ round((($preguntaIndex + 1) / $totalPreguntas) * 100) }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Lógica -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs"></i>
                        Configurar Lógica de Salto
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Información de la Pregunta -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-question-circle"></i> Pregunta Actual:</h6>
                        <p class="mb-2"><strong>{{ $preguntaActual->texto }}</strong></p>
                        <span class="badge badge-info badge-tipo">
                            <i class="fas fa-tag"></i> {{ ucfirst(str_replace('_', ' ', $preguntaActual->tipo)) }}
                        </span>
                    </div>

                    <form action="{{ route('logica.wizard.store') }}" method="POST" id="formLogica">
                        @csrf
                        <input type="hidden" name="pregunta_id" value="{{ $preguntaActual->id }}">

                        <!-- Configuración por Respuesta -->
                        <div id="logicasContainer">
                            @foreach($preguntaActual->respuestas as $index => $respuesta)
                                <div class="logica-item" data-respuesta-id="{{ $respuesta->id }}">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">
                                                <i class="fas fa-check-circle text-success"></i>
                                                Si responde:
                                            </label>
                                            <input type="text" class="form-control" value="{{ $respuesta->texto }}" readonly>
                                            <input type="hidden" name="logicas[{{ $index }}][respuesta_id]" value="{{ $respuesta->id }}">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">
                                                <i class="fas fa-arrow-right text-primary"></i>
                                                Entonces:
                                            </label>
                                            <select name="logicas[{{ $index }}][siguiente_pregunta_id]" class="form-control logica-select">
                                                <option value="">-- Continuar secuencialmente --</option>
                                                @foreach($preguntasDestino as $destino)
                                                    <option value="{{ $destino->id }}"
                                                            {{ $logicaExistente->where('respuesta_id', $respuesta->id)->where('siguiente_pregunta_id', $destino->id)->first() ? 'selected' : '' }}>
                                                        {{ $destino->orden }}. {{ Str::limit($destino->texto, 50) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">
                                                <i class="fas fa-stop-circle text-danger"></i>
                                                O finalizar:
                                            </label>
                                            <div class="form-check">
                                                <input type="checkbox" name="logicas[{{ $index }}][finalizar]"
                                                       class="form-check-input finalizar-checkbox"
                                                       value="1"
                                                       {{ $logicaExistente->where('respuesta_id', $respuesta->id)->where('finalizar', true)->first() ? 'checked' : '' }}>
                                                <label class="form-check-label">
                                                    Finalizar encuesta
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Botones de Acción -->
                        <div class="form-group text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i>
                                Guardar y Continuar
                            </button>
                            <a href="{{ route('logica.wizard.cancel') }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel Lateral - Vista Previa -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye"></i>
                        Vista Previa de Lógica
                    </h5>
                </div>
                <div class="card-body">
                    <div id="vistaPrevia">
                        <p class="text-muted text-center">
                            <i class="fas fa-info-circle"></i>
                            La vista previa se actualizará automáticamente mientras configuras la lógica.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Información de la Encuesta -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i>
                        Información de la Encuesta
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Encuesta:</strong> {{ $encuesta->titulo }}</p>
                    <p><strong>Empresa:</strong> {{ $encuesta->empresa->nombre ?? 'N/A' }}</p>
                    <p><strong>Total Preguntas:</strong> {{ $encuesta->preguntas->count() }}</p>
                    <p><strong>Preguntas con Lógica:</strong> {{ $totalPreguntas }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
// Funciones globales para la lógica
window.actualizarVistaPrevia = function() {
    var vistaPrevia = document.getElementById('vistaPrevia');
    var logicaItems = document.querySelectorAll('.logica-item');
    var html = '';

    logicaItems.forEach(function(item) {
        var respuestaTexto = item.querySelector('input[readonly]').value;
        var siguienteSelect = item.querySelector('.logica-select');
        var finalizarCheckbox = item.querySelector('.finalizar-checkbox');

        var accion = '';
        if (finalizarCheckbox.checked) {
            accion = '<span class="badge badge-danger"><i class="fas fa-stop-circle"></i> Finalizar</span>';
        } else if (siguienteSelect.value) {
            var opcionSeleccionada = siguienteSelect.options[siguienteSelect.selectedIndex];
            accion = '<span class="badge badge-success"><i class="fas fa-arrow-right"></i> Ir a: ' + opcionSeleccionada.text + '</span>';
        } else {
            accion = '<span class="badge badge-secondary"><i class="fas fa-arrow-down"></i> Continuar</span>';
        }

        html += '<div class="mb-2 p-2 border rounded">';
        html += '<strong>"' + respuestaTexto + '"</strong><br>';
        html += accion;
        html += '</div>';
    });

    if (html === '') {
        html = '<p class="text-muted text-center">No hay lógica configurada</p>';
    }

    vistaPrevia.innerHTML = html;
};

window.validarLogica = function() {
    var logicaItems = document.querySelectorAll('.logica-item');
    var errores = [];

    logicaItems.forEach(function(item) {
        var siguienteSelect = item.querySelector('.logica-select');
        var finalizarCheckbox = item.querySelector('.finalizar-checkbox');

        // Verificar que no se seleccione finalizar y una pregunta de destino al mismo tiempo
        if (finalizarCheckbox.checked && siguienteSelect.value) {
            errores.push('No puedes finalizar la encuesta y saltar a otra pregunta al mismo tiempo.');
        }
    });

    return errores;
};

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== WIZARD DE LÓGICA INICIADO ===');

    // Actualizar vista previa cuando cambien los controles
    document.querySelectorAll('.logica-select, .finalizar-checkbox').forEach(function(element) {
        element.addEventListener('change', window.actualizarVistaPrevia);
    });

    // Validar formulario antes de enviar
    document.getElementById('formLogica').addEventListener('submit', function(e) {
        var errores = window.validarLogica();
        if (errores.length > 0) {
            e.preventDefault();
            alert('Errores encontrados:\n' + errores.join('\n'));
            return false;
        }
    });

    // Inicializar vista previa
    window.actualizarVistaPrevia();

    console.log('=== WIZARD DE LÓGICA LISTO ===');
});
</script>
@endsection
