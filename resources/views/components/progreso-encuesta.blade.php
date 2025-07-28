@props(['encuesta'])

@php
    $progreso = $encuesta->obtenerProgresoConfiguracion();
@endphp

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tasks"></i> Progreso de Configuración
        </h3>
        <div class="card-tools">
            <span class="badge badge-{{ $progreso['completados'] === $progreso['total'] ? 'success' : 'info' }}">
                {{ $progreso['completados'] }}/{{ $progreso['total'] }} completados
            </span>
        </div>
    </div>
    <div class="card-body">
        <!-- Barra de progreso -->
        <div class="progress mb-3" style="height: 25px;">
            <div class="progress-bar bg-success" role="progressbar"
                 style="width: {{ $progreso['porcentaje'] }}%;"
                 aria-valuenow="{{ $progreso['porcentaje'] }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
                <strong>{{ $progreso['porcentaje'] }}% Completado</strong>
            </div>
        </div>

        <!-- Lista de pasos -->
        <div class="row">
            @foreach($progreso['pasos'] as $clave => $paso)
                <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center p-2 border rounded {{ $paso['completado'] ? 'bg-light' : '' }}">
                        <div class="mr-3">
                            @if($paso['completado'])
                                <i class="fas fa-check-circle text-success" style="font-size: 1.2em;"></i>
                            @elseif($clave === $progreso['siguiente_paso'])
                                <i class="fas fa-arrow-right text-primary" style="font-size: 1.2em;"></i>
                            @else
                                <i class="fas fa-circle text-muted" style="font-size: 1.2em;"></i>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="{{ $paso['completado'] ? 'text-success' : ($clave === $progreso['siguiente_paso'] ? 'text-primary font-weight-bold' : 'text-muted') }}">
                                    <i class="{{ $paso['icono'] }}"></i> {{ $paso['nombre'] }}
                                </span>
                                @if(isset($paso['cantidad']))
                                    <span class="badge badge-{{ $paso['completado'] ? 'success' : 'secondary' }}">
                                        {{ $paso['cantidad'] }}
                                    </span>
                                @endif
                            </div>
                            @if($clave === $progreso['siguiente_paso'])
                                <small class="text-primary">
                                    <i class="fas fa-info-circle"></i> Siguiente paso
                                </small>
                            @endif
                        </div>
                        @if($paso['completado'])
                            <a href="{{ $paso['ruta'] }}" class="btn btn-sm btn-outline-success ml-2" title="Ver/Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                        @else
                            <a href="{{ $paso['ruta'] }}" class="btn btn-sm btn-primary ml-2" title="Completar">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Mensaje de estado -->
        @if($progreso['completados'] === $progreso['total'])
            <div class="alert alert-success mt-3">
                <i class="fas fa-check-circle"></i>
                <strong>¡Configuración completada!</strong> La encuesta está lista para ser enviada.
            </div>
        @elseif($progreso['siguiente_paso'])
            <div class="alert alert-info mt-3">
                <i class="fas fa-info-circle"></i>
                <strong>Siguiente paso:</strong> {{ $progreso['pasos'][$progreso['siguiente_paso']]['nombre'] }}
            </div>
        @endif
    </div>
</div>
