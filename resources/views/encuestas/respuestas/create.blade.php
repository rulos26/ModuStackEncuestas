@extends('adminlte::page')

@section('content')
<h3>Agregar respuestas a las preguntas</h3>
<form method="POST" action="{{ route('encuestas.respuestas.store', $encuestaId) }}">
    @csrf

    @foreach($preguntas as $pregunta)
        @if(in_array($pregunta->tipo, ['seleccion_unica', 'seleccion_multiple']))
            <div class="card mb-3">
                <div class="card-header"><strong>{{ $pregunta->texto }}</strong></div>
                <div class="card-body">
                    <div class="respuestas-group" data-pregunta-id="{{ $pregunta->id }}">
                        <div class="form-group">
                            <input type="hidden" name="respuestas[{{ $loop->index }}][pregunta_id]" value="{{ $pregunta->id }}">
                            <input type="text" name="respuestas[{{ $loop->index }}][texto]" class="form-control mb-2" placeholder="Texto de la respuesta" required>
                            <input type="number" name="respuestas[{{ $loop->index }}][orden]" class="form-control mb-2" placeholder="Orden">
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <button type="submit" class="btn btn-primary">Guardar respuestas</button>
</form>

<a href="{{ route('encuestas.logica.create', $encuestaId) }}" class="btn btn-success mt-3">Siguiente: Configurar lógica</a>
@endsection
