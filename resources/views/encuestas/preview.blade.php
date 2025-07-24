@extends('adminlte::page')

@section('content')
<h3>Vista previa de encuesta: {{ $encuesta->titulo }}</h3>

@foreach($encuesta->preguntas as $pregunta)
<div class="card mb-3">
    <div class="card-header">{{ $loop->iteration }}. {{ $pregunta->texto }}</div>
    <div class="card-body">
        @foreach($pregunta->respuestas as $respuesta)
            <div>
                <i class="far fa-circle"></i> {{ $respuesta->texto }}
            </div>
        @endforeach
    </div>
</div>
@endforeach

<a href="{{ route('encuestas.publica', $encuesta->slug) }}" class="btn btn-success">
    Ver como usuario (link público)
</a>
@endsection
