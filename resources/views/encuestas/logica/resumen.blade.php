@extends('adminlte::page')

@section('title', 'Resumen de Lógica')

@section('content_header')
    <h1>
        <i class="fas fa-clipboard-check"></i> Resumen de Lógica Configurada
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.logica.create', $encuesta->id) }}">Configurar Lógica</a></li>
            <li class="breadcrumb-item active">Resumen</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <!-- RESUMEN DE LÓGICA -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-check"></i> Lógica Configurada
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('encuestas.logica.create', $encuesta->id) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Editar Lógica
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(count($resumenLogica) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Pregunta</th>
                                        <th>Respuesta</th>
                                        <th>Acción</th>
                                        <th>Estado</th>
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
                                            <td>
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Configurado
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h4>No hay lógica configurada</h4>
                            <p>Configure la lógica de saltos para personalizar el flujo de la encuesta.</p>
                            <a href="{{ route('encuestas.logica.create', $encuesta->id) }}" class="btn btn-primary">
                                <i class="fas fa-cogs"></i> Configurar Lógica
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- PANEL DE ACCIONES -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Acciones
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(count($resumenLogica) > 0)
                            <a href="{{ route('encuestas.preview', $encuesta->id) }}" class="btn btn-success btn-lg">
                                <i class="fas fa-eye"></i> Vista Previa
                            </a>
                        @endif

                        <a href="{{ route('encuestas.logica.create', $encuesta->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Editar Lógica
                        </a>

                        <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info">
                            <i class="fas fa-arrow-left"></i> Volver a la Encuesta
                        </a>
                    </div>
                </div>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total preguntas
                            <span class="badge badge-primary badge-pill">{{ $encuesta->preguntas->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Preguntas con lógica
                            <span class="badge badge-success badge-pill">{{ count($resumenLogica) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Saltos configurados
                            <span class="badge badge-info badge-pill">{{ collect($resumenLogica)->where('siguiente_pregunta', '!=', null)->count() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Finalizaciones
                            <span class="badge badge-danger badge-pill">{{ collect($resumenLogica)->where('finalizar', true)->count() }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endsection
