@extends('layouts.encuesta-publica') <!-- Usa una vista limpia sin AdminLTE si deseas -->

@section('content')
<h3>{{ $encuesta->titulo }}</h3>

<form method="POST" action="{{ route('encuestas.responder', $encuesta->id) }}">
    @csrf

    @foreach($encuesta->preguntas as $pregunta)
        <div class="mb-4">
            <label><strong>{{ $loop->iteration }}. {{ $pregunta->texto }}</strong></label><br>
            @foreach($pregunta->respuestas as $respuesta)
                <div>
                    <input type="radio" name="respuestas[{{ $pregunta->id }}]" value="{{ $respuesta->id }}"> {{ $respuesta->texto }}
                </div>
            @endforeach
        </div>
    @endforeach

    <button class="btn btn-primary">Enviar respuestas</button>
</form>
@endsection
