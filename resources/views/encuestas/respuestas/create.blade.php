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
        <form method="POST" action="{{ route('encuestas.respuestas.store', $encuestaId) }}">
            @csrf

            @forelse($preguntas as $pregunta)
                @if(in_array($pregunta->tipo, ['seleccion_unica', 'seleccion_multiple']))
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-question-circle"></i>
                                Pregunta {{ $loop->iteration }}: {{ $pregunta->texto }}
                            </h5>
                            <small>Tipo: {{ ucfirst(str_replace('_', ' ', $pregunta->tipo)) }}</small>
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
                @endif
            @empty
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay preguntas de selección (única o múltiple) para agregar respuestas.
                    <br>
                    <a href="{{ route('encuestas.preguntas.create', $encuestaId) }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Agregar Preguntas
                    </a>
                </div>
            @endforelse

            @if($preguntas->isNotEmpty())
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Guardar Todas las Respuestas
                    </button>
                    <a href="{{ route('encuestas.show', $encuestaId) }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            @endif
        </form>
    </div>
</div>

@if($preguntas->isNotEmpty())
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
});
</script>
@endsection
