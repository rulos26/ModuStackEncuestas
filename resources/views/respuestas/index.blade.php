@extends('adminlte::page')

@section('title', 'Análisis de Respuestas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i>
                        Análisis de Respuestas con IA
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">
                            <i class="fas fa-robot"></i>
                            IA Inteligente
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error</h5>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-brain"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Análisis Inteligente</span>
                                    <span class="info-box-number">IA Gratuita</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Análisis automático de respuestas con sugerencias de visualización
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-chart-pie"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Gráficos Sugeridos</span>
                                    <span class="info-box-number">{{ $encuestas->count() }}</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 70%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Encuestas disponibles para análisis
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        <i class="fas fa-search"></i>
                                        Seleccionar Encuesta para Análisis
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('respuestas.generar-analisis') }}" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label for="encuesta_id">
                                                <i class="fas fa-list"></i>
                                                Encuesta a Analizar
                                            </label>
                                            <select name="encuesta_id" id="encuesta_id" class="form-control @error('encuesta_id') is-invalid @enderror" required>
                                                <option value="">-- Seleccionar Encuesta --</option>
                                                @foreach($encuestas as $encuesta)
                                                    <option value="{{ $encuesta->id }}"
                                                            data-empresa="{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}"
                                                            data-preguntas="{{ $encuesta->preguntas->count() }}"
                                                            data-respuestas="{{ $encuesta->encuestas_respondidas }}">
                                                        {{ $encuesta->titulo }}
                                                        @if($encuesta->empresa)
                                                            - {{ $encuesta->empresa->nombre }}
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('encuesta_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Selecciona una encuesta para generar análisis automático con IA
                                            </small>
                                        </div>

                                        <div id="encuesta-info" class="alert alert-info" style="display: none;">
                                            <h6><i class="fas fa-info-circle"></i> Información de la Encuesta</h6>
                                            <div id="info-content"></div>
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-robot"></i>
                                                Generar Análisis con IA
                                            </button>
                                            <a href="{{ route('home') }}" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left"></i>
                                                Volver
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($encuestas->isNotEmpty())
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-table"></i>
                                            Encuestas Disponibles
                                        </h3>
                                    </div>
                                    <div class="card-body table-responsive p-0">
                                        <table class="table table-hover text-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Título</th>
                                                    <th>Empresa</th>
                                                    <th>Preguntas</th>
                                                    <th>Respuestas</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($encuestas as $encuesta)
                                                    <tr>
                                                        <td>{{ $encuesta->id }}</td>
                                                        <td>{{ $encuesta->titulo }}</td>
                                                        <td>{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}</td>
                                                        <td>
                                                            <span class="badge badge-info">
                                                                {{ $encuesta->preguntas->count() }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-success">
                                                                {{ $encuesta->encuestas_respondidas }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-{{ $encuesta->estado === 'publicada' ? 'success' : 'warning' }}">
                                                                {{ ucfirst($encuesta->estado) }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button type="button"
                                                                    class="btn btn-sm btn-primary seleccionar-encuesta"
                                                                    data-id="{{ $encuesta->id }}"
                                                                    data-titulo="{{ $encuesta->titulo }}">
                                                                <i class="fas fa-chart-bar"></i>
                                                                Analizar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Sin Encuestas</h5>
                                    No hay encuestas publicadas disponibles para análisis.
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-box {
    margin-bottom: 20px;
}
.card-tools .badge {
    font-size: 0.8rem;
}
</style>

<script>
$(document).ready(function() {
    // Mostrar información de la encuesta seleccionada
    $('#encuesta_id').change(function() {
        var selectedOption = $(this).find('option:selected');
        var empresa = selectedOption.data('empresa');
        var preguntas = selectedOption.data('preguntas');
        var respuestas = selectedOption.data('respuestas');

        if ($(this).val()) {
            var infoContent = `
                <strong>Empresa:</strong> ${empresa}<br>
                <strong>Preguntas:</strong> ${preguntas}<br>
                <strong>Respuestas:</strong> ${respuestas}
            `;
            $('#info-content').html(infoContent);
            $('#encuesta-info').show();
        } else {
            $('#encuesta-info').hide();
        }
    });

    // Seleccionar encuesta desde la tabla
    $('.seleccionar-encuesta').click(function() {
        var id = $(this).data('id');
        var titulo = $(this).data('titulo');
        $('#encuesta_id').val(id).trigger('change');

        // Scroll suave al formulario
        $('html, body').animate({
            scrollTop: $('#encuesta_id').offset().top - 100
        }, 500);
    });
});
</script>
@endsection
