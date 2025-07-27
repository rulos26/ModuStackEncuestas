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
                            <label for="descripcion" class="form-label">
                                <i class="fas fa-info-circle"></i> Descripción (opcional)
                            </label>
                            <textarea name="descripcion" id="descripcion"
                                      class="form-control @error('descripcion') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Descripción adicional de la pregunta">{{ old('descripcion') }}</textarea>
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
                            <select name="tipo" id="tipo"
                                    class="form-control @error('tipo') is-invalid @enderror"
                                    required>
                                <option value="">Selecciona el tipo de pregunta</option>
                                @foreach(App\Models\Pregunta::getTiposDisponibles() as $tipo => $config)
                                    <option value="{{ $tipo }}" {{ old('tipo') == $tipo ? 'selected' : '' }}>
                                        {{ $config['icono'] }} {{ $config['nombre'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
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
                                                           value="{{ old('placeholder') }}"
                                                           placeholder="Ej: Ingresa tu nombre completo">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="min_caracteres">Mín. caracteres</label>
                                                    <input type="number" name="min_caracteres" id="min_caracteres"
                                                           class="form-control"
                                                           value="{{ old('min_caracteres') }}"
                                                           min="0">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="max_caracteres">Máx. caracteres</label>
                                                    <input type="number" name="max_caracteres" id="max_caracteres"
                                                           class="form-control"
                                                           value="{{ old('max_caracteres') }}"
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
                                                           value="{{ old('escala_min', 0) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="escala_max">Valor máximo</label>
                                                    <input type="number" name="escala_max" id="escala_max"
                                                           class="form-control"
                                                           value="{{ old('escala_max', 10) }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="escala_etiqueta_min">Etiqueta mínima</label>
                                                    <input type="text" name="escala_etiqueta_min" id="escala_etiqueta_min"
                                                           class="form-control"
                                                           value="{{ old('escala_etiqueta_min') }}"
                                                           placeholder="Ej: Muy malo">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="escala_etiqueta_max">Etiqueta máxima</label>
                                                    <input type="text" name="escala_etiqueta_max" id="escala_etiqueta_max"
                                                           class="form-control"
                                                           value="{{ old('escala_etiqueta_max') }}"
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
                                                           value="{{ old('tipos_archivo_permitidos') }}"
                                                           placeholder="Ej: .pdf,.doc,.docx,.jpg,.png">
                                                    <small class="form-text text-muted">Separar con comas: .pdf, .doc, .jpg</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="tamano_max_archivo">Tamaño máximo (MB)</label>
                                                    <input type="number" name="tamano_max_archivo" id="tamano_max_archivo"
                                                           class="form-control"
                                                           value="{{ old('tamano_max_archivo', 10) }}"
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
                                                           value="{{ old('latitud_default') }}"
                                                           step="0.000001"
                                                           placeholder="Ej: 4.710989">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="longitud_default">Longitud por defecto</label>
                                                    <input type="number" name="longitud_default" id="longitud_default"
                                                           class="form-control"
                                                           value="{{ old('longitud_default') }}"
                                                           step="0.000001"
                                                           placeholder="Ej: -74.072092">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="zoom_default">Zoom por defecto</label>
                                                    <input type="number" name="zoom_default" id="zoom_default"
                                                           class="form-control"
                                                           value="{{ old('zoom_default', 10) }}"
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

                    <h6>Preguntas Actuales:</h6>
                    @if($encuesta->preguntas->count() > 0)
                        <ul class="list-unstyled">
                            @foreach($encuesta->preguntas as $pregunta)
                                <li class="mb-2">
                                    <small class="text-muted">{{ $pregunta->orden }}.</small>
                                    {{ Str::limit($pregunta->texto, 30) }}
                                    <span class="badge badge-info">{{ $pregunta->getNombreTipo() }}</span>
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
                            Selecciona el tipo de pregunta que mejor se adapte a la información que necesitas recopilar.
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

    // Mostrar configuraciones específicas según el tipo seleccionado
    $('#tipo').on('change', function() {
        const tipo = $(this).val();

        // Ocultar todas las configuraciones
        $('.config-tipo').hide();
        $('.tipo-info').hide();

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

            // Mostrar información del tipo
            $(`.tipo-info[data-tipo="${tipo}"]`).show();
            $('#configuraciones-especificas').show();
        } else {
            $('#configuraciones-especificas').hide();
        }
    });

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

    // Trigger change event on load if tipo is already selected
    if ($('#tipo').val()) {
        $('#tipo').trigger('change');
    }
});
</script>
@endsection
