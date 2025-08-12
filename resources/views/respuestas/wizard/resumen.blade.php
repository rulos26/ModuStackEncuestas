@extends('adminlte::page')

@section('title', 'Resumen de Configuración de Respuestas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- PROGRESO DEL WIZARD -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                            <strong><i class="fas fa-check-circle"></i> Configuración Completada</strong>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Todas las respuestas han sido configuradas exitosamente
                        </small>
                    </div>
                </div>
            </div>

            <!-- ESTADÍSTICAS FINALES -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Configuración Completada</span>
                            <span class="info-box-number">100%</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Todas las preguntas procesadas
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-poll"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Encuesta</span>
                            <span class="info-box-number">{{ $encuesta->titulo }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                ID: {{ $encuesta->id }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-cogs"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas Configuradas</span>
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
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sesión Activa</span>
                            <span class="info-box-number">{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $encuesta->estado }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RESUMEN PRINCIPAL -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle"></i> Resumen de Configuración de Respuestas
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            <i class="fas fa-info-circle"></i> Uso Administrativo
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <h5><i class="fas fa-thumbs-up"></i> ¡Configuración Exitosa!</h5>
                        <p class="mb-0">
                            Se han configurado <strong>{{ $respuestasCount }}</strong> respuesta(s) para las preguntas de la encuesta
                            "<strong>{{ $encuesta->titulo }}</strong>". Ahora los usuarios podrán responder estas preguntas con las opciones que has definido.
                        </p>
                    </div>

                    <!-- TABLA DE PREGUNTAS CON SUS RESPUESTAS -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Pregunta</th>
                                    <th width="15%">Tipo</th>
                                    <th width="35%">Opciones de Respuesta</th>
                                    <th width="10%">Cantidad</th>
                                    <th width="10%">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($preguntasConRespuestas as $index => $pregunta)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge badge-primary">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $pregunta->pregunta }}</strong>
                                            @if($pregunta->descripcion)
                                                <br><small class="text-muted">{{ $pregunta->descripcion }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $pregunta->tipo === 'seleccion_unica' ? 'primary' : ($pregunta->tipo === 'casillas_verificacion' ? 'success' : 'info') }}">
                                                <i class="fas fa-{{ $pregunta->tipo === 'seleccion_unica' ? 'dot-circle' : ($pregunta->tipo === 'casillas_verificacion' ? 'check-square' : 'list-check') }}"></i>
                                                @if($pregunta->tipo === 'seleccion_unica')
                                                    Selección Única
                                                @elseif($pregunta->tipo === 'casillas_verificacion')
                                                    Casillas
                                                @elseif($pregunta->tipo === 'seleccion_multiple')
                                                    Selección Múltiple
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            @if($pregunta->respuestas->count() > 0)
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($pregunta->respuestas->sortBy('orden') as $respuesta)
                                                        <li>
                                                            <i class="fas fa-check text-success"></i>
                                                            <strong>{{ $respuesta->orden }}.</strong> {{ $respuesta->texto }}
                                                            @if(!$respuesta->activa)
                                                                <span class="badge badge-secondary">Inactiva</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-exclamation-triangle"></i> Sin respuestas configuradas
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $pregunta->respuestas->count() }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($pregunta->respuestas->count() > 0)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Configurada
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times"></i> Pendiente
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No hay preguntas para mostrar
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <a href="{{ route('respuestas.wizard.index') }}" class="btn btn-outline-primary btn-lg btn-block">
                                <i class="fas fa-plus"></i> Configurar Otra Encuesta
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-outline-info btn-lg btn-block">
                                <i class="fas fa-eye"></i> Ver Encuesta
                            </a>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('respuestas.wizard.confirmar') }}" method="POST" style="display: inline;">
                                @csrf
                                <input type="hidden" name="action" value="finish">
                                <button type="submit" class="btn btn-success btn-lg btn-block">
                                    <i class="fas fa-flag-checkered"></i> Finalizar Wizard
                                </button>
                            </form>
                        </div>
                    </div>
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

    // Confirmación antes de finalizar
    $('form[action*="confirmar"]').submit(function(e) {
        if (!confirm('¿Estás seguro de que quieres finalizar el wizard? Se limpiará la sesión actual.')) {
            e.preventDefault();
        }
    });
});
</script>
@endsection
