@extends('adminlte::page')

@section('title', 'Corregir Incompatibilidades')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">
                <i class="fas fa-exclamation-triangle text-warning"></i> Corregir Incompatibilidades
                <small class="text-muted">{{ $encuesta->titulo }}</small>
            </h1>
        </div>
        <div class="d-flex align-items-center">
            <span class="badge badge-warning badge-lg">
                {{ count($incompatibilidades) }} incompatibilidades detectadas
            </span>
        </div>
    </div>
@stop

@section('content')
    <!-- ALERTAS -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-exclamation-triangle"></i> Atención</h5>
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> Error</h5>
            {{ session('error') }}
        </div>
    @endif

    <!-- EXPLICACIÓN -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h5><i class="fas fa-info-circle text-info"></i> ¿Qué son las incompatibilidades?</h5>
                    <p class="mb-0">
                        Se detectaron preguntas cuyo tipo no es compatible con el formato de las respuestas.
                        Por ejemplo, una pregunta de "escala" no puede tener respuestas de texto simple.
                        Por favor, ajusta el tipo de cada pregunta para que sea compatible con sus respuestas.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA DE INCOMPATIBILIDADES -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list text-primary"></i> Incompatibilidades Detectadas
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($incompatibilidades as $index => $incompatibilidad)
                        <div class="card mb-3 border-warning">
                            <div class="card-header bg-warning bg-opacity-10">
                                <h6 class="mb-0">
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    Incompatibilidad #{{ $index + 1 }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Pregunta -->
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-question-circle text-primary"></i> Pregunta</h6>
                                        <p class="mb-2"><strong>{{ $incompatibilidad['pregunta_texto'] }}</strong></p>
                                        <span class="badge badge-{{ $controller->getBadgeColorForType($incompatibilidad['pregunta_tipo']) }}">
                                            {{ $controller->getTypeName($incompatibilidad['pregunta_tipo']) }}
                                        </span>
                                    </div>

                                    <!-- Respuesta -->
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-reply text-success"></i> Respuesta</h6>
                                        <p class="mb-2"><strong>{{ $incompatibilidad['respuesta_contenido'] }}</strong></p>
                                        <span class="badge badge-secondary">
                                            {{ ucfirst($incompatibilidad['respuesta_tipo']) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Sugerencia -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-lightbulb text-info"></i> Sugerencia</h6>
                                            <p class="mb-2">
                                                Basado en el contenido de la respuesta, sugerimos cambiar el tipo a:
                                                <strong>{{ $controller->getTypeName($incompatibilidad['sugerencia_tipo']) }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Formulario de corrección -->
                                <form action="{{ route('carga-masiva.guardar-correccion-incompatibilidad') }}" method="POST" class="mt-3">
                                    @csrf
                                    <input type="hidden" name="cache_key" value="{{ $cacheKey }}">
                                    <input type="hidden" name="numero_pregunta" value="{{ $incompatibilidad['numero_pregunta'] }}">

                                    <div class="row">
                                        <div class="col-md-8">
                                            <label for="nuevo_tipo_{{ $index }}">Nuevo tipo de pregunta:</label>
                                            <select name="nuevo_tipo" id="nuevo_tipo_{{ $index }}" class="form-control" required>
                                                <option value="">Selecciona un tipo...</option>
                                                @foreach($controller->obtenerTiposDisponibles() as $valor => $nombre)
                                                    <option value="{{ $valor }}"
                                                            {{ $valor === $incompatibilidad['sugerencia_tipo'] ? 'selected' : '' }}>
                                                        {{ $nombre }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <button type="submit" class="btn btn-success btn-block">
                                                <i class="fas fa-check"></i> Corregir Tipo
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach

                    @if(empty($incompatibilidades))
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-success">¡No hay incompatibilidades!</h4>
                            <p class="text-muted">Todas las preguntas y respuestas son compatibles.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- BOTONES DE ACCIÓN -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="{{ route('carga-masiva.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Inicio
                </a>
                <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info">
                    <i class="fas fa-eye"></i> Ver Encuesta
                </a>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.border-warning {
    border-left: 4px solid #ffc107 !important;
}

.bg-opacity-10 {
    background-color: rgba(255, 193, 7, 0.1) !important;
}

.badge-lg {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.alert {
    border-radius: 0.5rem;
}

.btn-group .btn {
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Tooltips
    $('[title]').tooltip();

    // Confirmación antes de corregir
    $('form').on('submit', function(e) {
        const nuevoTipo = $(this).find('select[name="nuevo_tipo"]').val();

        if (!nuevoTipo) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Tipo requerido',
                text: 'Por favor selecciona un tipo de pregunta.'
            });
            return false;
        }

        // Mostrar loading
        Swal.fire({
            title: 'Corrigiendo tipo...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });
});
</script>
@stop
