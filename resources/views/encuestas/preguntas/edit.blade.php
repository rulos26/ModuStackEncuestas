@extends('adminlte::page')

@section('title', 'Editar Pregunta')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i> Editar Pregunta
                    </h3>
                    <h4 class="text-muted">{{ $encuesta->titulo ?? 'Sin título' }}</h4>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
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

                    <form action="{{ route('encuestas.preguntas.update', [$encuesta->id, $pregunta->id]) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="texto" class="form-label">
                                <i class="fas fa-question-circle"></i> Texto de la pregunta
                            </label>
                            <input type="text" name="texto" id="texto"
                                   class="form-control @error('texto') is-invalid @enderror"
                                   value="{{ old('texto', $pregunta->texto) }}"
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
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-info-circle"></i> Descripción (opcional)
                            </label>
                            <textarea name="descripcion" id="descripcion"
                                      class="form-control @error('descripcion') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Descripción adicional de la pregunta">{{ old('descripcion', $pregunta->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Información adicional para ayudar al usuario a responder
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="tipo" class="form-label">
                                <i class="fas fa-list"></i> Tipo de pregunta
                            </label>

                            <!-- Dropdown personalizado para mostrar iconos -->
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100 text-left"
                                        type="button"
                                        id="tipoDropdown"
                                        data-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false">
                                    <span id="tipoSeleccionado">
                                        <i class="{{ $pregunta->getIconoTipo() }}"></i> {{ $pregunta->getNombreTipo() }}
                                    </span>
                                </button>
                                <div class="dropdown-menu w-100" aria-labelledby="tipoDropdown">
                                    @foreach(App\Models\Pregunta::getTiposDisponibles() as $tipo => $config)
                                        <a class="dropdown-item" href="#" data-tipo="{{ $tipo }}" data-icono="{{ $config['icono'] }}" data-nombre="{{ $config['nombre'] }}">
                                            <i class="{{ $config['icono'] }}"></i> {{ $config['nombre'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Campo oculto para el valor real -->
                            <input type="hidden" name="tipo" id="tipo" value="{{ old('tipo', $pregunta->tipo) }}" required>

                            @error('tipo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                El tipo determina cómo se mostrará y validará la respuesta
                            </small>
                        </div>

                        <!-- Configuraciones específicas por tipo -->
                        <div id="configuraciones-especificas" style="display: none;">
                            <!-- Configuración para texto -->
                            <div id="config-texto" class="config-tipo" style="display: none;">
                                <div class="card card-info">
                                    <div class="card-header">
                                        <h5><i class="fas fa-font"></i> Configuración de texto</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="placeholder">Placeholder</label>
                                                    <input type="text" name="placeholder" id="placeholder"
                                                           class="form-control"
                                                           value="{{ old('placeholder', $pregunta->placeholder) }}"
                                                           placeholder="Ej: Ingresa tu nombre completo">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="min_caracteres">Mín. caracteres</label>
                                                    <input type="number" name="min_caracteres" id="min_caracteres"
                                                           class="form-control"
                                                           value="{{ old('min_caracteres', $pregunta->min_caracteres) }}"
                                                           min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="max_caracteres">Máx. caracteres</label>
                                                    <input type="number" name="max_caracteres" id="max_caracteres"
                                                           class="form-control"
                                                           value="{{ old('max_caracteres', $pregunta->max_caracteres) }}"
                                                           min="1">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración para escala -->
                            <div id="config-escala" class="config-tipo" style="display: none;">
                                <div class="card card-warning">
                                    <div class="card-header">
                                        <h5><i class="fas fa-sliders-h"></i> Configuración de escala</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="escala_min">Valor mínimo</label>
                                                    <input type="number" name="escala_min" id="escala_min"
                                                           class="form-control"
                                                           value="{{ old('escala_min', $pregunta->escala_min) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="escala_max">Valor máximo</label>
                                                    <input type="number" name="escala_max" id="escala_max"
                                                           class="form-control"
                                                           value="{{ old('escala_max', $pregunta->escala_max) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="escala_etiqueta_min">Etiqueta mínima</label>
                                                    <input type="text" name="escala_etiqueta_min" id="escala_etiqueta_min"
                                                           class="form-control"
                                                           value="{{ old('escala_etiqueta_min', $pregunta->escala_etiqueta_min) }}"
                                                           placeholder="Ej: Muy malo">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="escala_etiqueta_max">Etiqueta máxima</label>
                                                    <input type="text" name="escala_etiqueta_max" id="escala_etiqueta_max"
                                                           class="form-control"
                                                           value="{{ old('escala_etiqueta_max', $pregunta->escala_etiqueta_max) }}"
                                                           placeholder="Ej: Excelente">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración para archivos -->
                            <div id="config-archivo" class="config-tipo" style="display: none;">
                                <div class="card card-success">
                                    <div class="card-header">
                                        <h5><i class="fas fa-upload"></i> Configuración de archivos</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tipos_archivo_permitidos">Tipos permitidos</label>
                                                    <input type="text" name="tipos_archivo_permitidos" id="tipos_archivo_permitidos"
                                                           class="form-control"
                                                           value="{{ old('tipos_archivo_permitidos', $pregunta->tipos_archivo_permitidos) }}"
                                                           placeholder="Ej: .pdf,.doc,.docx,.jpg,.png">
                                                    <small class="form-text text-muted">Separar con comas: .pdf, .doc, .jpg</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tamano_max_archivo">Tamaño máximo (MB)</label>
                                                    <input type="number" name="tamano_max_archivo" id="tamano_max_archivo"
                                                           class="form-control"
                                                           value="{{ old('tamano_max_archivo', $pregunta->tamano_max_archivo) }}"
                                                           min="1" max="100">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración para ubicación -->
                            <div id="config-ubicacion" class="config-tipo" style="display: none;">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h5><i class="fas fa-map-marker-alt"></i> Configuración de ubicación</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="latitud_default">Latitud por defecto</label>
                                                    <input type="number" name="latitud_default" id="latitud_default"
                                                           class="form-control"
                                                           value="{{ old('latitud_default', $pregunta->latitud_default) }}"
                                                           step="0.000001"
                                                           placeholder="Ej: 4.710989">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="longitud_default">Longitud por defecto</label>
                                                    <input type="number" name="longitud_default" id="longitud_default"
                                                           class="form-control"
                                                           value="{{ old('longitud_default', $pregunta->longitud_default) }}"
                                                           step="0.000001"
                                                           placeholder="Ej: -74.072092">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="zoom_default">Zoom por defecto</label>
                                                    <input type="number" name="zoom_default" id="zoom_default"
                                                           class="form-control"
                                                           value="{{ old('zoom_default', $pregunta->zoom_default) }}"
                                                           min="1" max="20">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="orden" class="form-label">
                                <i class="fas fa-sort-numeric-up"></i> Orden de la pregunta
                            </label>
                            <input type="number" name="orden" id="orden"
                                   class="form-control @error('orden') is-invalid @enderror"
                                   value="{{ old('orden', $pregunta->orden) }}"
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
                                       {{ old('obligatoria', $pregunta->obligatoria) ? 'checked' : '' }}>
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
                                <i class="fas fa-save"></i> Actualizar Pregunta
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
                    <h6>Pregunta Actual:</h6>
                    <div class="alert alert-info">
                        <strong>{{ $pregunta->texto }}</strong><br>
                        <small>Tipo: {{ $pregunta->getNombreTipo() }}</small><br>
                        <small>Orden: {{ $pregunta->orden }}</small><br>
                        <small>Obligatoria: {{ $pregunta->obligatoria ? 'Sí' : 'No' }}</small>
                    </div>

                    <hr>

                    <h6>Tipos de Preguntas:</h6>
                    <div id="tipos-info">
                        @foreach(App\Models\Pregunta::getTiposDisponibles() as $tipo => $config)
                            <div class="tipo-info" data-tipo="{{ $tipo }}" style="display: none;">
                                <h6>{{ $config['nombre'] }}</h6>
                                <p class="text-muted">{{ $config['descripcion'] }}</p>
                                @if($config['necesita_respuestas'])
                                    <div class="alert alert-info">
                                        <i class="fas fa-info"></i> Este tipo requiere configurar opciones de respuesta.
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="alert alert-warning">
                        <h6><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h6>
                        <p class="mb-0">
                            Al cambiar el tipo de pregunta, las respuestas existentes podrían no ser compatibles.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.dropdown-item {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #f8f9fa;
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item i {
    margin-right: 0.5rem;
    width: 16px;
    text-align: center;
}

#tipoDropdown {
    text-align: left;
    position: relative;
    padding: 0.375rem 0.75rem;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
    background-color: #fff;
    color: #495057;
}

#tipoDropdown:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

#tipoDropdown:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #ced4da;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.dropdown-toggle::after {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Manejar el dropdown personalizado de tipos
    $('.dropdown-item').on('click', function(e) {
        e.preventDefault();

        const tipo = $(this).data('tipo');
        const icono = $(this).data('icono');
        const nombre = $(this).data('nombre');

        // Actualizar el botón del dropdown
        $('#tipoSeleccionado').html(`<i class="${icono}"></i> ${nombre}`);

        // Actualizar el campo oculto
        $('#tipo').val(tipo);

        // Mostrar configuraciones específicas según el tipo seleccionado
        mostrarConfiguracionesEspecificas(tipo);

        // Mostrar información del tipo
        mostrarInformacionTipo(tipo);
    });

    // Función para mostrar configuraciones específicas
    function mostrarConfiguracionesEspecificas(tipo) {
        // Ocultar todas las configuraciones
        $('.config-tipo').hide();

        if (tipo) {
            // Mostrar configuración específica
            if (['respuesta_corta', 'parrafo'].includes(tipo)) {
                $('#config-texto').show();
            } else if (tipo === 'escala_lineal') {
                $('#config-escala').show();
            } else if (tipo === 'carga_archivos') {
                $('#config-archivo').show();
            } else if (tipo === 'ubicacion_mapa') {
                $('#config-ubicacion').show();
            }

            $('#configuraciones-especificas').show();
        } else {
            $('#configuraciones-especificas').hide();
        }
    }

    // Función para mostrar información del tipo
    function mostrarInformacionTipo(tipo) {
        $('.tipo-info').hide();
        if (tipo) {
            $(`.tipo-info[data-tipo="${tipo}"]`).show();
        }
    }

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

        // Validaciones específicas por tipo
        if (tipo === 'escala_lineal') {
            const escalaMin = parseInt($('#escala_min').val()) || 0;
            const escalaMax = parseInt($('#escala_max').val()) || 10;

            if (escalaMax <= escalaMin) {
                errorMessage += 'El valor máximo de la escala debe ser mayor al mínimo.\n';
                isValid = false;
            }
        }

        if (!isValid) {
            e.preventDefault();
            alert('Por favor, corrige los siguientes errores:\n\n' + errorMessage);
        }
    });

    // Inicializar con el tipo actual
    const tipoActual = $('#tipo').val();
    if (tipoActual) {
        mostrarConfiguracionesEspecificas(tipoActual);
        mostrarInformacionTipo(tipoActual);
    }
});
</script>
@endsection
