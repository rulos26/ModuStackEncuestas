@extends('adminlte::page')

@section('title', 'Resumen de Lógica Configurada')

@section('content_header')
    <div class="row">
        <div class="col-md-6">
            <h1>
                <i class="fas fa-clipboard-check"></i>
                Resumen de Lógica Configurada
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
        <div class="col-md-8">
            <!-- Información de la Encuesta -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i>
                        Información de la Encuesta
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Encuesta:</strong> {{ $encuesta->titulo }}</p>
                            <p><strong>Empresa:</strong> {{ $encuesta->empresa->nombre ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Preguntas:</strong> {{ $encuesta->preguntas->count() }}</p>
                            <p><strong>Lógicas Configuradas:</strong> {{ $logicas->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen de Lógica -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-project-diagram"></i>
                        Lógica de Salto Configurada
                    </h5>
                </div>
                <div class="card-body">
                    @if($resumenLogica->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="30%">
                                            <i class="fas fa-question-circle"></i>
                                            Pregunta Origen
                                        </th>
                                        <th width="25%">
                                            <i class="fas fa-check-circle"></i>
                                            Respuesta
                                        </th>
                                        <th width="35%">
                                            <i class="fas fa-arrow-right"></i>
                                            Acción
                                        </th>
                                        <th width="10%">
                                            <i class="fas fa-cogs"></i>
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resumenLogica as $logica)
                                        <tr>
                                            <td>
                                                <strong>{{ $logica['pregunta_origen'] }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $logica['respuesta'] }}</span>
                                            </td>
                                            <td>
                                                @if($logica['finalizar'])
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-stop-circle"></i> Finalizar Encuesta
                                                    </span>
                                                @elseif($logica['siguiente_pregunta'])
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-arrow-right"></i> Ir a: {{ $logica['siguiente_pregunta'] }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-arrow-down"></i> Continuar Secuencialmente
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                        onclick="mostrarDetallesLogica('{{ $logica['pregunta_origen'] }}', '{{ $logica['respuesta'] }}', '{{ $logica['accion'] }}')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                            <h5>No hay lógica configurada</h5>
                            <p class="text-muted">
                                No se ha configurado ninguna lógica de salto para esta encuesta.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Panel Lateral -->
        <div class="col-md-4">
            <!-- Estadísticas -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i>
                        Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon">
                                    <i class="fas fa-arrow-right"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Saltos</span>
                                    <span class="info-box-number">
                                        {{ $logicas->where('siguiente_pregunta_id', '!=', null)->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon">
                                    <i class="fas fa-stop-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Finalizar</span>
                                    <span class="info-box-number">
                                        {{ $logicas->where('finalizar', true)->count() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools"></i>
                        Acciones
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('logica.wizard.confirmar') }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check"></i>
                            Confirmar y Finalizar
                        </button>
                    </form>

                    <a href="{{ route('logica.wizard.configurar') }}" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-edit"></i>
                        Editar Lógica
                    </a>

                    <a href="{{ route('logica.wizard.cancel') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                </div>
            </div>

            <!-- Diagrama de Flujo -->
            @if($resumenLogica->count() > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-sitemap"></i>
                            Vista de Flujo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="diagramaFlujo">
                            <p class="text-muted text-center">
                                <i class="fas fa-info-circle"></i>
                                Diagrama de flujo de la lógica configurada.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Modal para detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle"></i>
                        Detalles de la Lógica
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detallesContenido">
                        <!-- Contenido dinámico -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
.info-box {
    min-height: 80px;
    margin-bottom: 0;
}

.info-box-icon {
    border-radius: 0;
    display: block;
    float: left;
    height: 80px;
    width: 80px;
    text-align: center;
    font-size: 40px;
    line-height: 80px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 80px;
}

.info-box-text {
    display: block;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.info-box-number {
    display: block;
    font-weight: bold;
    font-size: 18px;
}

.table th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.badge {
    font-size: 0.8rem;
}
</style>
@stop

@section('js')
<script>
function mostrarDetallesLogica(pregunta, respuesta, accion) {
    var contenido = '<div class="row">';
    contenido += '<div class="col-12">';
    contenido += '<p><strong>Pregunta:</strong> ' + pregunta + '</p>';
    contenido += '<p><strong>Respuesta:</strong> ' + respuesta + '</p>';
    contenido += '<p><strong>Acción:</strong> ' + accion + '</p>';
    contenido += '</div>';
    contenido += '</div>';

    document.getElementById('detallesContenido').innerHTML = contenido;
    $('#modalDetalles').modal('show');
}

// Auto-hide alerts
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 5000);

// Generar diagrama de flujo simple
document.addEventListener('DOMContentLoaded', function() {
    var diagramaFlujo = document.getElementById('diagramaFlujo');
    if (diagramaFlujo) {
        var html = '<div class="text-center">';
        html += '<i class="fas fa-sitemap fa-2x text-primary mb-2"></i>';
        html += '<p class="text-muted">Flujo de lógica configurado</p>';
        html += '<small class="text-muted">';
        html += '{{ $logicas->count() }} reglas de salto configuradas';
        html += '</small>';
        html += '</div>';
        diagramaFlujo.innerHTML = html;
    }
});
</script>
@stop
