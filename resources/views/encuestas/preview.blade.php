@extends('adminlte::page')

@section('title', 'Vista Previa de Encuesta')

@section('content_header')
    <h1>
        <i class="fas fa-eye"></i> Vista Previa de Encuesta
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Vista Previa</li>
        </ol>
    </nav>

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

    <!-- INFORMACIÓN DE LA ENCUESTA -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información de la Encuesta
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Título:</strong> {{ $encuesta->titulo }}</p>
                            <p><strong>Empresa:</strong> {{ $encuesta->empresa->nombre_legal ?? 'No asignada' }}</p>
                            <p><strong>Estado:</strong>
                                <span class="badge badge-{{ $encuesta->estado === 'publicada' ? 'success' : ($encuesta->estado === 'enviada' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($encuesta->estado) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total preguntas:</strong> {{ $encuesta->preguntas->count() }}</p>
                            <p><strong>Preguntas obligatorias:</strong> {{ $preguntasObligatorias }}</p>
                            <p><strong>Preguntas opcionales:</strong> {{ $preguntasOpcionales }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Acciones
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('encuestas.logica.resumen', $encuesta->id) }}" class="btn btn-warning">
                            <i class="fas fa-clipboard-check"></i> Ver Resumen de Lógica
                        </a>
                        <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info">
                            <i class="fas fa-arrow-left"></i> Volver a la Encuesta
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- NAVEGACIÓN POR BLOQUES -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Preguntas (Bloque {{ $bloqueActual }} de {{ $totalBloques }})
                    </h3>
                    <div class="card-tools">
                        @if($bloqueActual > 1)
                            <a href="{{ route('encuestas.preview', $encuesta->id) }}?bloque={{ $bloqueActual - 1 }}" class="btn btn-sm btn-secondary">
                                <i class="fas fa-chevron-left"></i> Anterior
                            </a>
                        @endif

                        @if($bloqueActual < $totalBloques)
                            <a href="{{ route('encuestas.preview', $encuesta->id) }}?bloque={{ $bloqueActual + 1 }}" class="btn btn-sm btn-primary">
                                Siguiente <i class="fas fa-chevron-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- PROGRESO DE BLOQUES -->
                    <div class="progress mb-3" style="height: 20px;">
                        @for($i = 1; $i <= $totalBloques; $i++)
                            <div class="progress-bar {{ $i <= $bloqueActual ? 'bg-primary' : 'bg-light' }}"
                                 style="width: {{ 100 / $totalBloques }}%;"
                                 title="Bloque {{ $i }}">
                                @if($i == $bloqueActual)
                                    <strong>{{ $i }}</strong>
                                @endif
                            </div>
                        @endfor
                    </div>

                    <!-- PREGUNTAS DEL BLOQUE -->
                    @foreach($preguntasBloque as $pregunta)
                        <div class="card mb-3 border-{{ $pregunta->obligatoria ? 'danger' : 'info' }}">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="fas fa-question-circle"></i>
                                        Pregunta {{ $pregunta->orden }}
                                        @if($pregunta->obligatoria)
                                            <span class="badge badge-danger">Obligatoria</span>
                                        @else
                                            <span class="badge badge-info">Opcional</span>
                                        @endif
                                    </h5>
                                    <small class="text-muted">
                                        <i class="fas fa-tag"></i> {{ $pregunta->getNombreTipo() }}
                                    </small>
                                </div>

                                <!-- ACCIONES DE EDICIÓN -->
                                @if($encuesta->encuestas_enviadas == 0)
                                    <div class="btn-group">
                                        <a href="{{ route('encuestas.preguntas.edit', [$encuesta->id, $pregunta->id]) }}"
                                           class="btn btn-sm btn-warning"
                                           title="Editar pregunta">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-danger"
                                                onclick="confirmarEliminacion({{ $pregunta->id }})"
                                                title="Eliminar pregunta">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            <div class="card-body">
                                <h6 class="card-title">{{ $pregunta->texto }}</h6>

                                @if($pregunta->descripcion)
                                    <p class="text-muted">{{ $pregunta->descripcion }}</p>
                                @endif

                                <!-- MOSTRAR RESPUESTAS SEGÚN EL TIPO -->
                                @switch($pregunta->tipo)
                                    @case('seleccion_unica')
                                        <div class="form-group">
                                            @foreach($pregunta->respuestas as $respuesta)
                                                <div class="form-check">
                                                    <input type="radio" class="form-check-input" name="pregunta_{{ $pregunta->id }}" id="respuesta_{{ $respuesta->id }}" disabled>
                                                    <label class="form-check-label" for="respuesta_{{ $respuesta->id }}">
                                                        {{ $respuesta->texto }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('seleccion_multiple')
                                        <div class="form-group">
                                            @foreach($pregunta->respuestas as $respuesta)
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="pregunta_{{ $pregunta->id }}[]" id="respuesta_{{ $respuesta->id }}" disabled>
                                                    <label class="form-check-label" for="respuesta_{{ $respuesta->id }}">
                                                        {{ $respuesta->texto }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                        @break

                                    @case('lista_desplegable')
                                        <div class="form-group">
                                            <select class="form-control" disabled>
                                                <option value="">Seleccione una opción</option>
                                                @foreach($pregunta->respuestas as $respuesta)
                                                    <option value="{{ $respuesta->id }}">{{ $respuesta->texto }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @break

                                    @case('escala_lineal')
                                        <div class="form-group">
                                            <div class="d-flex justify-content-between">
                                                <span>{{ $pregunta->escala_etiqueta_min ?? '1' }}</span>
                                                <div class="flex-grow-1 mx-2">
                                                    <input type="range" class="form-control-range" min="{{ $pregunta->escala_min ?? 1 }}" max="{{ $pregunta->escala_max ?? 5 }}" disabled>
                                                </div>
                                                <span>{{ $pregunta->escala_etiqueta_max ?? '5' }}</span>
                                            </div>
                                        </div>
                                        @break

                                    @default
                                        <div class="form-group">
                                            <input type="text" class="form-control" placeholder="{{ $pregunta->placeholder ?? 'Escriba su respuesta' }}" disabled>
                                        </div>
                                @endswitch
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DE CONFIRMACIÓN PARA ELIMINAR -->
    <div class="modal fade" id="modalEliminar" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar esta pregunta?</p>
                    <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <form id="formEliminar" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
function confirmarEliminacion(preguntaId) {
    $('#formEliminar').attr('action', '{{ route("encuestas.preview", $encuesta->id) }}/preguntas/' + preguntaId + '/eliminar');
    $('#modalEliminar').modal('show');
}

$(document).ready(function() {
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endsection
