@extends('adminlte::page')

@section('title', 'Wizard de Preguntas - Seleccionar Encuesta')

@section('content_header')
    <h1>
        <i class="fas fa-magic"></i> Wizard de Preguntas
        <small>Paso 1: Seleccionar Encuesta</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item active">Wizard de Preguntas</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL WIZARD -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-list"></i> 1. Seleccionar Encuesta</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 33%;" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-question-circle"></i> 2. Crear Pregunta</strong>
                </div>
                <div class="progress-bar bg-secondary" role="progressbar" style="width: 34%;" aria-valuenow="34" aria-valuemin="0" aria-valuemax="100">
                    <strong><i class="fas fa-check"></i> 3. Confirmar</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTADOR DE PREGUNTAS EN SESIÓN -->
    @if(Session::get('wizard_preguntas_count', 0) > 0 && Session::get('wizard_encuesta_id'))
        <div class="alert alert-success">
            <h5><i class="fas fa-play-circle"></i> Sesión Activa</h5>
            <p class="mb-0">
                <strong>{{ Session::get('wizard_preguntas_count', 0) }}</strong> pregunta(s) creada(s) en esta sesión.
                <span class="badge badge-primary ml-2">
                    <i class="fas fa-poll"></i> Encuesta ID: {{ Session::get('wizard_encuesta_id') }}
                </span>
                <div class="mt-2">
                    <a href="{{ route('preguntas.wizard.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i> Continuar Agregando Preguntas
                    </a>
                    <a href="{{ route('preguntas.wizard.cancel') }}" class="btn btn-outline-danger btn-sm ml-2"
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
                <i class="fas fa-poll"></i> Selecciona una Encuesta
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
                        <h5><i class="fas fa-lightbulb"></i> ¿Cómo funciona el Wizard?</h5>
                        <ol class="mb-0">
                            <li><strong>Selecciona una encuesta</strong> de la lista de abajo</li>
                            <li><strong>Crea preguntas</strong> usando el formulario simplificado</li>
                            <li><strong>Confirma si quieres continuar</strong> agregando más preguntas</li>
                            <li><strong>Finaliza</strong> cuando hayas terminado</li>
                        </ol>
                    </div>
                </div>
            </div>

            @if($encuestas->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay encuestas disponibles</h4>
                    <p class="text-muted">Para usar el wizard de preguntas, primero debes crear una encuesta.</p>
                    <a href="{{ route('encuestas.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Crear Encuesta
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
                                                <i class="fas fa-calendar"></i>
                                                <strong>Creada:</strong> {{ $encuesta->created_at->format('d/m/Y') }}
                                            </p>
                                            <p class="mb-2">
                                                <span class="badge badge-{{ $encuesta->estado === 'borrador' ? 'warning' : ($encuesta->estado === 'publicada' ? 'success' : 'info') }}">
                                                    {{ ucfirst($encuesta->estado) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-light">
                                    <form action="{{ route('preguntas.wizard.create') }}" method="GET" class="d-inline">
                                        <input type="hidden" name="encuesta_id" value="{{ $encuesta->id }}">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-arrow-right"></i> Seleccionar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('encuestas.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Encuestas
                    </a>
                </div>
                <div class="col-md-6 text-right">
                    <span class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        El wizard te permitirá crear múltiples preguntas de forma rápida y eficiente.
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    .hover-shadow:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-2px);
        transition: all 0.3s ease;
    }

    .progress-bar {
        font-size: 0.8rem;
    }

    .card {
        transition: all 0.3s ease;
    }

    .border-primary {
        border-color: #007bff !important;
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
    });
</script>
@endsection
