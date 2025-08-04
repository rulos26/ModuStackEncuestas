@extends('adminlte::page')

@section('title', 'Asignar Tipos de Preguntas')

@section('content_header')
    <h1>
        <i class="fas fa-magic"></i> Asignar Tipos de Preguntas
        <small class="text-muted">Pregunta {{ $preguntaIndex + 1 }} de {{ $totalPreguntas }}</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Barra de Progreso -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             role="progressbar"
                             style="width: {{ (($preguntaIndex + 1) / $totalPreguntas) * 100 }}%"
                             aria-valuenow="{{ $preguntaIndex + 1 }}"
                             aria-valuemin="0"
                             aria-valuemax="{{ $totalPreguntas }}">
                            <strong class="text-white">{{ $preguntaIndex + 1 }} de {{ $totalPreguntas }}</strong>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            {{ round((($preguntaIndex + 1) / $totalPreguntas) * 100) }}% completado
                        </small>
                    </div>
                </div>
            </div>

            <!-- Pregunta Actual -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle text-primary"></i> Pregunta {{ $preguntaIndex + 1 }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Texto de la Pregunta:</h5>
                        <p class="mb-0 font-weight-bold">{{ $preguntaActual['texto'] }}</p>
                    </div>

                    <form action="{{ route('carga-masiva.guardar-tipo-pregunta') }}" method="POST">
                        @csrf
                        <input type="hidden" name="cache_key" value="{{ $cacheKey }}">
                        <input type="hidden" name="pregunta_index" value="{{ $preguntaIndex }}">

                        <!-- Selección de Tipo -->
                        <div class="form-group">
                            <label for="tipo_pregunta">
                                <i class="fas fa-cogs"></i> Seleccionar Tipo de Pregunta
                            </label>
                            <select name="tipo_pregunta" id="tipo_pregunta" class="form-control" required>
                                <option value="">Selecciona un tipo...</option>
                                @foreach($tiposDisponibles as $valor => $nombre)
                                    <option value="{{ $valor }}" {{ old('tipo_pregunta') == $valor ? 'selected' : '' }}>
                                        {{ $nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Selecciona el tipo más apropiado para esta pregunta
                            </small>
                        </div>

                        <!-- Descripción del Tipo Seleccionado -->
                        <div id="descripcion-tipo" class="alert alert-secondary" style="display: none;">
                            <h6><i class="fas fa-info-circle"></i> Descripción:</h6>
                            <p id="descripcion-texto" class="mb-0"></p>
                        </div>

                        <!-- Botones -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="btn-continuar">
                                <i class="fas fa-arrow-right"></i> Continuar
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="anteriorPregunta()">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </button>
                            <a href="{{ route('carga-masiva.index') }}" class="btn btn-danger">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información de Tipos -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-lightbulb"></i> Guía de Tipos de Preguntas
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($tiposDisponibles as $valor => $nombre)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-left-{{ $this->getColorForType($valor) }}">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <i class="fas fa-{{ $this->getIconForType($valor) }} text-{{ $this->getColorForType($valor) }}"></i>
                                                    {{ $nombre }}
                                                </h6>
                                                <p class="card-text small">{{ $this->getDescriptionForType($valor) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.progress {
    border-radius: 0.5rem;
}

.border-left-primary { border-left: 4px solid #007bff !important; }
.border-left-success { border-left: 4px solid #28a745 !important; }
.border-left-info { border-left: 4px solid #17a2b8 !important; }
.border-left-warning { border-left: 4px solid #ffc107 !important; }
.border-left-danger { border-left: 4px solid #dc3545 !important; }
.border-left-secondary { border-left: 4px solid #6c757d !important; }
.border-left-dark { border-left: 4px solid #343a40 !important; }

.alert {
    border-radius: 0.5rem;
}

.btn {
    border-radius: 0.375rem;
}
</style>
@stop

@section('js')
<script>
// Mostrar descripción del tipo seleccionado
document.getElementById('tipo_pregunta').addEventListener('change', function() {
    const tipo = this.value;
    const descripcionDiv = document.getElementById('descripcion-tipo');
    const descripcionTexto = document.getElementById('descripcion-texto');

    if (tipo) {
        const descripciones = {
            'texto_corto': 'Ideal para nombres, emails, teléfonos, fechas y respuestas cortas. El usuario puede escribir texto libre pero limitado.',
            'parrafo': 'Perfecto para comentarios, opiniones, sugerencias y respuestas largas. Permite texto extenso.',
            'seleccion_unica': 'El usuario debe elegir una sola opción de una lista de opciones disponibles.',
            'casilla': 'El usuario puede seleccionar múltiples opciones de una lista de opciones.',
            'lista_desplegable': 'Muestra las opciones en un menú desplegable. El usuario selecciona una opción.',
            'escala': 'Permite al usuario calificar en una escala numérica (ej: 1-10, 1-5).',
            'cuadricula': 'Matriz de opciones donde el usuario puede evaluar múltiples criterios.'
        };

        descripcionTexto.textContent = descripciones[tipo] || 'Descripción no disponible.';
        descripcionDiv.style.display = 'block';
    } else {
        descripcionDiv.style.display = 'none';
    }
});

// Validación del formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const tipoSeleccionado = document.getElementById('tipo_pregunta').value;

    if (!tipoSeleccionado) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Tipo requerido',
            text: 'Por favor selecciona un tipo de pregunta.'
        });
        return;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Guardando...',
        html: 'Procesando tipo de pregunta...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});

// Función para ir a la pregunta anterior
function anteriorPregunta() {
    const preguntaActual = {{ $preguntaIndex }};
    if (preguntaActual > 0) {
        window.location.href = '{{ route("carga-masiva.wizard-preguntas") }}?cache_key={{ $cacheKey }}&pregunta=' + (preguntaActual - 1);
    }
}

// Atajos de teclado
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.ctrlKey) {
        document.getElementById('btn-continuar').click();
    }
});
</script>
@stop
