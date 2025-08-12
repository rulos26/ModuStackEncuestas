@extends('adminlte::page')

@section('title', 'Wizard de Respuestas - Seleccionar Encuesta')

@section('content_header')
    <h1>
        <i class="fas fa-clipboard-check"></i> Wizard de Respuestas
        <small>Paso 1: Seleccionar Encuesta</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('respuestas.index') }}">Respuestas</a></li>
            <li class="breadcrumb-item active">Wizard de Respuestas</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL WIZARD -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-list"></i> 1. Seleccionar</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-question-circle"></i> 2. Responder</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
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

    <!-- CONTADOR DE RESPUESTAS EN SESIÓN -->
    @if(Session::get('wizard_respuestas_count', 0) > 0 && Session::get('wizard_encuesta_id'))
        <div class="alert alert-success">
            <h5><i class="fas fa-play-circle"></i> Sesión Activa</h5>
            <p class="mb-0">
                <strong>{{ Session::get('wizard_respuestas_count', 0) }}</strong> respuesta(s) guardada(s) en esta sesión.
                <span class="badge badge-primary ml-2">
                    <i class="fas fa-poll"></i> Encuesta ID: {{ Session::get('wizard_encuesta_id') }}
                </span>
                <span class="badge badge-info ml-2">
                    <i class="fas fa-question-circle"></i> Pregunta {{ Session::get('wizard_pregunta_index', 0) + 1 }}
                </span>
                <div class="mt-2">
                    <a href="{{ route('respuestas.wizard.responder') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-play"></i> Continuar Respondiendo
                    </a>
                    <a href="{{ route('respuestas.wizard.cancel') }}" class="btn btn-outline-danger btn-sm ml-2"
                       onclick="return confirm('¿Estás seguro de que quieres cancelar el wizard? Se perderán los datos de la sesión.')">
                        <i class="fas fa-times"></i> Cancelar Sesión
                    </a>
                </div>
            </p>
        </div>
    @endif

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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-info"></i> Información</h5>
            {{ session('info') }}
        </div>
    @endif

    <!-- TARJETA PRINCIPAL -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title">
                <i class="fas fa-clipboard-check"></i> Selecciona una Encuesta para Responder
            </h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    <i class="fas fa-list"></i> {{ $encuestas->count() }} encuesta(s) disponible(s)
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb"></i> ¿Cómo funciona el Wizard de Respuestas?</h5>
                        <ol class="mb-0">
                            <li><strong>Selecciona una encuesta</strong> de la lista de abajo</li>
                            <li><strong>Responde las preguntas</strong> una por una en el flujo del wizard</li>
                            <li><strong>Revisa el resumen</strong> de todas tus respuestas</li>
                            <li><strong>Confirma y finaliza</strong> el proceso</li>
                            <li><strong>¡Listo!</strong> Tus respuestas han sido guardadas</li>
                        </ol>
                    </div>
                </div>
            </div>

            @if($encuestas->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay encuestas disponibles</h4>
                    <p class="text-muted">No hay encuestas publicadas y habilitadas para responder en este momento.</p>
                    <a href="{{ route('respuestas.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Volver a Respuestas
                    </a>
                </div>
            @else
                <div class="row">
                    @foreach($encuestas as $encuesta)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 border-primary hover-shadow">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-poll text-primary"></i>
                                        {{ Str::limit($encuesta->titulo, 30) }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-building"></i>
                                                <strong>Empresa:</strong> {{ $encuesta->empresa->nombre ?? 'Sin empresa' }}
                                            </p>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-question-circle"></i>
                                                <strong>Preguntas:</strong> {{ $encuesta->preguntas->count() }}
                                            </p>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-clock"></i>
                                                <strong>Creada:</strong> {{ $encuesta->created_at->format('d/m/Y') }}
                                            </p>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-check-circle text-success"></i>
                                                <strong>Estado:</strong> {{ ucfirst($encuesta->estado) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light">
                                    <div class="row">
                                        <div class="col-12">
                                            <a href="{{ route('respuestas.wizard.responder', ['encuesta_id' => $encuesta->id]) }}"
                                               class="btn btn-primary btn-block">
                                                <i class="fas fa-play"></i> Comenzar a Responder
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

@section('css')
<style>
    .progress-bar {
        font-size: 0.7rem;
    }

    .card {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
    }

    .card-title {
        font-weight: 600;
    }

    .btn-block {
        font-weight: 600;
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

        // Confirmación antes de cancelar
        $('a[href*="cancel"]').click(function(e) {
            if (!confirm('¿Estás seguro de que quieres cancelar el wizard? Se perderán los datos de la sesión.')) {
                e.preventDefault();
                return false;
            }
        });
    });
</script>
@endsection
