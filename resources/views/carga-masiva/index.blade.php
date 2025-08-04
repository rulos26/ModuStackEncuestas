@extends('adminlte::page')

@section('title', 'Carga Masiva de Encuestas')

@section('content_header')
    <h1>
        <i class="fas fa-upload"></i> Carga Masiva de Encuestas
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-upload"></i> Cargar Preguntas desde Archivo
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('carga-masiva.procesar-preguntas') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Selección de Encuesta -->
                        <div class="form-group">
                            <label for="encuesta_id">
                                <i class="fas fa-clipboard-list"></i> Seleccionar Encuesta
                            </label>
                            <select name="encuesta_id" id="encuesta_id" class="form-control @error('encuesta_id') is-invalid @enderror" required>
                                <option value="">Selecciona una encuesta...</option>
                                @foreach($encuestas as $encuesta)
                                    <option value="{{ $encuesta->id }}" {{ old('encuesta_id') == $encuesta->id ? 'selected' : '' }}>
                                        {{ $encuesta->titulo }} ({{ $encuesta->preguntas->count() }} preguntas)
                                    </option>
                                @endforeach
                            </select>
                            @error('encuesta_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                Selecciona la encuesta donde se cargarán las preguntas
                            </small>
                        </div>

                        <!-- Archivo de Preguntas -->
                        <div class="form-group">
                            <label for="archivo_preguntas">
                                <i class="fas fa-file-alt"></i> Archivo de Preguntas (.txt)
                            </label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('archivo_preguntas') is-invalid @enderror"
                                           id="archivo_preguntas" name="archivo_preguntas" accept=".txt" required>
                                    <label class="custom-file-label" for="archivo_preguntas">Elegir archivo...</label>
                                </div>
                            </div>
                            @error('archivo_preguntas')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Formato requerido:</strong> Un archivo .txt con una pregunta por línea
                            </small>
                        </div>

                        <!-- Modo de Asignación -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-cogs"></i> Modo de Asignación de Tipos
                            </label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="modo_automatico" name="modo_asignacion" value="automatico"
                                               class="custom-control-input" {{ old('modo_asignacion') == 'automatico' ? 'checked' : '' }} required>
                                        <label class="custom-control-label" for="modo_automatico">
                                            <i class="fas fa-robot text-primary"></i> Automático (IA)
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            El sistema determinará automáticamente el tipo de cada pregunta
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="modo_manual" name="modo_asignacion" value="manual"
                                               class="custom-control-input" {{ old('modo_asignacion') == 'manual' ? 'checked' : '' }} required>
                                        <label class="custom-control-label" for="modo_manual">
                                            <i class="fas fa-user text-success"></i> Manual
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Asignarás el tipo de cada pregunta manualmente
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @error('modo_asignacion')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Procesar Preguntas
                            </button>
                            <a href="{{ route('encuestas.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Ayuda -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información de Ayuda
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-file-text text-primary"></i> Formato del Archivo de Preguntas</h5>
                            <p>El archivo debe contener una pregunta por línea. Ejemplo:</p>
                            <pre class="bg-light p-2 rounded">
¿Cuál es tu nombre completo?
¿Cuál es tu edad?
¿Cuál es tu profesión?
¿Cuál es tu nivel de satisfacción?
                            </pre>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-cogs text-success"></i> Tipos de Preguntas Disponibles</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-font text-info"></i> <strong>Texto corto:</strong> Para nombres, emails, etc.</li>
                                <li><i class="fas fa-paragraph text-info"></i> <strong>Párrafo:</strong> Para respuestas largas</li>
                                <li><i class="fas fa-dot-circle text-info"></i> <strong>Selección única:</strong> Una opción de varias</li>
                                <li><i class="fas fa-check-square text-info"></i> <strong>Casilla:</strong> Múltiples opciones</li>
                                <li><i class="fas fa-list text-info"></i> <strong>Lista desplegable:</strong> Menú de opciones</li>
                                <li><i class="fas fa-star text-info"></i> <strong>Escala:</strong> Puntuación del 1 al 10</li>
                                <li><i class="fas fa-table text-info"></i> <strong>Cuadrícula:</strong> Matriz de opciones</li>
                            </ul>
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
.custom-file-label::after {
    content: "Buscar";
}

.card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.custom-control-label {
    font-weight: 500;
}

pre {
    font-size: 0.875rem;
    line-height: 1.4;
}

.list-unstyled li {
    margin-bottom: 0.5rem;
}

.list-unstyled i {
    width: 20px;
}
</style>
@stop

@section('js')
<script>
// Mostrar nombre del archivo seleccionado
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = e.target.files[0].name;
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
});

// Validación del formulario
document.querySelector('form').addEventListener('submit', function(e) {
    var encuestaId = document.getElementById('encuesta_id').value;
    var archivo = document.getElementById('archivo_preguntas').files[0];
    var modoAsignacion = document.querySelector('input[name="modo_asignacion"]:checked');

    if (!encuestaId) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Encuesta requerida',
            text: 'Por favor selecciona una encuesta.'
        });
        return;
    }

    if (!archivo) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Archivo requerido',
            text: 'Por favor selecciona un archivo de preguntas.'
        });
        return;
    }

    if (!modoAsignacion) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Modo requerido',
            text: 'Por favor selecciona un modo de asignación.'
        });
        return;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Procesando archivo...',
        html: 'Leyendo preguntas y analizando contenido...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});
</script>
@stop
