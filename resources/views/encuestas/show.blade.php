@extends('adminlte::page')

@section('content')
<div class="container">
    <h2>Detalle de la Encuesta</h2>
    <p><strong>Título:</strong> {{ $encuesta->titulo }}</p>
    <p><strong>Slug:</strong> {{ $encuesta->slug }}</p>
    <p><strong>Estado:</strong> {{ $encuesta->habilitada ? 'Habilitada' : 'Deshabilitada' }}</p>

    <h4>Preguntas</h4>
    @foreach($encuesta->preguntas as $pregunta)
        <div class="card mb-2">
            <div class="card-header">
                {{ $loop->iteration }}. {{ $pregunta->texto }}
                @if($pregunta->obligatoria)
                    <span class="badge bg-success">Obligatoria</span>
                @endif
            </div>
            <div class="card-body">
                <strong>Tipo:</strong> {{ $pregunta->tipo }}<br>
                @if($pregunta->respuestas->count())
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

    <div class="mt-4">
        <a href="{{ route('encuestas.preguntas.create', $encuesta->id) }}" class="btn btn-primary me-2">Agregar Preguntas</a>
        <a href="{{ route('encuestas.respuestas.create', $encuesta->id) }}" class="btn btn-info me-2">Agregar Respuestas</a>
        <a href="{{ route('encuestas.logica.create', $encuesta->id) }}" class="btn btn-warning me-2">Configurar Lógica</a>
        <a href="{{ route('encuestas.preview', $encuesta->id) }}" class="btn btn-success me-2">Previsualizar Encuesta</a>
        <a href="{{ route('encuestas.index') }}" class="btn btn-secondary">Volver al listado</a>
    </div>
</div>
@endsection