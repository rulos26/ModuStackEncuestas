@extends('adminlte::page')

@section('title', 'Wizard de Preguntas - Confirmar')

@section('content_header')
    <h1>
        <i class="fas fa-magic"></i> Wizard de Preguntas
        <small>Paso 3: Confirmar - {{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('preguntas.wizard.index') }}">Wizard de Preguntas</a></li>
            <li class="breadcrumb-item active">Confirmar</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL WIZARD -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 1. Seleccionar Encuesta</strong>
                </div>
                <div class="progress-bar bg-success" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 2. Crear Pregunta</strong>
                </div>
                <div class="progress-bar bg-primary" role="progressbar" style="width: 34%;" aria-valuenow="34" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 3. Confirmar</strong>
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
                    <span class="info-box-text">Pregunta Creada</span>
                    <span class="info-box-number">¡Éxito!</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        ID: {{ $pregunta->id }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Preguntas en Sesión</span>
                    <span class="info-box-number">{{ $preguntasCount }}</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        Creadas en esta sesión
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-poll"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total en Encuesta</span>
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
                        {{ $encuesta->titulo }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- DETALLES DE LA PREGUNTA CREADA -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title">
                <i class="fas fa-check-circle"></i> Pregunta Creada Exitosamente
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    <i class="fas fa-plus"></i> Paso 3 de 3
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h5><i class="fas fa-question-circle text-primary"></i> Detalles de la Pregunta:</h5>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td><strong>Texto:</strong></td>
                                <td>{{ $pregunta->texto }}</td>
                            </tr>
                            @if($pregunta->descripcion)
                                <tr>
                                    <td><strong>Descripción:</strong></td>
                                    <td>{{ $pregunta->descripcion }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Tipo:</strong></td>
                                <td>
                                    <span class="badge badge-info">
                                        @switch($pregunta->tipo)
                                            @case('respuesta_corta')
                                                📝 Respuesta corta
                                                @break
                                            @case('parrafo')
                                                📄 Párrafo
                                                @break
                                            @case('seleccion_unica')
                                                🔘 Selección única
                                                @break
                                            @case('casillas_verificacion')
                                                ☑️ Casillas de verificación
                                                @break
                                            @case('lista_desplegable')
                                                📋 Lista desplegable
                                                @break
                                            @case('escala_lineal')
                                                📊 Escala lineal
                                                @break
                                            @case('fecha')
                                                📅 Fecha
                                                @break
                                            @case('hora')
                                                🕐 Hora
                                                @break
                                            @case('carga_archivos')
                                                📎 Carga de archivos
                                                @break
                                            @case('ubicacion_mapa')
                                                🗺️ Ubicación en mapa
                                                @break
                                            @default
                                                {{ ucfirst($pregunta->tipo) }}
                                        @endswitch
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Orden:</strong></td>
                                <td>{{ $pregunta->orden }}</td>
                            </tr>
                            <tr>
                                <td><strong>Obligatoria:</strong></td>
                                <td>
                                    @if($pregunta->obligatoria)
                                        <span class="badge badge-danger">Sí</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> ¡Pregunta Guardada!</h5>
                        <p class="mb-0">
                            La pregunta se ha creado correctamente en la encuesta
                            <strong>"{{ $encuesta->titulo }}"</strong>.
                        </p>
                    </div>

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Próximos Pasos</h5>
                        <p class="mb-0">
                            Puedes continuar agregando más preguntas o finalizar el wizard.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OPCIONES DE CONTINUACIÓN -->
    <div class="card">
        <div class="card-header bg-primary text-white">
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
                                <i class="fas fa-plus"></i> Continuar Agregando Preguntas
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                Agrega más preguntas a la encuesta usando el wizard simplificado.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Formulario optimizado</li>
                                <li><i class="fas fa-check text-success"></i> Contador en tiempo real</li>
                                <li><i class="fas fa-check text-success"></i> Navegación fluida</li>
                            </ul>
                            <form action="{{ route('preguntas.wizard.confirm') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="action" value="continue">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-plus"></i> Agregar Otra Pregunta
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-check"></i> Finalizar Wizard
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                Termina el wizard y ve a la vista de la encuesta.
                            </p>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-primary"></i> Ver todas las preguntas</li>
                                <li><i class="fas fa-check text-primary"></i> Continuar con respuestas</li>
                                <li><i class="fas fa-check text-primary"></i> Configurar lógica</li>
                            </ul>
                            <form action="{{ route('preguntas.wizard.confirm') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="action" value="finish">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-check"></i> Finalizar Wizard
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
                    <a href="{{ route('preguntas.wizard.create') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Formulario
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> Ver Encuesta
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    .progress-bar {
        font-size: 0.8rem;
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
        $('form[action*="confirm"]').submit(function(e) {
            const action = $(this).find('input[name="action"]').val();

            if (action === 'finish') {
                if (!confirm('¿Estás seguro de que quieres finalizar el wizard? Se guardarán todas las preguntas creadas.')) {
                    e.preventDefault();
                    return false;
                }
            }

            return true;
        });
    });
</script>
@endsection
