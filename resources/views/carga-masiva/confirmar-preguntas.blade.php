@extends('adminlte::page')

@section('title', 'Confirmar Preguntas')

@section('content_header')
    <h1>
        <i class="fas fa-check-circle"></i> Confirmar Preguntas
        <small class="text-muted">{{ $encuesta->titulo }}</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-check"></i> Resumen de Preguntas a Guardar
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Información</h5>
                        <p class="mb-0">
                            Se van a guardar <strong>{{ count($preguntas) }} preguntas</strong> en la encuesta
                            <strong>"{{ $encuesta->titulo }}"</strong>.
                            Revisa los detalles antes de continuar.
                        </p>
                    </div>

                    <!-- Tabla de Preguntas -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="50%">Pregunta</th>
                                    <th width="20%">Tipo</th>
                                    <th width="15%">Icono</th>
                                    <th width="10%">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preguntas as $index => $pregunta)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge badge-primary">{{ $index + 1 }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $pregunta['texto'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $this->getBadgeColorForType($pregunta['tipo']) }}">
                                                {{ $this->getTypeName($pregunta['tipo']) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <i class="fas fa-{{ $this->getIconForType($pregunta['tipo']) }} fa-2x text-{{ $this->getColorForType($pregunta['tipo']) }}"></i>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Listo
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Estadísticas -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5><i class="fas fa-chart-pie"></i> Estadísticas</h5>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="text-center">
                                                <h3 class="text-primary">{{ count($preguntas) }}</h3>
                                                <small class="text-muted">Total Preguntas</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="text-center">
                                                <h3 class="text-success">{{ count(array_unique(array_column($preguntas, 'tipo'))) }}</h3>
                                                <small class="text-muted">Tipos Diferentes</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5><i class="fas fa-list"></i> Distribución por Tipo</h5>
                                    @php
                                        $tiposCount = array_count_values(array_column($preguntas, 'tipo'));
                                    @endphp
                                    @foreach($tiposCount as $tipo => $count)
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="badge badge-{{ $this->getBadgeColorForType($tipo) }}">
                                                {{ $this->getTypeName($tipo) }}
                                            </span>
                                            <span class="badge badge-secondary">{{ $count }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <form action="{{ route('carga-masiva.guardar-preguntas') }}" method="POST">
                                @csrf
                                <input type="hidden" name="cache_key" value="{{ $cacheKey }}">

                                <div class="btn-group" role="group">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-save"></i> Guardar Preguntas
                                    </button>
                                    <a href="{{ route('carga-masiva.wizard-preguntas', ['cache_key' => $cacheKey, 'pregunta' => 0]) }}" class="btn btn-warning btn-lg">
                                        <i class="fas fa-edit"></i> Editar Tipos
                                    </a>
                                    <a href="{{ route('carga-masiva.index') }}" class="btn btn-danger btn-lg">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
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

.table th {
    background-color: #343a40;
    color: white;
    border-color: #454d55;
}

.badge {
    font-size: 0.875rem;
}

.btn-group .btn {
    margin-right: 0.5rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.alert {
    border-radius: 0.5rem;
}
</style>
@stop

@section('js')
<script>
// Confirmación antes de guardar
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();

    Swal.fire({
        title: '¿Confirmar guardado?',
        text: 'Se van a guardar {{ count($preguntas) }} preguntas en la encuesta "{{ $encuesta->titulo }}". ¿Estás seguro?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Mostrar loading
            Swal.fire({
                title: 'Guardando preguntas...',
                html: 'Procesando {{ count($preguntas) }} preguntas...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar formulario
            this.submit();
        }
    });
});

// Animación de entrada para las filas de la tabla
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        setTimeout(() => {
            row.style.opacity = '0';
            row.style.transform = 'translateY(20px)';
            row.style.transition = 'all 0.3s ease';

            setTimeout(() => {
                row.style.opacity = '1';
                row.style.transform = 'translateY(0)';
            }, 100);
        }, index * 100);
    });
});
</script>
@stop
