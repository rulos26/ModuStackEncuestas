@extends('adminlte::page')

@section('content')
<h3>Configurar lógica de la encuesta</h3>

<form method="POST" action="{{ route('encuestas.logica.store', $encuestaId) }}">
    @csrf

    @foreach($preguntas as $pregunta)
        @foreach($pregunta->respuestas as $respuesta)
        <div class="card mb-3">
            <div class="card-header">
                Si se responde <strong>"{{ $respuesta->texto }}"</strong> en la pregunta <strong>"{{ $pregunta->texto }}"</strong>...
            </div>
            <div class="card-body row">
                <input type="hidden" name="logicas[{{ $loop->parent->index }}_{{ $loop->index }}][pregunta_id]" value="{{ $pregunta->id }}">
                <input type="hidden" name="logicas[{{ $loop->parent->index }}_{{ $loop->index }}][respuesta_id]" value="{{ $respuesta->id }}">

                <div class="col-md-6">
                    <label>Ir a la pregunta:</label>
                    <select name="logicas[{{ $loop->parent->index }}_{{ $loop->index }}][siguiente_pregunta_id]" class="form-control">
                        <option value="">-- Ninguna --</option>
                        @foreach($preguntas as $destino)
                            <option value="{{ $destino->id }}">{{ $destino->texto }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>¿Finalizar encuesta?</label>
                    <input type="checkbox" name="logicas[{{ $loop->parent->index }}_{{ $loop->index }}][finalizar]" value="1">
                </div>
            </div>
        </div>
        @endforeach
    @endforeach

    <button type="submit" class="btn btn-primary">Guardar lógica</button>
</form>

<a href="{{ route('encuestas.preview', $encuestaId) }}" class="btn btn-success mt-3">Siguiente: Vista previa</a>
@endsection
