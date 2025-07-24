@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Agregar Pregunta a la Encuesta: {{ $encuesta->titulo ?? 'Sin título' }}</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('encuestas.preguntas.store', $encuesta->id) }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="texto" class="form-label">Texto de la pregunta</label>
            <input type="text" name="texto" id="texto" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="tipo" class="form-label">Tipo de pregunta</label>
            <select name="tipo" id="tipo" class="form-control" required>
                <option value="texto">Texto</option>
                <option value="seleccion_unica">Selección Única</option>
                <option value="seleccion_multiple">Selección Múltiple</option>
                <option value="numero">Número</option>
                <option value="fecha">Fecha</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="orden" class="form-label">Orden</label>
            <input type="number" name="orden" id="orden" class="form-control" value="1" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="obligatoria" id="obligatoria" class="form-check-input" checked>
            <label for="obligatoria" class="form-check-label">¿Obligatoria?</label>
        </div>

        <button type="submit" class="btn btn-primary">Agregar Pregunta</button>
    </form>
</div>
@endsection