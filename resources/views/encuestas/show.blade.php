@extends('adminlte::page')

@section('content')
<div class="container">
    <h2>Detalle de la Encuesta</h2>
    <p><strong>Título:</strong> {{ $encuesta->titulo }}</p>
    <p><strong>Slug:</strong> {{ $encuesta->slug }}</p>
    <p><strong>Estado:</strong> {{ $encuesta->habilitada ? 'Habilitada' : 'Deshabilitada' }}</p>

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

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> ¡Atención!</h5>
            {{ session('warning') }}
        </div>
    @endif

    <h4>Preguntas</h4>
    @if($encuesta->preguntas->count() > 0)
        <div class="mb-3">
            <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteAllQuestionsModal">
                <i class="fas fa-trash"></i> Eliminar Todas las Preguntas
            </button>
        </div>

        @foreach($encuesta->preguntas as $pregunta)
            <div class="card mb-2">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        {{ $loop->iteration }}. {{ $pregunta->texto }}
                        @if($pregunta->obligatoria)
                            <span class="badge bg-success">Obligatoria</span>
                        @endif
                        <span class="badge bg-info">{{ $pregunta->getNombreTipo() }}</span>
                    </div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('encuestas.preguntas.edit', [$encuesta->id, $pregunta->id]) }}"
                           class="btn btn-warning btn-sm"
                           title="Editar pregunta">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button"
                                class="btn btn-danger btn-sm"
                                onclick="confirmarEliminarPregunta({{ $pregunta->id }}, '{{ $pregunta->texto }}')"
                                title="Eliminar pregunta">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <strong>Tipo:</strong> {{ $pregunta->getNombreTipo() }}<br>
                    @if($pregunta->descripcion)
                        <strong>Descripción:</strong> {{ $pregunta->descripcion }}<br>
                    @endif
                    @if($pregunta->respuestas->count())
                        <strong>Respuestas:</strong>
                        <ul>
                            @foreach($pregunta->respuestas as $respuesta)
                                <li>{{ $respuesta->texto }}</li>
                            @endforeach
                        </ul>
                    @else
                        <em>Sin respuestas configuradas.</em>
                    @endif
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Esta encuesta no tiene preguntas configuradas.
        </div>
    @endif

    <!-- PROGRESO DE CONFIGURACIÓN -->
    <x-progreso-encuesta :encuesta="$encuesta" />

    <div class="mt-4">
        <a href="{{ route('encuestas.preguntas.create', $encuesta->id) }}" class="btn btn-primary me-2">
            <i class="fas fa-plus"></i> Agregar Preguntas
        </a>

        @php
            $preguntasSeleccion = $encuesta->preguntas()->whereIn('tipo', ['seleccion_unica', 'seleccion_multiple'])->count();
        @endphp

        @if($preguntasSeleccion > 0)
            <a href="{{ route('encuestas.respuestas.create', $encuesta->id) }}" class="btn btn-info me-2">
                <i class="fas fa-list-check"></i> Agregar Respuestas
            </a>
        @else
            <button type="button" class="btn btn-info me-2" disabled title="Primero debes agregar preguntas de selección">
                <i class="fas fa-list-check"></i> Agregar Respuestas
            </button>
        @endif

        <a href="{{ route('encuestas.logica.create', $encuesta->id) }}" class="btn btn-warning me-2">
            <i class="fas fa-cogs"></i> Configurar Lógica
        </a>
        <a href="{{ route('encuestas.preview', $encuesta->id) }}" class="btn btn-success me-2">
            <i class="fas fa-eye"></i> Previsualizar Encuesta
        </a>

        @if($encuesta->estado === 'enviada' || $encuesta->estado === 'en_progreso' || $encuesta->estado === 'pausada')
            <a href="{{ route('encuestas.seguimiento.dashboard', $encuesta->id) }}" class="btn btn-info me-2">
                <i class="fas fa-chart-line"></i> Dashboard de Seguimiento
            </a>
        @endif

        <a href="{{ route('encuestas.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
        </a>
    </div>

    @if($preguntasSeleccion == 0 && $encuesta->preguntas->count() > 0)
        <div class="mt-3">
            <div class="alert alert-warning">
                <h5><i class="icon fas fa-exclamation-triangle"></i> No puedes agregar respuestas</h5>
                <p>Esta encuesta no tiene preguntas de selección (única o múltiple). Solo puedes agregar respuestas a preguntas de este tipo.</p>
                <p><strong>Tipos de preguntas que permiten respuestas:</strong></p>
                <ul>
                    <li><strong>Selección Única:</strong> El usuario elige una sola opción de una lista</li>
                    <li><strong>Selección Múltiple:</strong> El usuario puede elegir varias opciones de una lista</li>
                </ul>
                <a href="{{ route('encuestas.preguntas.create', $encuesta->id) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Agregar Preguntas de Selección
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Modal para agregar preguntas -->
@if(session('show_add_questions_modal'))
<div class="modal fade" id="addQuestionsModal" tabindex="-1" role="dialog" aria-labelledby="addQuestionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQuestionsModalLabel">
                    <i class="fas fa-plus"></i> Agregar Preguntas de Selección
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <h6><i class="icon fas fa-info"></i> ¿Por qué necesitas preguntas de selección?</h6>
                    <p>Para poder agregar respuestas a una encuesta, necesitas tener preguntas de tipo:</p>
                    <ul>
                        <li><strong>Selección Única:</strong> El usuario elige una sola opción de una lista</li>
                        <li><strong>Selección Múltiple:</strong> El usuario puede elegir varias opciones de una lista</li>
                    </ul>
                    <p>Las preguntas de texto, número o fecha no requieren respuestas predefinidas.</p>
                </div>

                <div class="text-center">
                    <a href="{{ route('encuestas.preguntas.create', $encuesta->id) }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus"></i> Ir a Agregar Preguntas
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Modal para eliminar pregunta individual -->
<div class="modal fade" id="deleteQuestionModal" tabindex="-1" role="dialog" aria-labelledby="deleteQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteQuestionModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar la pregunta:</p>
                <p><strong id="preguntaToDelete"></strong></p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡Atención!</strong> Esta acción también eliminará todas las respuestas asociadas a esta pregunta.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form id="deleteQuestionForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Pregunta
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para eliminar todas las preguntas -->
<div class="modal fade" id="deleteAllQuestionsModal" tabindex="-1" role="dialog" aria-labelledby="deleteAllQuestionsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAllQuestionsModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger"></i> Confirmar Eliminación Masiva
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar <strong>TODAS</strong> las preguntas de esta encuesta?</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¡ADVERTENCIA!</strong> Esta acción eliminará:
                    <ul class="mb-0 mt-2">
                        <li>Todas las preguntas de la encuesta</li>
                        <li>Todas las respuestas asociadas</li>
                        <li>Toda la lógica condicional configurada</li>
                    </ul>
                </div>
                <p>Esta acción <strong>NO SE PUEDE DESHACER</strong>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form action="{{ route('encuestas.preguntas.destroyAll', $encuesta->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Todas las Preguntas
                    </button>
                </form>
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

    // Show modal if needed
    @if(session('show_add_questions_modal'))
        $('#addQuestionsModal').modal('show');
    @endif
});

// Función para confirmar eliminación de pregunta individual
function confirmarEliminarPregunta(preguntaId, preguntaTexto) {
    // Actualizar el modal con la información de la pregunta
    $('#preguntaToDelete').text(preguntaTexto);

    // Actualizar el formulario con la URL correcta
    $('#deleteQuestionForm').attr('action', '{{ route("encuestas.preguntas.destroy", ["encuesta" => $encuesta->id, "pregunta" => ":preguntaId"]) }}'.replace(':preguntaId', preguntaId));

    // Mostrar el modal
    $('#deleteQuestionModal').modal('show');
}

// Confirmar eliminación de todas las preguntas
function confirmarEliminarTodasPreguntas() {
    if (confirm('¿Estás seguro de que quieres eliminar TODAS las preguntas? Esta acción no se puede deshacer.')) {
        // El formulario se enviará automáticamente
        return true;
    }
    return false;
}
</script>
@endsection
