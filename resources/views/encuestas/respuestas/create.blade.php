@extends('adminlte::page')

@section('title', 'Agregar Respuestas')

@section('content_header')
    <h1>Agregar Respuestas a las Preguntas</h1>
@endsection

@section('content')
@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning">
        {{ session('warning') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Encuesta: {{ $encuesta->titulo ?? 'Sin título' }}</h3>
    </div>
    <div class="card-body">
        <!-- Resumen de preguntas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Preguntas</span>
                        <span class="info-box-number">{{ $preguntas->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Con Respuestas</span>
                        <span class="info-box-number">{{ $preguntasConRespuestas->count() }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Sin Respuestas</span>
                        <span class="info-box-number">{{ $preguntasSinRespuestas->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if($preguntasConRespuestas->isNotEmpty())
            <div class="alert alert-info">
                <h5><i class="icon fas fa-info"></i> Preguntas con respuestas configuradas</h5>
                <p>Las siguientes preguntas ya tienen respuestas configuradas. Puedes editarlas o continuar con las que no tienen respuestas.</p>
            </div>

            @foreach($preguntasConRespuestas as $pregunta)
                <div class="card mb-3 border-success">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-check-circle"></i>
                            Pregunta {{ $loop->iteration }}: {{ $pregunta->texto }}
                        </h5>
                        <small>Tipo: {{ ucfirst(str_replace('_', ' ', $pregunta->tipo)) }} | Respuestas: {{ $pregunta->respuestas->count() }}</small>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Respuestas actuales:</h6>
                                <ul class="list-group list-group-flush">
                                    @foreach($pregunta->respuestas as $respuesta)
                                        <li class="list-group-item">
                                            <strong>{{ $respuesta->orden }}.</strong> {{ $respuesta->texto }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <button class="btn btn-warning btn-sm" onclick="editarRespuestas({{ $pregunta->id }})">
                                        <i class="fas fa-edit"></i> Editar Respuestas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        @if($preguntasSinRespuestas->isNotEmpty())
            <div class="alert alert-warning">
                <h5><i class="icon fas fa-exclamation-triangle"></i> Preguntas sin respuestas configuradas</h5>
                <p>Las siguientes preguntas necesitan respuestas para poder ser utilizadas en la encuesta.</p>
            </div>

            <form method="POST" action="{{ route('encuestas.respuestas.store', $encuestaId) }}">
                @csrf

                @foreach($preguntasSinRespuestas as $pregunta)
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                Pregunta {{ $loop->iteration }}: {{ $pregunta->texto }}
                            </h5>
                            <small>Tipo: {{ ucfirst(str_replace('_', ' ', $pregunta->tipo)) }} | Sin respuestas configuradas</small>
                        </div>
                        <div class="card-body">
                            <div class="respuestas-container" data-pregunta-id="{{ $pregunta->id }}">
                                <!-- Respuesta 1 -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Respuesta 1:</label>
                                        <input type="text" name="respuestas[{{ $pregunta->id }}][1][texto]"
                                               class="form-control" placeholder="Ingrese la primera respuesta" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Orden:</label>
                                        <input type="number" name="respuestas[{{ $pregunta->id }}][1][orden]"
                                               class="form-control" value="1" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <input type="hidden" name="respuestas[{{ $pregunta->id }}][1][pregunta_id]" value="{{ $pregunta->id }}">
                                    </div>
                                </div>

                                <!-- Respuesta 2 -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Respuesta 2:</label>
                                        <input type="text" name="respuestas[{{ $pregunta->id }}][2][texto]"
                                               class="form-control" placeholder="Ingrese la segunda respuesta" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Orden:</label>
                                        <input type="number" name="respuestas[{{ $pregunta->id }}][2][orden]"
                                               class="form-control" value="2" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <input type="hidden" name="respuestas[{{ $pregunta->id }}][2][pregunta_id]" value="{{ $pregunta->id }}">
                                    </div>
                                </div>

                                <!-- Respuesta 3 -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Respuesta 3:</label>
                                        <input type="text" name="respuestas[{{ $pregunta->id }}][3][texto]"
                                               class="form-control" placeholder="Ingrese la tercera respuesta">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Orden:</label>
                                        <input type="number" name="respuestas[{{ $pregunta->id }}][3][orden]"
                                               class="form-control" value="3" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <input type="hidden" name="respuestas[{{ $pregunta->id }}][3][pregunta_id]" value="{{ $pregunta->id }}">
                                    </div>
                                </div>

                                <!-- Respuesta 4 -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Respuesta 4:</label>
                                        <input type="text" name="respuestas[{{ $pregunta->id }}][4][texto]"
                                               class="form-control" placeholder="Ingrese la cuarta respuesta">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Orden:</label>
                                        <input type="number" name="respuestas[{{ $pregunta->id }}][4][orden]"
                                               class="form-control" value="4" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <input type="hidden" name="respuestas[{{ $pregunta->id }}][4][pregunta_id]" value="{{ $pregunta->id }}">
                                    </div>
                                </div>

                                <!-- Respuesta 5 -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Respuesta 5:</label>
                                        <input type="text" name="respuestas[{{ $pregunta->id }}][5][texto]"
                                               class="form-control" placeholder="Ingrese la quinta respuesta">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Orden:</label>
                                        <input type="number" name="respuestas[{{ $pregunta->id }}][5][orden]"
                                               class="form-control" value="5" min="1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">&nbsp;</label>
                                        <input type="hidden" name="respuestas[{{ $pregunta->id }}][5][pregunta_id]" value="{{ $pregunta->id }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($preguntasSinRespuestas->isNotEmpty())
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Guardar Respuestas
                        </button>
                        <a href="{{ route('encuestas.show', $encuestaId) }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                @endif
            </form>
        @endif

        @if($preguntasSinRespuestas->isEmpty() && $preguntasConRespuestas->isNotEmpty())
            <div class="alert alert-success">
                <h5><i class="icon fas fa-check"></i> ¡Todas las preguntas tienen respuestas configuradas!</h5>
                <p>Todas las preguntas de selección ya tienen respuestas configuradas. Puedes continuar con la configuración de lógica.</p>
                <a href="{{ route('encuestas.logica.create', $encuestaId) }}" class="btn btn-success">
                    <i class="fas fa-arrow-right"></i> Continuar: Configurar Lógica
                </a>
            </div>
        @endif
    </div>
</div>

@if($preguntasSinRespuestas->isNotEmpty())
    <div class="mt-3">
        <a href="{{ route('encuestas.logica.create', $encuestaId) }}" class="btn btn-success">
            <i class="fas fa-arrow-right"></i> Siguiente: Configurar Lógica
        </a>
    </div>
@endif
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación simple del formulario
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredInputs = form.querySelectorAll('input[required]');
            let isValid = true;

            requiredInputs.forEach(input => {
                if (input.value.trim() === '') {
                    isValid = false;
                    input.style.borderColor = 'red';
                } else {
                    input.style.borderColor = '';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor, complete todas las respuestas obligatorias (mínimo 2 por pregunta).');
            }
        });
    }
});

function editarRespuestas(preguntaId) {
    // Función para editar respuestas existentes
    alert('Función de edición de respuestas en desarrollo. Por ahora, puedes eliminar y volver a crear las respuestas.');
}
</script>
@endsection
