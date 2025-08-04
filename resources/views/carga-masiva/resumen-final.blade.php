@extends('adminlte::page')

@section('title', 'Resumen Final - Carga Masiva')

@section('content_header')
    <h1>
        <i class="fas fa-chart-bar"></i> Resumen Final
        <small class="text-muted">{{ $encuesta->titulo }}</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Resumen General -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tasks"></i> Resumen de Carga Masiva
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Respuestas Guardadas</span>
                                    <span class="info-box-number">{{ $resultado['guardadas'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Errores</span>
                                    <span class="info-box-number">{{ count($resultado['errores']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sin Pregunta</span>
                                    <span class="info-box-number">{{ count($resultado['sin_pregunta']) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tasa de Éxito</span>
                                    <span class="info-box-number">
                                        @php
                                            $total = $resultado['guardadas'] + count($resultado['errores']) + count($resultado['sin_pregunta']);
                                            $tasa = $total > 0 ? round(($resultado['guardadas'] / $total) * 100, 1) : 0;
                                        @endphp
                                        {{ $tasa }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles de Errores -->
            @if(count($resultado['errores']) > 0)
                <div class="card">
                    <div class="card-header bg-danger">
                        <h3 class="card-title text-white">
                            <i class="fas fa-exclamation-triangle"></i> Errores Encontrados
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-info-circle"></i> Se encontraron los siguientes errores:</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="90%">Descripción del Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resultado['errores'] as $index => $error)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-danger">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <i class="fas fa-exclamation-circle text-danger"></i>
                                                {{ $error }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Respuestas Sin Pregunta -->
            @if(count($resultado['sin_pregunta']) > 0)
                <div class="card">
                    <div class="card-header bg-warning">
                        <h3 class="card-title text-white">
                            <i class="fas fa-question-circle"></i> Respuestas Sin Pregunta Correspondiente
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <h5><i class="fas fa-info-circle"></i> Las siguientes respuestas no pudieron ser asociadas:</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="10%">#</th>
                                        <th width="90%">Respuesta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resultado['sin_pregunta'] as $index => $respuesta)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-warning">{{ $index + 1 }}</span>
                                            </td>
                                            <td>
                                                <i class="fas fa-question-circle text-warning"></i>
                                                {{ $respuesta }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Mensaje de Éxito -->
            @if($resultado['guardadas'] > 0)
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title text-white">
                            <i class="fas fa-check-circle"></i> ¡Carga Completada!
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-thumbs-up"></i> ¡Excelente trabajo!</h5>
                            <p class="mb-0">
                                Se han procesado y guardado <strong>{{ $resultado['guardadas'] }} respuestas</strong>
                                exitosamente en la encuesta <strong>"{{ $encuesta->titulo }}"</strong>.
                            </p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-chart-line"></i> Próximos Pasos</h6>
                                <ul>
                                    <li>Revisar las preguntas y respuestas cargadas</li>
                                    <li>Configurar el envío de la encuesta</li>
                                    <li>Probar la encuesta antes del envío masivo</li>
                                    <li>Monitorear las respuestas recibidas</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-lightbulb"></i> Recomendaciones</h6>
                                <ul>
                                    <li>Verifica que los tipos de preguntas sean correctos</li>
                                    <li>Revisa las respuestas para asegurar compatibilidad</li>
                                    <li>Considera hacer una prueba piloto</li>
                                    <li>Configura recordatorios si es necesario</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Botones de Acción -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Acciones Disponibles
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-primary btn-block">
                                <i class="fas fa-eye"></i> Ver Encuesta
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('encuestas.edit', $encuesta->id) }}" class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Editar Encuesta
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('encuestas.seguimiento.dashboard', $encuesta->id) }}" class="btn btn-info btn-block">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="{{ route('carga-masiva.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-upload"></i> Nueva Carga
                            </a>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('encuestas.index') }}" class="btn btn-outline-primary btn-block">
                                <i class="fas fa-list"></i> Lista de Encuestas
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('encuestas.create') }}" class="btn btn-outline-success btn-block">
                                <i class="fas fa-plus"></i> Crear Nueva Encuesta
                            </a>
                        </div>
                    </div>
                </div>
            </div>
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

.info-box {
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.table th {
    background-color: #343a40;
    color: white;
    border-color: #454d55;
}

.badge {
    font-size: 0.875rem;
}

.btn-block {
    border-radius: 0.375rem;
}

.alert {
    border-radius: 0.5rem;
}
</style>
@stop

@section('js')
<script>
// Animación de entrada para las tarjetas
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.5s ease';

            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100);
        }, index * 200);
    });
});

// Mostrar notificación de éxito si hay respuestas guardadas
@if($resultado['guardadas'] > 0)
    Swal.fire({
        icon: 'success',
        title: '¡Carga Completada!',
        text: 'Se han guardado {{ $resultado["guardadas"] }} respuestas exitosamente.',
        confirmButtonText: 'Continuar'
    });
@endif

// Mostrar advertencia si hay errores
@if(count($resultado['errores']) > 0)
    setTimeout(() => {
        Swal.fire({
            icon: 'warning',
            title: 'Errores Detectados',
            text: 'Se encontraron {{ count($resultado["errores"]) }} errores durante la carga. Revisa los detalles.',
            confirmButtonText: 'Entendido'
        });
    }, 1000);
@endif
</script>
@stop
