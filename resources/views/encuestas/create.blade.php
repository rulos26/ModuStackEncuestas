@extends('adminlte::page')

@section('title', 'Nueva Encuesta')

@section('content_header')
    <h1>Nueva Encuesta</h1>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="card">
    <div class="card-body">
        <form action="{{ route('encuestas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" class="form-control" value="{{ old('titulo') }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="empresa_id">Empresa</label>
                <select name="empresa_id" id="empresa_id" class="form-control" required>
                    <option value="">Seleccione una empresa</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>{{ $empresa->nombre_legal ?? $empresa->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="numero_encuestas">Número de Encuestas</label>
                <input type="number" name="numero_encuestas" id="numero_encuestas" class="form-control" value="{{ old('numero_encuestas', 0) }}" min="0">
            </div>
            <div class="form-group">
                <label for="tiempo_disponible">Tiempo Disponible</label>
                <input type="datetime-local" name="tiempo_disponible" id="tiempo_disponible" class="form-control" value="{{ old('tiempo_disponible') }}">
            </div>
            <div class="form-group form-check">
                <input type="checkbox" name="enviar_por_correo" id="enviar_por_correo" class="form-check-input" value="1" {{ old('enviar_por_correo') ? 'checked' : '' }}>
                <label for="enviar_por_correo" class="form-check-label">Enviar por correo</label>
            </div>
            <div class="form-group">
                <label for="estado">Estado</label>
                <select name="estado" id="estado" class="form-control" required>
                    <option value="borrador" {{ old('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="enviada" {{ old('estado') == 'enviada' ? 'selected' : '' }}>Enviada</option>
                    <option value="publicada" {{ old('estado') == 'publicada' ? 'selected' : '' }}>Publicada</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
            <a href="{{ route('encuestas.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
