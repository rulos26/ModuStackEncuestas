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
                            {{ $empresa->nombre_legal }}
                        </option>
                    @endforeach
                </select>
                @error('empresa_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="numero_encuestas">Número de encuestas a enviar</label>
                <input type="number" name="numero_encuestas" id="numero_encuestas"
                       class="form-control @error('numero_encuestas') is-invalid @enderror"
                       value="{{ old('numero_encuestas') }}" min="1" max="10000"
                       placeholder="Ej: 100">
                @error('numero_encuestas')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    Número total de encuestas que se enviarán
                </small>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio"
                               class="form-control @error('fecha_inicio') is-invalid @enderror"
                               value="{{ old('fecha_inicio') }}"
                               min="{{ date('Y-m-d') }}">
                        @error('fecha_inicio')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Cuándo estará disponible la encuesta
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_fin">Fecha de fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin"
                               class="form-control @error('fecha_fin') is-invalid @enderror"
                               value="{{ old('fecha_fin') }}"
                               min="{{ date('Y-m-d') }}">
                        @error('fecha_fin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Cuándo dejará de estar disponible
                        </small>
                    </div>
                </div>
            </div>



            {{-- Estado se maneja automáticamente en el backend --}}
            {{-- No se muestra en el formulario para evitar errores --}}

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="enviar_por_correo" id="enviar_por_correo"
                           class="form-check-input @error('enviar_por_correo') is-invalid @enderror"
                           value="1" {{ old('enviar_por_correo') ? 'checked' : '' }}>
                    <label for="enviar_por_correo" class="form-check-label">
                        Envío por correo electrónico
                    </label>
                    @error('enviar_por_correo')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="envio_masivo_activado" id="envio_masivo_activado"
                           class="form-check-input @error('envio_masivo_activado') is-invalid @enderror"
                           value="1" {{ old('envio_masivo_activado') ? 'checked' : '' }}>
                    <label for="envio_masivo_activado" class="form-check-label">
                        Activar envío masivo automático
                    </label>
                    @error('envio_masivo_activado')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="asunto_correo">Asunto del correo</label>
                <input type="text" name="asunto_correo" id="asunto_correo"
                       class="form-control @error('asunto_correo') is-invalid @enderror"
                       value="{{ old('asunto_correo') }}" maxlength="255"
                       placeholder="Ej: Invitación a participar en encuesta">
                @error('asunto_correo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="plantilla_correo">Plantilla del correo</label>
                <textarea name="plantilla_correo" id="plantilla_correo" rows="6"
                          class="form-control @error('plantilla_correo') is-invalid @enderror"
                          placeholder="Plantilla del correo electrónico...">{{ old('plantilla_correo') }}</textarea>
                @error('plantilla_correo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    Use {nombre}, {empresa}, {link} como variables dinámicas
                </small>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" name="habilitada" id="habilitada"
                           class="form-check-input @error('habilitada') is-invalid @enderror"
                           value="1" {{ old('habilitada') ? 'checked' : '' }}>
                    <label for="habilitada" class="form-check-label">
                        Encuesta pública
                    </label>
                    @error('habilitada')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
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

    // Validación de fechas
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');

    // Validar fecha de inicio
    fechaInicio.addEventListener('change', function() {
        const fechaSeleccionada = new Date(this.value);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);

        if (fechaSeleccionada < hoy) {
            alert('La fecha de inicio debe ser igual o posterior a hoy.');
            this.value = '';
        }

        // Actualizar fecha mínima de fin
        if (fechaFin) {
            fechaFin.min = this.value;
        }
    });

    // Validar fecha de fin
    if (fechaFin) {
        fechaFin.addEventListener('change', function() {
            const fechaInicioValor = fechaInicio.value;

            if (fechaInicioValor && this.value < fechaInicioValor) {
                alert('La fecha de fin debe ser igual o posterior a la fecha de inicio.');
                this.value = '';
            }
        });
    }
});
</script>
@endsection
