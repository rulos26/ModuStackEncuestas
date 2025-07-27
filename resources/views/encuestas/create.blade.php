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

@if(session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-body">
        <form action="{{ route('encuestas.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="titulo">Título <span class="text-danger">*</span></label>
                <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror"
                       value="{{ old('titulo') }}" required maxlength="255" minlength="3"
                       placeholder="Ingrese el título de la encuesta">
                @error('titulo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="empresa_id">Empresa <span class="text-danger">*</span></label>
                <select name="empresa_id" id="empresa_id" class="form-control @error('empresa_id') is-invalid @enderror" required>
                    <option value="">Seleccione una empresa</option>
                    @foreach($empresas as $empresa)
                        <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                            {{ $empresa->nombre_legal ?? $empresa->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('empresa_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="numero_encuestas">Número de Encuestas</label>
                <input type="number" name="numero_encuestas" id="numero_encuestas"
                       class="form-control @error('numero_encuestas') is-invalid @enderror"
                       value="{{ old('numero_encuestas', 0) }}" min="0" max="10000"
                       placeholder="0 = sin límite">
                @error('numero_encuestas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Deje en 0 para no establecer límite</small>
            </div>

            <div class="form-group">
                <label for="tiempo_disponible">Tiempo Disponible</label>
                <input type="datetime-local" name="tiempo_disponible" id="tiempo_disponible"
                       class="form-control @error('tiempo_disponible') is-invalid @enderror"
                       value="{{ old('tiempo_disponible') }}">
                @error('tiempo_disponible')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Deje vacío para que no expire</small>
            </div>

            <div class="form-group">
                <label for="estado">Estado <span class="text-danger">*</span></label>
                <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                    <option value="borrador" {{ old('estado') == 'borrador' ? 'selected' : '' }}>Borrador</option>
                    <option value="enviada" {{ old('estado') == 'enviada' ? 'selected' : '' }}>Enviada</option>
                    <option value="publicada" {{ old('estado') == 'publicada' ? 'selected' : '' }}>Publicada</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group form-check">
                <input type="checkbox" name="enviar_por_correo" id="enviar_por_correo"
                       class="form-check-input @error('enviar_por_correo') is-invalid @enderror"
                       value="1" {{ old('enviar_por_correo') ? 'checked' : '' }}>
                <label for="enviar_por_correo" class="form-check-label">Enviar por correo</label>
                @error('enviar_por_correo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group form-check">
                <input type="checkbox" name="habilitada" id="habilitada"
                       class="form-check-input @error('habilitada') is-invalid @enderror"
                       value="1" {{ old('habilitada', true) ? 'checked' : '' }}>
                <label for="habilitada" class="form-check-label">Habilitada para el público</label>
                @error('habilitada')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Solo las encuestas habilitadas pueden ser respondidas públicamente</small>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar Encuesta
                </button>
                <a href="{{ route('encuestas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del tiempo disponible
    const tiempoDisponible = document.getElementById('tiempo_disponible');
    tiempoDisponible.addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const now = new Date();

        if (selectedDate <= now) {
            alert('El tiempo disponible debe ser posterior a la fecha y hora actual.');
            this.value = '';
        }
    });

    // Validación del número de encuestas
    const numeroEncuestas = document.getElementById('numero_encuestas');
    numeroEncuestas.addEventListener('input', function() {
        if (this.value < 0) {
            this.value = 0;
        }
        if (this.value > 10000) {
            this.value = 10000;
        }
    });
});
</script>
@endsection
