@extends('adminlte::page')

@section('title', 'Agregar Pregunta')

@section('content_header')
    <h1>
        <i class="fas fa-question-circle"></i> Agregar Pregunta
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Agregar Pregunta</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL FLUJO -->
    <div class="progress mb-4" style="height: 25px;">
        <div class="progress-bar bg-success" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <strong>1. Crear Encuesta ✓</strong>
        </div>
        <div class="progress-bar bg-primary" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <strong>2. Agregar Preguntas</strong>
        </div>
        <div class="progress-bar bg-secondary" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <strong>3. Configurar Respuestas</strong>
        </div>
        <div class="progress-bar bg-secondary" role="progressbar" style="width: 25%;" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
            <strong>4. Configurar Lógica</strong>
        </div>
    </div>

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
            <h5><i class="icon fas fa-ban"></i> Errores de validación</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus"></i> Nueva Pregunta
                    </h3>
                </div>
                <div class="card-body">
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
                            <textarea name="descripcion" id="descripcion" rows="3"
                                      class="form-control @error('descripcion') is-invalid @enderror"
                                      placeholder="Información adicional o contexto para la pregunta">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="tipo" class="form-label">
                                <i class="fas fa-list"></i> Tipo de pregunta
                            </label>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" id="tipoDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span id="tipoSeleccionado">
                                        <i class="fas fa-question-circle"></i> Selecciona el tipo de pregunta
                                    </span>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="tipoDropdown">
                                    @foreach(App\Models\Pregunta::getTiposDisponibles() as $tipo => $config)
                                        <li>
                                            <a class="dropdown-item" href="#" data-tipo="{{ $tipo }}" data-icono="{{ $config['icono'] }}" data-nombre="{{ $config['nombre'] }}">
                                                <i class="{{ $config['icono'] }}"></i> {{ $config['nombre'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <input type="hidden" name="tipo" id="tipo" value="{{ old('tipo') }}" required>
                            </div>
                            @error('tipo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                El tipo determina cómo se mostrará y validará la respuesta
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="obligatoria" id="obligatoria"
                                       class="form-check-input @error('obligatoria') is-invalid @enderror"
                                       value="1" {{ old('obligatoria') ? 'checked' : '' }}>
                                <label for="obligatoria" class="form-check-label">
                                    <i class="fas fa-exclamation-triangle"></i> ¿Es obligatoria?
                                </label>
                                @error('obligatoria')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Configuraciones específicas por tipo -->
                        <div id="configuraciones-especificas" style="display: none;">
                            <div id="config-texto" class="config-tipo" style="display: none;">
                                <div class="form-group">
                                    <label for="placeholder">Placeholder (opcional)</label>
                                    <input type="text" name="placeholder" id="placeholder" class="form-control"
                                           value="{{ old('placeholder') }}" placeholder="Texto de ejemplo">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="min_caracteres">Mínimo de caracteres</label>
                                            <input type="number" name="min_caracteres" id="min_caracteres"
                                                   class="form-control" value="{{ old('min_caracteres') }}" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="max_caracteres">Máximo de caracteres</label>
                                            <input type="number" name="max_caracteres" id="max_caracteres"
                                                   class="form-control" value="{{ old('max_caracteres') }}" min="1">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="config-escala" class="config-tipo" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="escala_min">Valor mínimo</label>
                                            <input type="number" name="escala_min" id="escala_min"
                                                   class="form-control" value="{{ old('escala_min', 1) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="escala_max">Valor máximo</label>
                                            <input type="number" name="escala_max" id="escala_max"
                                                   class="form-control" value="{{ old('escala_max', 10) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="escala_etiqueta_min">Etiqueta mínimo</label>
                                            <input type="text" name="escala_etiqueta_min" id="escala_etiqueta_min"
                                                   class="form-control" value="{{ old('escala_etiqueta_min') }}" placeholder="Ej: Muy malo">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="escala_etiqueta_max">Etiqueta máximo</label>
                                            <input type="text" name="escala_etiqueta_max" id="escala_etiqueta_max"
                                                   class="form-control" value="{{ old('escala_etiqueta_max') }}" placeholder="Ej: Excelente">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="config-archivo" class="config-tipo" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipos_archivo_permitidos">Tipos de archivo permitidos</label>
                                            <input type="text" name="tipos_archivo_permitidos" id="tipos_archivo_permitidos"
                                                   class="form-control" value="{{ old('tipos_archivo_permitidos') }}"
                                                   placeholder="Ej: .pdf,.doc,.jpg">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tamano_max_archivo">Tamaño máximo (MB)</label>
                                            <input type="number" name="tamano_max_archivo" id="tamano_max_archivo"
                                                   class="form-control" value="{{ old('tamano_max_archivo', 10) }}" min="1" max="100">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="config-ubicacion" class="config-tipo" style="display: none;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="latitud_default">Latitud por defecto</label>
                                            <input type="number" name="latitud_default" id="latitud_default"
                                                   class="form-control" value="{{ old('latitud_default') }}" step="any">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="longitud_default">Longitud por defecto</label>
                                            <input type="number" name="longitud_default" id="longitud_default"
                                                   class="form-control" value="{{ old('longitud_default') }}" step="any">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="zoom_default">Zoom por defecto</label>
                                            <input type="number" name="zoom_default" id="zoom_default"
                                                   class="form-control" value="{{ old('zoom_default', 10) }}" min="1" max="20">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Pregunta
                            </button>
                            <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-secondary">
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
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información
                    </h3>
                </div>
                <div class="card-body">
                    <div id="tipos-info">
                        <p class="text-muted">Selecciona un tipo de pregunta para ver más información.</p>
                    </div>

                    <hr>

                    <h5><i class="fas fa-list"></i> Preguntas existentes</h5>
                    @if($encuesta->preguntas->count() > 0)
                        <ul class="list-group">
                            @foreach($encuesta->preguntas as $pregunta)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $loop->iteration }}.</strong> {{ Str::limit($pregunta->texto, 30) }}
                                        <span class="badge badge-{{ $pregunta->obligatoria ? 'danger' : 'info' }} ml-2">
                                            {{ $pregunta->obligatoria ? 'Obligatoria' : 'Opcional' }}
                                        </span>
                                    </div>
                                    <span class="badge badge-secondary">{{ $pregunta->getNombreTipo() }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No hay preguntas agregadas aún.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Custom dropdown functionality
    $('.dropdown-item').click(function(e) {
        e.preventDefault();
        var tipo = $(this).data('tipo');
        var icono = $(this).data('icono');
        var nombre = $(this).data('nombre');

        $('#tipo').val(tipo);
        $('#tipoSeleccionado').html('<i class="' + icono + '"></i> ' + nombre);

        mostrarConfiguracionesEspecificas(tipo);
        mostrarInformacionTipo(tipo);
    });

    function mostrarConfiguracionesEspecificas(tipo) {
        $('.config-tipo').hide();
        $('#configuraciones-especificas').show();

        if (['respuesta_corta', 'parrafo'].includes(tipo)) {
            $('#config-texto').show();
        } else if (tipo === 'escala_lineal') {
            $('#config-escala').show();
        } else if (tipo === 'carga_archivos') {
            $('#config-archivo').show();
        } else if (tipo === 'ubicacion_mapa') {
            $('#config-ubicacion').show();
        } else {
            $('#configuraciones-especificas').hide();
        }
    }

    function mostrarInformacionTipo(tipo) {
        var tipos = @json(App\Models\Pregunta::getTiposDisponibles());
        var config = tipos[tipo];

        if (config) {
            var html = `
                <h5><i class="${config.icono}"></i> ${config.nombre}</h5>
                <p>${config.descripcion}</p>
                ${config.necesita_respuestas ? '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Este tipo requiere respuestas predefinidas.</div>' : ''}
            `;
            $('#tipos-info').html(html);
        }
    }

    // Validación de escala
    $('#escala_max').on('change', function() {
        var min = parseInt($('#escala_min').val()) || 0;
        var max = parseInt($(this).val()) || 0;

        if (max <= min) {
            alert('El valor máximo debe ser mayor al mínimo.');
            $(this).val(min + 1);
        }
    });
});
</script>
@endsection

@section('css')
<style>
.dropdown-item {
    cursor: pointer;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.dropdown-item i {
    width: 20px;
    text-align: center;
    margin-right: 8px;
}

.progress {
    border-radius: 15px;
}

.progress-bar {
    font-size: 12px;
    line-height: 25px;
}
</style>
@endsection
