@extends('adminlte::page')

@section('title', 'Wizard de Respuestas - Resumen')

@section('content_header')
    <h1>
        <i class="fas fa-clipboard-check"></i> Wizard de Respuestas
        <small>Paso 3: Resumen - {{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('respuestas.index') }}">Respuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('respuestas.wizard.index') }}">Wizard de Respuestas</a></li>
            <li class="breadcrumb-item active">Resumen</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL WIZARD -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 1. Seleccionar</strong>
                </div>
                <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 2. Responder</strong>
                </div>
                <div class="progress-bar bg-primary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-list-check"></i> 3. Resumen</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 4. Confirmar</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-flag-checkered"></i> 5. Finalizar</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- ESTADÍSTICAS DE LA SESIÓN -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Encuesta Completada</span>
                    <span class="info-box-number">¡Éxito!</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $encuesta->titulo }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Respuestas Guardadas</span>
                    <span class="info-box-number">{{ $respuestasCount }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        En esta sesión
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-poll"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total de Preguntas</span>
                    <span class="info-box-number">{{ $encuesta->preguntas->count() }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        Preguntas totales
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Sesión Activa</span>
                    <span class="info-box-number">ID: {{ $encuesta->id }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        {{ $encuesta->empresa->nombre ?? 'Sin empresa' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    <!-- RESUMEN DE RESPUESTAS -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-list-check"></i> Resumen de tus Respuestas
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    <i class="fas fa-check"></i> Paso 3 de 5
                </span>
            </div>
        </div>
        <div class="card-body">
            @if($respuestasUsuario->isEmpty())
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h4 class="text-warning">No se encontraron respuestas</h4>
                    <p class="text-muted">Parece que no se guardaron las respuestas correctamente.</p>
                    <a href="{{ route('respuestas.wizard.responder') }}" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Volver a Responder
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">Pregunta</th>
                                <th width="15%">Tipo</th>
                                <th width="40%">Tu Respuesta</th>
                                <th width="10%">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($respuestasUsuario as $index => $respuestaUsuario)
                                <tr>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $index + 1 }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $respuestaUsuario->pregunta->texto }}</strong>
                                        @if($respuestaUsuario->pregunta->obligatoria)
                                            <span class="badge badge-danger ml-1">Obligatoria</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            @switch($respuestaUsuario->pregunta->tipo)
                                                @case('respuesta_corta')
                                                    📝 Corta
                                                    @break
                                                @case('parrafo')
                                                    📄 Párrafo
                                                    @break
                                                @case('seleccion_unica')
                                                    🔘 Única
                                                    @break
                                                @case('casillas_verificacion')
                                                    ☑️ Múltiple
                                                    @break
                                                @case('escala_lineal')
                                                    📊 Escala
                                                    @break
                                                @case('fecha')
                                                    📅 Fecha
                                                    @break
                                                @case('hora')
                                                    🕐 Hora
                                                    @break
                                                @default
                                                    {{ ucfirst($respuestaUsuario->pregunta->tipo) }}
                                            @endswitch
                                        </span>
                                    </td>
                                    <td>
                                        @if($respuestaUsuario->respuesta_id)
                                            <!-- Respuesta de selección -->
                                            <span class="text-success">
                                                <i class="fas fa-check-circle"></i>
                                                {{ $respuestaUsuario->respuesta->texto ?? 'Respuesta no encontrada' }}
                                            </span>
                                        @else
                                            <!-- Respuesta de texto -->
                                            <span class="text-primary">
                                                <i class="fas fa-comment"></i>
                                                {{ Str::limit($respuestaUsuario->respuesta_texto, 100) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Guardada
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- INFORMACIÓN ADICIONAL -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Información de la Sesión</h5>
                            <ul class="mb-0">
                                <li><strong>Encuesta:</strong> {{ $encuesta->titulo }}</li>
                                <li><strong>Empresa:</strong> {{ $encuesta->empresa->nombre ?? 'Sin empresa' }}</li>
                                <li><strong>Fecha:</strong> {{ now()->format('d/m/Y H:i:s') }}</li>
                                <li><strong>IP:</strong> {{ request()->ip() }}</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Estado de Completitud</h5>
                            <ul class="mb-0">
                                <li><strong>Respuestas guardadas:</strong> {{ $respuestasUsuario->count() }}</li>
                                <li><strong>Preguntas totales:</strong> {{ $encuesta->preguntas->count() }}</li>
                                <li><strong>Porcentaje completado:</strong> {{ round(($respuestasUsuario->count() / $encuesta->preguntas->count()) * 100) }}%</li>
                                <li><strong>Estado:</strong> <span class="badge badge-success">Completada</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- OPCIONES DE CONTINUACIÓN -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title">
                <i class="fas fa-arrow-right"></i> ¿Qué deseas hacer ahora?
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-check"></i> Finalizar y Salir
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                Completa el proceso y regresa al listado de encuestas.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Respuestas guardadas</li>
                                <li><i class="fas fa-check text-success"></i> Sesión finalizada</li>
                                <li><i class="fas fa-check text-success"></i> Volver al inicio</li>
                            </ul>
                            <form action="{{ route('respuestas.wizard.confirmar') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="action" value="finish">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-check"></i> Finalizar y Salir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-plus"></i> Iniciar Otra Encuesta
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                Comienza a responder otra encuesta disponible.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-primary"></i> Nueva encuesta</li>
                                <li><i class="fas fa-check text-primary"></i> Sesión limpia</li>
                                <li><i class="fas fa-check text-primary"></i> Continuar respondiendo</li>
                            </ul>
                            <form action="{{ route('respuestas.wizard.confirmar') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="action" value="continue">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-plus"></i> Iniciar Otra Encuesta
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('respuestas.wizard.responder') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Responder
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('respuestas.wizard.index') }}" class="btn btn-info">
                        <i class="fas fa-list"></i> Ver Todas las Encuestas
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    .progress-bar {
        font-size: 0.7rem;
    }

    .info-box {
        min-height: 80px;
    }

    .card {
        transition: all 0.3s ease;
    }

    .border-success {
        border-color: #28a745 !important;
    }

    .border-primary {
        border-color: #007bff !important;
    }

    .btn-block {
        font-weight: 600;
    }

    .table td {
        vertical-align: middle;
    }

    .badge {
        font-size: 0.8rem;
    }

    .alert {
        border-radius: 8px;
    }

    .progress {
        border-radius: 15px;
        overflow: hidden;
    }
</style>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Animación suave para las tarjetas
        $('.card').hover(
            function() {
                $(this).addClass('shadow-sm');
            },
            function() {
                $(this).removeClass('shadow-sm');
            }
        );

        // Confirmación antes de finalizar
        $('form[action*="confirmar"]').submit(function(e) {
            const action = $(this).find('input[name="action"]').val();

            if (action === 'finish') {
                if (!confirm('¿Estás seguro de que quieres finalizar el wizard? Se guardarán todas las respuestas.')) {
                    e.preventDefault();
                    return false;
                }
            }

            return true;
        });
    });
</script>
@endsection
