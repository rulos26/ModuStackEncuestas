@extends('adminlte::page')

@section('title', 'Agregar Respuestas')

@section('content_header')
    <h1>
        <i class="fas fa-list-check"></i> Agregar Respuestas
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- DASHBOARD DE ESTADÍSTICAS -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalPreguntas }}</h3>
                    <p>Total de Preguntas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-question-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $preguntasConRespuestas->count() }}</h3>
                    <p>Preguntas con Respuestas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $preguntasSinRespuestas->count() }}</h3>
                    <p>Preguntas sin Respuestas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box {{ $puedeConfigurarLogica ? 'bg-primary' : 'bg-secondary' }}">
                <div class="inner">
                    <h3>{{ $puedeConfigurarLogica ? 'Sí' : 'No' }}</h3>
                    <p>Puede Configurar Lógica</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Agregar Respuestas</li>
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
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    @if($preguntasSinRespuestas->isNotEmpty())
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle"></i> Preguntas que necesitan respuestas
                </h3>
            </div>
            <div class="card-body">
                @foreach($preguntasSinRespuestas as $pregunta)
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>{{ $loop->iteration }}. {{ $pregunta->texto }}</strong>
                            <span class="badge badge-{{ $pregunta->obligatoria ? 'danger' : 'info' }} ml-2">
                                {{ $pregunta->obligatoria ? 'Obligatoria' : 'Opcional' }}
                            </span>
                            <span class="badge badge-secondary ml-2">{{ $pregunta->getNombreTipo() }}</span>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('encuestas.respuestas.store', $encuesta->id) }}" method="POST">
                                @csrf
                                <div class="respuestas-container" data-pregunta-id="{{ $pregunta->id }}">
                                    <div class="respuesta-item mb-2">
                                        <div class="input-group">
                                            <input type="text" name="respuestas[{{ $pregunta->id }}][0][texto]"
                                                   class="form-control" placeholder="Escriba la respuesta" required>
                                            <input type="hidden" name="respuestas[{{ $pregunta->id }}][0][orden]" value="1">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success agregar-respuesta"
                                                        data-pregunta-id="{{ $pregunta->id }}">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary mt-2">
                                    <i class="fas fa-save"></i> Guardar Respuestas
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($preguntasConRespuestas->isNotEmpty())
        <div class="card card-info mt-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-check-circle"></i> Preguntas con respuestas configuradas
                </h3>
            </div>
            <div class="card-body">
                @foreach($preguntasConRespuestas as $pregunta)
                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>{{ $loop->iteration }}. {{ $pregunta->texto }}</strong>
                            <span class="badge badge-success ml-2">{{ $pregunta->respuestas->count() }} respuestas</span>
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                @foreach($pregunta->respuestas as $respuesta)
                                    <li class="list-group-item">
                                        <i class="fas fa-circle text-primary"></i> {{ $respuesta->texto }}
                                    </li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn btn-warning btn-sm mt-2 editar-respuestas"
                                    data-pregunta-id="{{ $pregunta->id }}">
                                <i class="fas fa-edit"></i> Editar Respuestas
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- BOTONES DE NAVEGACIÓN -->
    <div class="mt-4">
        @if($puedeConfigurarLogica)
            <a href="{{ route('encuestas.logica.create', $encuesta->id) }}" class="btn btn-primary">
                <i class="fas fa-cogs"></i> Configurar Lógica
            </a>
        @else
            <button type="button" class="btn btn-secondary" disabled title="Primero completa todas las respuestas">
                <i class="fas fa-cogs"></i> Configurar Lógica
            </button>
        @endif

        <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info">
            <i class="fas fa-arrow-left"></i> Volver a la Encuesta
        </a>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Agregar nueva respuesta
    $('.agregar-respuesta').click(function() {
        var preguntaId = $(this).data('pregunta-id');
        var container = $('.respuestas-container[data-pregunta-id="' + preguntaId + '"]');
        var itemCount = container.find('.respuesta-item').length;

        var newItem = `
            <div class="respuesta-item mb-2">
                <div class="input-group">
                    <input type="text" name="respuestas[${preguntaId}][${itemCount}][texto]"
                           class="form-control" placeholder="Escriba la respuesta" required>
                    <input type="hidden" name="respuestas[${preguntaId}][${itemCount}][orden]" value="${itemCount + 1}">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger eliminar-respuesta">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.append(newItem);
    });

    // Eliminar respuesta
    $(document).on('click', '.eliminar-respuesta', function() {
        $(this).closest('.respuesta-item').remove();
    });
});
</script>
@endsection
