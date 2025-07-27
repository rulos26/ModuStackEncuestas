@extends('adminlte::page')

@section('title', 'Agregar Pregunta')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> Agregar Pregunta a la Encuesta
                    </h3>
                    <h4 class="text-muted">{{ $encuesta->titulo ?? 'Sin título' }}</h4>
                </div>
                <div class="card-body">
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

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Errores de validación</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('encuestas.preguntas.store', $encuesta->id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label for="texto" class="form-label">
                                <i class="fas fa-question-circle"></i> Texto de la pregunta
                            </label>
                            <input type="text" name="texto" id="texto"
                                   class="form-control @error('texto') is-invalid @enderror"
                                   value="{{ old('texto') }}"
                                   placeholder="Ej: ¿Cuál es tu color favorito?"
                                   required>
                            @error('texto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                La pregunta debe ser clara y específica (3-500 caracteres)
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="tipo" class="form-label">
                                <i class="fas fa-list"></i> Tipo de pregunta
                            </label>
                            <select name="tipo" id="tipo"
                                    class="form-control @error('tipo') is-invalid @enderror"
                                    required>
                                <option value="">Selecciona el tipo de pregunta</option>
                                <option value="texto" {{ old('tipo') == 'texto' ? 'selected' : '' }}>
                                    📝 Texto libre
                                </option>
                                <option value="seleccion_unica" {{ old('tipo') == 'seleccion_unica' ? 'selected' : '' }}>
                                    🔘 Selección única (Radio buttons)
                                </option>
                                <option value="seleccion_multiple" {{ old('tipo') == 'seleccion_multiple' ? 'selected' : '' }}>
                                    ☑️ Selección múltiple (Checkboxes)
                                </option>
                                <option value="numero" {{ old('tipo') == 'numero' ? 'selected' : '' }}>
                                    🔢 Número
                                </option>
                                <option value="fecha" {{ old('tipo') == 'fecha' ? 'selected' : '' }}>
                                    📅 Fecha
                                </option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Solo las preguntas de selección (única o múltiple) permiten agregar respuestas predefinidas
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="orden" class="form-label">
                                <i class="fas fa-sort-numeric-up"></i> Orden de la pregunta
                            </label>
                            <input type="number" name="orden" id="orden"
                                   class="form-control @error('orden') is-invalid @enderror"
                                   value="{{ old('orden', $encuesta->preguntas->count() + 1) }}"
                                   min="1" required>
                            @error('orden')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                El orden determina la secuencia en que aparecerán las preguntas
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="obligatoria" id="obligatoria"
                                       class="form-check-input @error('obligatoria') is-invalid @enderror"
                                       {{ old('obligatoria', true) ? 'checked' : '' }}>
                                <label for="obligatoria" class="form-check-label">
                                    <i class="fas fa-asterisk"></i> ¿Es obligatoria?
                                </label>
                                @error('obligatoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    Las preguntas obligatorias deben ser respondidas para completar la encuesta
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Guardar Pregunta
                            </button>
                            <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-info-circle"></i> Información
                    </h5>
                </div>
                <div class="card-body">
                    <h6>Tipos de Preguntas:</h6>
                    <ul class="list-unstyled">
                        <li><strong>📝 Texto libre:</strong> Respuesta abierta</li>
                        <li><strong>🔘 Selección única:</strong> Una sola opción</li>
                        <li><strong>☑️ Selección múltiple:</strong> Varias opciones</li>
                        <li><strong>🔢 Número:</strong> Valor numérico</li>
                        <li><strong>📅 Fecha:</strong> Fecha específica</li>
                    </ul>

                    <hr>

                    <h6>Preguntas Actuales:</h6>
                    @if($encuesta->preguntas->count() > 0)
                        <ul class="list-unstyled">
                            @foreach($encuesta->preguntas as $pregunta)
                                <li class="mb-2">
                                    <small class="text-muted">{{ $pregunta->orden }}.</small>
                                    {{ Str::limit($pregunta->texto, 30) }}
                                    <span class="badge badge-info">{{ $pregunta->tipo }}</span>
                                    @if($pregunta->obligatoria)
                                        <span class="badge badge-success">Obligatoria</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No hay preguntas configuradas aún.</p>
                    @endif

                    <hr>

                    <div class="alert alert-info">
                        <h6><i class="icon fas fa-lightbulb"></i> Consejo:</h6>
                        <p class="mb-0">
                            Para poder agregar respuestas predefinidas, usa preguntas de tipo
                            <strong>Selección única</strong> o <strong>Selección múltiple</strong>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Validación del formulario
    $('form').on('submit', function(e) {
        const texto = $('#texto').val().trim();
        const tipo = $('#tipo').val();
        const orden = $('#orden').val();

        let isValid = true;
        let errorMessage = '';

        // Validar texto
        if (texto.length < 3) {
            errorMessage += 'El texto de la pregunta debe tener al menos 3 caracteres.\n';
            isValid = false;
        }

        if (texto.length > 500) {
            errorMessage += 'El texto de la pregunta no puede exceder 500 caracteres.\n';
            isValid = false;
        }

        // Validar tipo
        if (!tipo) {
            errorMessage += 'Debes seleccionar un tipo de pregunta.\n';
            isValid = false;
        }

        // Validar orden
        if (orden < 1) {
            errorMessage += 'El orden debe ser mayor a 0.\n';
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Por favor, corrige los siguientes errores:\n\n' + errorMessage);
        }
    });

    // Mostrar información adicional según el tipo seleccionado
    $('#tipo').on('change', function() {
        const tipo = $(this).val();
        const infoText = $('.form-text.text-muted').last();

        if (tipo === 'seleccion_unica' || tipo === 'seleccion_multiple') {
            infoText.text('Después de guardar esta pregunta, podrás agregar respuestas predefinidas.');
        } else {
            infoText.text('Este tipo de pregunta no requiere respuestas predefinidas.');
        }
    });
});
</script>
@endsection
