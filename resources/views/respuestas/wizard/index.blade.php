@extends('layouts.app')

@section('title', 'Wizard de Respuestas - Configuración Administrativa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Wizard de Configuración de Respuestas
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">
                            <i class="fas fa-info-circle"></i> Uso Administrativo
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-lightbulb"></i> ¿Qué hace este wizard?</h5>
                        <p class="mb-0">
                            Este wizard te permite <strong>configurar las respuestas concretas</strong> para las preguntas de tipo
                            "Selección Única" y "Casillas de Verificación" que ya han sido creadas en las encuestas.
                        </p>
                    </div>

                    @if(Session::get('wizard_respuestas_count', 0) > 0 && Session::get('wizard_encuesta_id'))
                        <div class="alert alert-success">
                            <h5><i class="fas fa-play-circle"></i> Sesión Activa</h5>
                            <p class="mb-0">
                                <strong>{{ Session::get('wizard_respuestas_count', 0) }}</strong> respuesta(s) configurada(s) en esta sesión.
                                <span class="badge badge-primary ml-2">
                                    <i class="fas fa-poll"></i> Encuesta ID: {{ Session::get('wizard_encuesta_id') }}
                                </span>
                                <div class="mt-2">
                                    <a href="{{ route('respuestas.wizard.responder') }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Continuar Configurando Respuestas
                                    </a>
                                    <a href="{{ route('respuestas.wizard.cancel') }}" class="btn btn-outline-danger btn-sm ml-2"
                                       onclick="return confirm('¿Estás seguro de que quieres cancelar el wizard? Se perderán los datos de la sesión.')">
                                        <i class="fas fa-times"></i> Cancelar Sesión
                                    </a>
                                </div>
                            </p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12">
                            <h5><i class="fas fa-list"></i> Encuestas que Requieren Configuración de Respuestas</h5>
                            <p class="text-muted">
                                Selecciona una encuesta para configurar las respuestas de sus preguntas de selección.
                            </p>
                        </div>
                    </div>

                    @if($encuestas->count() > 0)
                        <div class="row">
                            @foreach($encuestas as $encuesta)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="card-title mb-0">
                                                <i class="fas fa-poll"></i> {{ $encuesta->titulo }}
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6">
                                                    <small class="text-muted">Empresa:</small>
                                                    <p class="mb-1"><strong>{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}</strong></p>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Estado:</small>
                                                    <p class="mb-1">
                                                        <span class="badge badge-{{ $encuesta->estado === 'publicada' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($encuesta->estado) }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="row mt-2">
                                                <div class="col-6">
                                                    <small class="text-muted">Total Preguntas:</small>
                                                    <p class="mb-1"><strong>{{ $encuesta->preguntas->count() }}</strong></p>
                                                </div>
                                                <div class="col-6">
                                                    <small class="text-muted">Sin Respuestas:</small>
                                                    <p class="mb-1">
                                                        <span class="badge badge-danger">
                                                            {{ $encuesta->preguntas->filter(function($p) {
                                                                return $p->respuestas->isEmpty() &&
                                                                       in_array($p->tipo, ['seleccion_unica', 'casillas_verificacion']);
                                                            })->count() }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <a href="{{ route('respuestas.wizard.responder', ['encuesta_id' => $encuesta->id]) }}"
                                                   class="btn btn-primary btn-block">
                                                    <i class="fas fa-cogs"></i> Configurar Respuestas
                                                </a>
                                            </div>
                                        </div>
                                        <div class="card-footer text-muted">
                                            <small>
                                                <i class="fas fa-calendar"></i> Creada: {{ $encuesta->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            <h4 class="mt-3 text-success">¡Excelente trabajo!</h4>
                            <p class="text-muted">
                                Todas las encuestas ya tienen sus respuestas configuradas correctamente.
                            </p>
                            <a href="{{ route('encuestas.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Volver a Encuestas
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Confirmación antes de cancelar
    $('.btn-cancel').click(function(e) {
        if (!confirm('¿Estás seguro de que quieres cancelar el wizard?')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
