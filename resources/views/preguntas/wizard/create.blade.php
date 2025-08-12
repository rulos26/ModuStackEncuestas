@extends('adminlte::page')

@section('title', 'Wizard de Preguntas - Crear Pregunta')

@section('content_header')
    <h1>
        <i class="fas fa-magic"></i> Wizard de Preguntas
        <small>Paso 2: Crear Pregunta - {{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('preguntas.wizard.index') }}">Wizard de Preguntas</a></li>
            <li class="breadcrumb-item active">Crear Pregunta</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL WIZARD -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 1. Seleccionar Encuesta</strong>
                </div>
                <div class="progress-bar bg-primary" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-question-circle"></i> 2. Crear Pregunta</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 34%;" aria-valuenow="34" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 3. Confirmar</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTADOR DE PREGUNTAS EN SESIÓN -->
    @if(Session::get('wizard_preguntas_count', 0) > 0)
        <div class="alert alert-info">
            <h5><i class="fas fa-info-circle"></i> Progreso de la Sesión</h5>
            <p class="mb-0">
                <strong>{{ Session::get('wizard_preguntas_count', 0) }}</strong> pregunta(s) creada(s) en esta sesión.
                <span class="badge badge-primary ml-2">
                    <i class="fas fa-poll"></i> {{ $encuesta->preguntas->count() + Session::get('wizard_preguntas_count', 0) }} total en la encuesta
                </span>
            </p>
        </div>
    @endif

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

    <!-- INFORMACIÓN DE LA ENCUESTA -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-poll"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Encuesta Seleccionada</span>
                    <span class="info-box-number">{{ $encuesta->titulo }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $encuesta->empresa->nombre ?? 'Sin empresa' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Preguntas Existentes</span>
                    <span class="info-box-number">{{ $encuesta->preguntas->count() }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        Estado: {{ ucfirst($encuesta->estado) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULARIO DE PREGUNTA -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-question-circle"></i> Crear Nueva Pregunta
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    <i class="fas fa-plus"></i> Paso 2 de 3
                </span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('preguntas.wizard.store') }}" method="POST" id="form-pregunta">
                @csrf

                <!-- TEXTO DE LA PREGUNTA -->
                <div class="form-group">
                    <label for="texto" class="form-label">
                        <i class="fas fa-question-circle text-primary"></i>
                        <strong>Texto de la pregunta *</strong>
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
                        <i class="fas fa-info-circle"></i> La pregunta debe ser clara y específica (3-500 caracteres)
                    </small>
                </div>

                <!-- DESCRIPCIÓN -->
                <div class="form-group">
                    <label for="descripcion" class="form-label">
                        <i class="fas fa-info-circle text-info"></i>
                        Descripción (opcional)
                    </label>
                    <textarea name="descripcion" id="descripcion" rows="3"
                              class="form-control @error('descripcion') is-invalid @enderror"
                              placeholder="Información adicional o contexto para la pregunta">{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- TIPO DE PREGUNTA -->
                <div class="form-group">
                    <label for="tipo" class="form-label">
                        <i class="fas fa-list text-warning"></i>
                        <strong>Tipo de pregunta *</strong>
                    </label>
                    <select name="tipo" id="tipo" class="form-control @error('tipo') is-invalid @enderror" required>
                        <option value="">Selecciona un tipo de pregunta</option>
                        <option value="respuesta_corta" {{ old('tipo') == 'respuesta_corta' ? 'selected' : '' }}>
                            📝 Respuesta corta
                        </option>
                        <option value="parrafo" {{ old('tipo') == 'parrafo' ? 'selected' : '' }}>
                            📄 Párrafo
                        </option>
                        <option value="seleccion_unica" {{ old('tipo') == 'seleccion_unica' ? 'selected' : '' }}>
                            🔘 Selección única
                        </option>
                        <option value="casillas_verificacion" {{ old('tipo') == 'casillas_verificacion' ? 'selected' : '' }}>
                            ☑️ Casillas de verificación
                        </option>
                        <option value="lista_desplegable" {{ old('tipo') == 'lista_desplegable' ? 'selected' : '' }}>
                            📋 Lista desplegable
                        </option>
                        <option value="escala_lineal" {{ old('tipo') == 'escala_lineal' ? 'selected' : '' }}>
                            📊 Escala lineal
                        </option>
                        <option value="fecha" {{ old('tipo') == 'fecha' ? 'selected' : '' }}>
                            📅 Fecha
                        </option>
                        <option value="hora" {{ old('tipo') == 'hora' ? 'selected' : '' }}>
                            🕐 Hora
                        </option>
                        <option value="carga_archivos" {{ old('tipo') == 'carga_archivos' ? 'selected' : '' }}>
                            📎 Carga de archivos
                        </option>
                        <option value="ubicacion_mapa" {{ old('tipo') == 'ubicacion_mapa' ? 'selected' : '' }}>
                            🗺️ Ubicación en mapa
                        </option>
                    </select>
                    @error('tipo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- ORDEN -->
                <div class="form-group">
                    <label for="orden" class="form-label">
                        <i class="fas fa-sort-numeric-up text-success"></i>
                        Orden (opcional)
                    </label>
                    <input type="number" name="orden" id="orden"
                           class="form-control @error('orden') is-invalid @enderror"
                           value="{{ old('orden', $encuesta->preguntas->count() + 1) }}"
                           min="1"
                           placeholder="Orden de la pregunta">
                    @error('orden')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Si no especificas un orden, se asignará automáticamente
                    </small>
                </div>

                <!-- OBLIGATORIA -->
                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="obligatoria" name="obligatoria" {{ old('obligatoria') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="obligatoria">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                            <strong>Pregunta obligatoria</strong>
                        </label>
                    </div>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Marca esta casilla si la pregunta es obligatoria para el encuestado
                    </small>
                </div>

                <!-- CAMPOS CONDICIONALES BASADOS EN EL TIPO -->
                <div id="campos-condicionales" class="mt-4">
                    <!-- Los campos específicos se cargarán dinámicamente con JavaScript -->
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="form-group mt-4">
                    <div class="row">
                        <div class="col-md-4">
                            <a href="{{ route('preguntas.wizard.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('preguntas.wizard.cancel') }}" class="btn btn-danger btn-block"
                               onclick="return confirm('¿Estás seguro de que quieres cancelar el wizard? Se perderán los datos de la sesión.')">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Guardar Pregunta
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('css')
<style>
    .progress-bar {
        font-size: 0.8rem;
    }

    .info-box {
        min-height: 80px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
    }

    .custom-control-label {
        font-weight: 600;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn-block {
        font-weight: 600;
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Función para mostrar campos condicionales según el tipo de pregunta
        function mostrarCamposCondicionales(tipo) {
            const contenedor = $('#campos-condicionales');
            contenedor.empty();

            switch(tipo) {
                case 'respuesta_corta':
                case 'parrafo':
                    contenedor.append(`
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="placeholder" class="form-label">
                                        <i class="fas fa-comment text-info"></i> Placeholder
                                    </label>
                                    <input type="text" name="placeholder" id="placeholder"
                                           class="form-control @error('placeholder') is-invalid @enderror"
                                           value="{{ old('placeholder') }}"
                                           placeholder="Texto de ejemplo">
                                    @error('placeholder')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="min_caracteres" class="form-label">
                                        <i class="fas fa-text-width text-warning"></i> Mínimo de caracteres
                                    </label>
                                    <input type="number" name="min_caracteres" id="min_caracteres"
                                           class="form-control @error('min_caracteres') is-invalid @enderror"
                                           value="{{ old('min_caracteres') }}"
                                           min="0"
                                           placeholder="0">
                                    @error('min_caracteres')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_caracteres" class="form-label">
                                        <i class="fas fa-text-width text-danger"></i> Máximo de caracteres
                                    </label>
                                    <input type="number" name="max_caracteres" id="max_caracteres"
                                           class="form-control @error('max_caracteres') is-invalid @enderror"
                                           value="{{ old('max_caracteres') }}"
                                           min="1"
                                           placeholder="500">
                                    @error('max_caracteres')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    `);
                    break;

                case 'escala_lineal':
                    contenedor.append(`
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="escala_min" class="form-label">
                                        <i class="fas fa-arrow-down text-danger"></i> Escala mínima
                                    </label>
                                    <input type="number" name="escala_min" id="escala_min"
                                           class="form-control @error('escala_min') is-invalid @enderror"
                                           value="{{ old('escala_min', 1) }}"
                                           placeholder="1">
                                    @error('escala_min')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="escala_max" class="form-label">
                                        <i class="fas fa-arrow-up text-success"></i> Escala máxima
                                    </label>
                                    <input type="number" name="escala_max" id="escala_max"
                                           class="form-control @error('escala_max') is-invalid @enderror"
                                           value="{{ old('escala_max', 5) }}"
                                           placeholder="5">
                                    @error('escala_max')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="escala_etiqueta_min" class="form-label">
                                        <i class="fas fa-tag text-danger"></i> Etiqueta mínima
                                    </label>
                                    <input type="text" name="escala_etiqueta_min" id="escala_etiqueta_min"
                                           class="form-control @error('escala_etiqueta_min') is-invalid @enderror"
                                           value="{{ old('escala_etiqueta_min') }}"
                                           placeholder="Muy malo">
                                    @error('escala_etiqueta_min')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="escala_etiqueta_max" class="form-label">
                                        <i class="fas fa-tag text-success"></i> Etiqueta máxima
                                    </label>
                                    <input type="text" name="escala_etiqueta_max" id="escala_etiqueta_max"
                                           class="form-control @error('escala_etiqueta_max') is-invalid @enderror"
                                           value="{{ old('escala_etiqueta_max') }}"
                                           placeholder="Excelente">
                                    @error('escala_etiqueta_max')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    `);
                    break;

                case 'carga_archivos':
                    contenedor.append(`
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipos_archivo_permitidos" class="form-label">
                                        <i class="fas fa-file text-info"></i> Tipos de archivo permitidos
                                    </label>
                                    <input type="text" name="tipos_archivo_permitidos" id="tipos_archivo_permitidos"
                                           class="form-control @error('tipos_archivo_permitidos') is-invalid @enderror"
                                           value="{{ old('tipos_archivo_permitidos', 'pdf,doc,docx,jpg,jpeg,png') }}"
                                           placeholder="pdf,doc,docx,jpg,jpeg,png">
                                    @error('tipos_archivo_permitidos')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Separa los tipos con comas
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tamano_max_archivo" class="form-label">
                                        <i class="fas fa-weight text-warning"></i> Tamaño máximo (MB)
                                    </label>
                                    <input type="number" name="tamano_max_archivo" id="tamano_max_archivo"
                                           class="form-control @error('tamano_max_archivo') is-invalid @enderror"
                                           value="{{ old('tamano_max_archivo', 10) }}"
                                           min="1"
                                           max="100"
                                           placeholder="10">
                                    @error('tamano_max_archivo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    `);
                    break;

                case 'ubicacion_mapa':
                    contenedor.append(`
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="latitud_default" class="form-label">
                                        <i class="fas fa-map-marker-alt text-danger"></i> Latitud por defecto
                                    </label>
                                    <input type="number" step="any" name="latitud_default" id="latitud_default"
                                           class="form-control @error('latitud_default') is-invalid @enderror"
                                           value="{{ old('latitud_default', 4.5709) }}"
                                           placeholder="4.5709">
                                    @error('latitud_default')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="longitud_default" class="form-label">
                                        <i class="fas fa-map-marker-alt text-success"></i> Longitud por defecto
                                    </label>
                                    <input type="number" step="any" name="longitud_default" id="longitud_default"
                                           class="form-control @error('longitud_default') is-invalid @enderror"
                                           value="{{ old('longitud_default', -74.2973) }}"
                                           placeholder="-74.2973">
                                    @error('longitud_default')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="zoom_default" class="form-label">
                                        <i class="fas fa-search-plus text-info"></i> Zoom por defecto
                                    </label>
                                    <input type="number" name="zoom_default" id="zoom_default"
                                           class="form-control @error('zoom_default') is-invalid @enderror"
                                           value="{{ old('zoom_default', 10) }}"
                                           min="1"
                                           max="20"
                                           placeholder="10">
                                    @error('zoom_default')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    `);
                    break;
            }
        }

        // Evento para cambiar tipo de pregunta
        $('#tipo').change(function() {
            mostrarCamposCondicionales($(this).val());
        });

        // Mostrar campos iniciales si hay un tipo seleccionado
        if ($('#tipo').val()) {
            mostrarCamposCondicionales($('#tipo').val());
        }

        // Validación del formulario
        $('#form-pregunta').submit(function(e) {
            const texto = $('#texto').val().trim();
            const tipo = $('#tipo').val();

            if (!texto) {
                e.preventDefault();
                alert('Por favor, ingresa el texto de la pregunta.');
                $('#texto').focus();
                return false;
            }

            if (!tipo) {
                e.preventDefault();
                alert('Por favor, selecciona un tipo de pregunta.');
                $('#tipo').focus();
                return false;
            }

            return true;
        });
    });
</script>
@endsection
