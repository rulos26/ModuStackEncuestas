@extends('adminlte::page')

@section('title', 'Resultado del Envío')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle mr-2"></i>
                        Resultado del Envío Masivo
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ $resultado['exitosos'] > 0 ? 'success' : 'danger' }}">
                            {{ $resultado['exitosos'] }} / {{ $resultado['total'] }} enviados
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Resumen general -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-users"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Empleados</span>
                                    <span class="info-box-number">{{ $resultado['total'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-check"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Enviados Exitosamente</span>
                                    <span class="info-box-number">{{ $resultado['exitosos'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-times"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Fallidos</span>
                                    <span class="info-box-number">{{ $resultado['fallidos_count'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info">
                                    <i class="fas fa-percentage"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tasa de Éxito</span>
                                    <span class="info-box-number">
                                        {{ $resultado['total'] > 0 ? round(($resultado['exitosos'] / $resultado['total']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información de la encuesta -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-poll mr-1"></i>Encuesta Enviada</h6>
                        <strong>Título:</strong> {{ $encuesta->titulo }}<br>
                        <strong>Empresa:</strong> {{ $encuesta->empresa ? $encuesta->empresa->nombre : 'Sin empresa' }}<br>
                        <strong>Estado:</strong> {{ ucfirst($encuesta->estado) }}<br>
                        <strong>Links públicos:</strong><br>
                        <strong>📱 Vista de Pruebas:</strong>
                        <a href="{{ url('/testing/encuesta-publica/vista/' . $encuesta->id) }}" target="_blank">
                            {{ url('/testing/encuesta-publica/vista/' . $encuesta->id) }}
                        </a><br>
                        <strong>🌐 Página Pública:</strong>
                        <a href="{{ url('/publica/encuesta/' . $encuesta->id) }}" target="_blank">
                            {{ url('/publica/encuesta/' . $encuesta->id) }}
                        </a>
                    </div>

                    @if($resultado['exitosos'] > 0)
                        <div class="alert alert-success">
                            <h6><i class="fas fa-check-circle mr-1"></i>Correos Enviados Exitosamente</h6>
                            <p class="mb-0">Se enviaron {{ $resultado['exitosos'] }} correos correctamente.</p>
                        </div>
                    @endif

                    @if($resultado['fallidos_count'] > 0)
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle mr-1"></i>Correos Fallidos</h6>
                            <p class="mb-0">{{ $resultado['fallidos_count'] }} correos no pudieron ser enviados.</p>
                        </div>
                    @endif

                    <!-- Lista de correos enviados -->
                    @if(count($resultado['enviados']) > 0)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-envelope mr-1"></i>
                                    Correos Enviados ({{ count($resultado['enviados']) }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Empleado</th>
                                                <th>Email</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($resultado['enviados'] as $index => $enviado)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $enviado['empleado'] }}</td>
                                                    <td>{{ $enviado['email'] }}</td>
                                                    <td>
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check mr-1"></i>
                                                            Enviado
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Lista de correos fallidos -->
                    @if(count($resultado['fallidos']) > 0)
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Correos Fallidos ({{ count($resultado['fallidos']) }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Empleado</th>
                                                <th>Email</th>
                                                <th>Error</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($resultado['fallidos'] as $index => $fallido)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $fallido['empleado'] }}</td>
                                                    <td>{{ $fallido['email'] }}</td>
                                                    <td>
                                                        <span class="badge badge-danger">
                                                            <i class="fas fa-times mr-1"></i>
                                                            {{ $fallido['error'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Botones de acción -->
                    <div class="form-group mt-4">
                        <a href="{{ route('envio-masivo.index') }}" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i>
                            Realizar Otro Envío
                        </a>

                        <a href="{{ route('envio-masivo.estadisticas') }}" class="btn btn-info ml-2">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Ver Estadísticas
                        </a>

                        <a href="{{ route('home') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-home mr-1"></i>
                            Volver al Inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Mostrar mensaje de éxito si todos los correos se enviaron
    @if($resultado['exitosos'] == $resultado['total'] && $resultado['total'] > 0)
        Swal.fire({
            title: '¡Envío Completado!',
            text: 'Todos los correos se enviaron exitosamente.',
            icon: 'success',
            confirmButtonText: 'Entendido'
        });
    @elseif($resultado['fallidos_count'] > 0)
        Swal.fire({
            title: 'Envío Parcial',
            text: '{{ $resultado["exitosos"] }} correos enviados, {{ $resultado["fallidos_count"] }} fallidos.',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
    @endif
});
</script>
@endpush
