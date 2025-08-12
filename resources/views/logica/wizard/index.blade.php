@extends('adminlte::page')

@section('title', 'Wizard de Lógica de Preguntas')

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>
                <i class="fas fa-project-diagram"></i>
                Wizard de Lógica de Preguntas
            </h1>
        </div>
        <div class="col-md-6 text-right">
            <a href="{{ route('encuestas.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Encuestas
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Seleccionar Encuesta
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Instrucciones:</strong> Selecciona la encuesta para la cual deseas configurar la lógica de salto entre preguntas.
                    </div>

                    <form action="{{ route('logica.wizard.configurar') }}" method="GET" id="formEncuesta">
                        <div class="form-group">
                            <label for="encuesta_id">
                                <i class="fas fa-poll"></i>
                                Encuesta:
                            </label>
                            <select name="encuesta_id" id="encuesta_id" class="form-control" required>
                                <option value="">-- Selecciona una encuesta --</option>
                                @foreach($encuestas as $encuesta)
                                    <option value="{{ $encuesta->id }}">
                                        {{ $encuesta->titulo }}
                                        @if($encuesta->empresa)
                                            - {{ $encuesta->empresa->nombre }}
                                        @endif
                                        ({{ $encuesta->preguntas->count() }} preguntas)
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Solo se muestran encuestas que tienen preguntas con respuestas configuradas.
                            </small>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i>
                                Siguiente
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if($encuestas->isEmpty())
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h4>No hay encuestas disponibles</h4>
                        <p class="text-muted">
                            No se encontraron encuestas con preguntas que permitan configurar lógica de salto.
                        </p>
                        <a href="{{ route('encuestas.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Crear Nueva Encuesta
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@stop

@section('css')
<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Validar formulario antes de enviar
    $('#formEncuesta').on('submit', function(e) {
        var encuestaId = $('#encuesta_id').val();
        if (!encuestaId) {
            e.preventDefault();
            alert('Por favor selecciona una encuesta.');
            return false;
        }
    });
});
</script>
@stop
