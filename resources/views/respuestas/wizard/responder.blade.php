@extends('adminlte::page')

@section('title', 'Wizard de Respuestas - Responder Pregunta')

@section('content_header')
    <h1>
        <i class="fas fa-clipboard-check"></i> Wizard de Respuestas
        <small>Paso 2: Responder Pregunta - {{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('respuestas.index') }}">Respuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('respuestas.wizard.index') }}">Wizard de Respuestas</a></li>
            <li class="breadcrumb-item active">Responder Pregunta</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL WIZARD -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 1. Seleccionar</strong>
                </div>
                <div class="progress-bar bg-primary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-question-circle"></i> 2. Responder</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-list-check"></i> 3. Resumen</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 4. Confirmar</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-flag-checkered"></i> 5. Finalizar</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- INDICADOR DE PROGRESO -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pregunta Actual</span>
                    <span class="info-box-number">{{ $preguntaIndex + 1 }} de {{ $totalPreguntas }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: {{ (($preguntaIndex + 1) / $totalPreguntas) * 100 }}%"></div>
                    </div>
                    <span class="progress-description">
                        {{ round((($preguntaIndex + 1) / $totalPreguntas) * 100) }}% completado
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Respuestas Guardadas</span>
                    <span class="info-box-number">{{ Session::get('wizard_respuestas_count', 0) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        En esta sesión
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Encuesta</span>
                    <span class="info-box-number">{{ Str::limit($encuesta->titulo, 15) }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $encuesta->empresa->nombre ?? 'Sin empresa' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

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

    <!-- FORMULARIO DE RESPUESTA -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-question-circle"></i> Pregunta {{ $preguntaIndex + 1 }} de {{ $totalPreguntas }}
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    <i class="fas fa-tag"></i> {{ ucfirst(str_replace('_', ' ', $preguntaActual->tipo)) }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('respuestas.wizard.store') }}" method="POST" id="form-respuesta">
                @csrf

                <!-- PREGUNTA -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-question-circle text-primary"></i>
                        <strong>{{ $preguntaActual->texto }}</strong>
                        @if($preguntaActual->obligatoria)
                            <span class="text-danger">*</span>
                        @endif
                    </label>

                    @if($preguntaActual->descripcion)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> {{ $preguntaActual->descripcion }}
                        </div>
                    @endif

                    <!-- CAMPOS DE RESPUESTA SEGÚN EL TIPO -->
                    <div id="campo-respuesta">
                        @switch($preguntaActual->tipo)
                            @case('respuesta_corta')
                                <input type="text" name="respuesta_texto"
                                       class="form-control @error('respuesta_texto') is-invalid @enderror"
                                       value="{{ old('respuesta_texto') }}"
                                       placeholder="{{ $preguntaActual->placeholder ?? 'Escribe tu respuesta aquí...' }}"
                                       @if($preguntaActual->obligatoria) required @endif
                                       @if($preguntaActual->min_caracteres) minlength="{{ $preguntaActual->min_caracteres }}" @endif
                                       @if($preguntaActual->max_caracteres) maxlength="{{ $preguntaActual->max_caracteres }}" @endif>
                                @error('respuesta_texto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @break

                            @case('parrafo')
                                <textarea name="respuesta_texto" rows="4"
                                          class="form-control @error('respuesta_texto') is-invalid @enderror"
                                          placeholder="{{ $preguntaActual->placeholder ?? 'Escribe tu respuesta aquí...' }}"
                                          @if($preguntaActual->obligatoria) required @endif
                                          @if($preguntaActual->min_caracteres) minlength="{{ $preguntaActual->min_caracteres }}" @endif
                                          @if($preguntaActual->max_caracteres) maxlength="{{ $preguntaActual->max_caracteres }}" @endif>{{ old('respuesta_texto') }}</textarea>
                                @error('respuesta_texto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @break

                            @case('seleccion_unica')
                                @foreach($preguntaActual->respuestas as $respuesta)
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" class="custom-control-input @error('respuesta_id') is-invalid @enderror"
                                               id="respuesta_{{ $respuesta->id }}" name="respuesta_id"
                                               value="{{ $respuesta->id }}"
                                               @if(old('respuesta_id') == $respuesta->id) checked @endif
                                               @if($preguntaActual->obligatoria) required @endif>
                                        <label class="custom-control-label" for="respuesta_{{ $respuesta->id }}">
                                            {{ $respuesta->texto }}
                                        </label>
                                    </div>
                                @endforeach
                                @error('respuesta_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @break

                            @case('casillas_verificacion')
                                @foreach($preguntaActual->respuestas as $respuesta)
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" class="custom-control-input @error('respuesta_ids') is-invalid @enderror"
                                               id="respuesta_{{ $respuesta->id }}" name="respuesta_ids[]"
                                               value="{{ $respuesta->id }}"
                                               @if(old('respuesta_ids') && in_array($respuesta->id, old('respuesta_ids'))) checked @endif>
                                        <label class="custom-control-label" for="respuesta_{{ $respuesta->id }}">
                                            {{ $respuesta->texto }}
                                        </label>
                                    </div>
                                @endforeach
                                @error('respuesta_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                @break

                            @case('escala_lineal')
                                <div class="form-group">
                                    <label class="form-label">Selecciona un valor entre {{ $preguntaActual->escala_min }} y {{ $preguntaActual->escala_max }}:</label>
                                    <div class="d-flex align-items-center">
                                        @if($preguntaActual->escala_etiqueta_min)
                                            <span class="mr-2 text-muted">{{ $preguntaActual->escala_etiqueta_min }}</span>
                                        @endif
                                        <input type="range" name="respuesta_escala"
                                               class="form-control-range @error('respuesta_escala') is-invalid @enderror"
                                               min="{{ $preguntaActual->escala_min }}"
                                               max="{{ $preguntaActual->escala_max }}"
                                               value="{{ old('respuesta_escala', $preguntaActual->escala_min) }}"
                                               @if($preguntaActual->obligatoria) required @endif
                                               oninput="document.getElementById('valor-escala').textContent = this.value">
                                        <span class="ml-2 badge badge-primary" id="valor-escala">{{ old('respuesta_escala', $preguntaActual->escala_min) }}</span>
                                        @if($preguntaActual->escala_etiqueta_max)
                                            <span class="ml-2 text-muted">{{ $preguntaActual->escala_etiqueta_max }}</span>
                                        @endif
                                    </div>
                                    @error('respuesta_escala')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                @break

                            @case('fecha')
                                <input type="date" name="respuesta_fecha"
                                       class="form-control @error('respuesta_fecha') is-invalid @enderror"
                                       value="{{ old('respuesta_fecha') }}"
                                       @if($preguntaActual->obligatoria) required @endif>
                                @error('respuesta_fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @break

                            @case('hora')
                                <input type="time" name="respuesta_hora"
                                       class="form-control @error('respuesta_hora') is-invalid @enderror"
                                       value="{{ old('respuesta_hora') }}"
                                       @if($preguntaActual->obligatoria) required @endif>
                                @error('respuesta_hora')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @break

                            @default
                                <input type="text" name="respuesta_texto"
                                       class="form-control @error('respuesta_texto') is-invalid @enderror"
                                       value="{{ old('respuesta_texto') }}"
                                       placeholder="Escribe tu respuesta aquí..."
                                       @if($preguntaActual->obligatoria) required @endif>
                                @error('respuesta_texto')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        @endswitch
                    </div>

                    <!-- INFORMACIÓN ADICIONAL -->
                    @if($preguntaActual->tipo == 'respuesta_corta' || $preguntaActual->tipo == 'parrafo')
                        @if($preguntaActual->min_caracteres || $preguntaActual->max_caracteres)
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i>
                                @if($preguntaActual->min_caracteres && $preguntaActual->max_caracteres)
                                    Mínimo {{ $preguntaActual->min_caracteres }} y máximo {{ $preguntaActual->max_caracteres }} caracteres.
                                @elseif($preguntaActual->min_caracteres)
                                    Mínimo {{ $preguntaActual->min_caracteres }} caracteres.
                                @elseif($preguntaActual->max_caracteres)
                                    Máximo {{ $preguntaActual->max_caracteres }} caracteres.
                                @endif
                            </small>
                        @endif
                    @endif
                </div>

                <!-- BOTONES DE ACCIÓN -->
                <div class="form-group mt-4">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('respuestas.wizard.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('respuestas.wizard.cancel') }}" class="btn btn-danger btn-block"
                               onclick="return confirm('¿Estás seguro de que quieres cancelar el wizard? Se perderán los datos de la sesión.')">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-arrow-right"></i>
                                @if($preguntaIndex + 1 == $totalPreguntas)
                                    Finalizar Encuesta
                                @else
                                    Siguiente Pregunta
                                @endif
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
        font-size: 0.7rem;
    }

    .info-box {
        min-height: 80px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        font-size: 1.1rem;
    }

    .custom-control-label {
        font-weight: 500;
        cursor: pointer;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn-block {
        font-weight: 600;
    }

    .alert {
        border-radius: 8px;
    }

    .progress {
        border-radius: 15px;
        overflow: hidden;
    }

    .form-control-range {
        height: 6px;
        border-radius: 3px;
    }

    .badge {
        font-size: 1rem;
        padding: 0.5rem 0.75rem;
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Validación del formulario
        $('#form-respuesta').submit(function(e) {
            const tipo = '{{ $preguntaActual->tipo }}';
            const obligatoria = {{ $preguntaActual->obligatoria ? 'true' : 'false' }};

            let isValid = true;
            let errorMessage = '';

            // Validar según el tipo de pregunta
            switch(tipo) {
                case 'respuesta_corta':
                case 'parrafo':
                    const texto = $('input[name="respuesta_texto"], textarea[name="respuesta_texto"]').val().trim();
                    if (obligatoria && !texto) {
                        isValid = false;
                        errorMessage = 'Debes responder esta pregunta.';
                    }
                    break;

                case 'seleccion_unica':
                    const seleccion = $('input[name="respuesta_id"]:checked').val();
                    if (obligatoria && !seleccion) {
                        isValid = false;
                        errorMessage = 'Debes seleccionar una opción.';
                    }
                    break;

                case 'casillas_verificacion':
                    const casillas = $('input[name="respuesta_ids[]"]:checked').length;
                    if (obligatoria && casillas === 0) {
                        isValid = false;
                        errorMessage = 'Debes seleccionar al menos una opción.';
                    }
                    break;

                case 'escala_lineal':
                    const escala = $('input[name="respuesta_escala"]').val();
                    if (obligatoria && !escala) {
                        isValid = false;
                        errorMessage = 'Debes seleccionar un valor en la escala.';
                    }
                    break;

                case 'fecha':
                    const fecha = $('input[name="respuesta_fecha"]').val();
                    if (obligatoria && !fecha) {
                        isValid = false;
                        errorMessage = 'Debes seleccionar una fecha.';
                    }
                    break;

                case 'hora':
                    const hora = $('input[name="respuesta_hora"]').val();
                    if (obligatoria && !hora) {
                        isValid = false;
                        errorMessage = 'Debes seleccionar una hora.';
                    }
                    break;
            }

            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
                return false;
            }

            return true;
        });

        // Actualizar valor de escala en tiempo real
        $('input[name="respuesta_escala"]').on('input', function() {
            $('#valor-escala').textContent = this.value;
        });
    });
</script>
@endsection
