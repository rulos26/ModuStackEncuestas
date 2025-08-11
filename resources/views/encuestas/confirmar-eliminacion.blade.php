@extends('layouts.app')

@section('title', 'Confirmar Eliminación de Encuesta')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Confirmar Eliminación de Encuesta
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle"></i> ¡ADVERTENCIA!</h4>
                        <p class="mb-0">
                            Estás a punto de eliminar permanentemente la encuesta <strong>"{{ $encuesta->titulo }}"</strong>
                            y <strong>TODOS</strong> los datos relacionados. Esta acción no se puede deshacer.
                        </p>
                    </div>

                    <!-- Información de la encuesta -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle"></i> Información de la Encuesta</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th>ID:</th>
                                    <td>{{ $encuesta->id }}</td>
                                </tr>
                                <tr>
                                    <th>Título:</th>
                                    <td>{{ $encuesta->titulo }}</td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge badge-{{ $encuesta->estado === 'publicada' ? 'success' : ($encuesta->estado === 'borrador' ? 'secondary' : 'info') }}">
                                            {{ ucfirst($encuesta->estado) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Empresa:</th>
                                    <td>{{ $encuesta->empresa->nombre ?? 'No asignada' }}</td>
                                </tr>
                                <tr>
                                    <th>Creada por:</th>
                                    <td>{{ $encuesta->user->name ?? 'Usuario no encontrado' }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha de creación:</th>
                                    <td>{{ $encuesta->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5><i class="fas fa-chart-bar"></i> Datos que se Eliminarán</h5>
                            <div class="alert alert-warning">
                                <ul class="mb-0">
                                    <li><strong>{{ $estadisticas['preguntas_count'] }}</strong> preguntas</li>
                                    <li><strong>{{ $estadisticas['respuestas_count'] }}</strong> opciones de respuesta</li>
                                    <li><strong>{{ $estadisticas['respuestas_usuarios_count'] }}</strong> respuestas de usuarios</li>
                                    <li><strong>{{ $estadisticas['bloques_envio_count'] }}</strong> bloques de envío</li>
                                    <li><strong>{{ $estadisticas['tokens_acceso_count'] }}</strong> tokens de acceso</li>
                                    <li><strong>{{ $estadisticas['configuraciones_envio_count'] }}</strong> configuraciones de envío</li>
                                    <li><strong>{{ $estadisticas['correos_enviados_count'] }}</strong> correos enviados</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Detalles adicionales -->
                    @if($encuesta->preguntas->count() > 0)
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5><i class="fas fa-list"></i> Preguntas que se Eliminarán</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Pregunta</th>
                                            <th>Tipo</th>
                                            <th>Respuestas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($encuesta->preguntas as $pregunta)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ Str::limit($pregunta->texto, 50) }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ ucfirst(str_replace('_', ' ', $pregunta->tipo)) }}
                                                </span>
                                            </td>
                                            <td>{{ $pregunta->respuestas->count() }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Confirmación final -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle"></i> Confirmación Final</h6>
                                <p class="mb-2">
                                    Para confirmar la eliminación, escribe <strong>"ELIMINAR"</strong> en el campo de abajo:
                                </p>
                                <div class="form-group">
                                    <input type="text" id="confirmacion-texto" class="form-control"
                                           placeholder="Escribe 'ELIMINAR' para confirmar"
                                           style="max-width: 300px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('encuestas.show', $encuesta) }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </a>

                                <div>
                                    <button type="button" id="btn-eliminar" class="btn btn-danger" disabled>
                                        <i class="fas fa-trash"></i> Eliminar Encuesta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación final -->
<div class="modal fade" id="modalConfirmacionFinal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmación Final
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás completamente seguro de que quieres eliminar esta encuesta?</p>
                <p class="text-danger"><strong>Esta acción no se puede deshacer.</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <form action="{{ route('encuestas.destroy', $encuesta) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, Eliminar Definitivamente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    const confirmacionInput = $('#confirmacion-texto');
    const btnEliminar = $('#btn-eliminar');
    const modalConfirmacion = $('#modalConfirmacionFinal');

    // Habilitar/deshabilitar botón según el texto de confirmación
    confirmacionInput.on('input', function() {
        const texto = $(this).val().trim();
        if (texto === 'ELIMINAR') {
            btnEliminar.prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger');
        } else {
            btnEliminar.prop('disabled', true).removeClass('btn-danger').addClass('btn-secondary');
        }
    });

    // Mostrar modal de confirmación final
    btnEliminar.on('click', function() {
        modalConfirmacion.modal('show');
    });

    // Enfocar el campo de confirmación al cargar la página
    confirmacionInput.focus();
});
</script>
@endpush
