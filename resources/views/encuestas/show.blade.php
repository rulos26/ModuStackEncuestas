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
        @foreach($encuesta->preguntas as $pregunta)
            <div class="card mb-2">
                <div class="card-header">
                    {{ $loop->iteration }}. {{ $pregunta->texto }}
                    @if($pregunta->obligatoria)
                        <span class="badge bg-success">Obligatoria</span>
                    @endif
                    <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $pregunta->tipo)) }}</span>
                </div>
                <div class="card-body">
                    <strong>Tipo:</strong> {{ $pregunta->tipo }}<br>
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
</script>
@endsection
