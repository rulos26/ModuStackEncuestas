@extends('adminlte::page')

@section('title', 'Confirmar Eliminación Masiva')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Confirmar Eliminación Masiva
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle"></i> ¡ADVERTENCIA CRÍTICA!</h4>
                        <p class="mb-0">
                            Estás a punto de eliminar permanentemente <strong>{{ count($encuestas) }} encuestas</strong>
                            y <strong>TODOS</strong> sus datos relacionados. Esta acción no se puede deshacer.
                        </p>
                    </div>

                    <!-- Resumen de eliminación -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-info-circle"></i> Resumen de Eliminación</h5>
                            <div class="alert alert-warning">
                                <ul class="mb-0">
                                    <li><strong>{{ $estadisticasTotales['encuestas_count'] }}</strong> encuestas</li>
                                    <li><strong>{{ $estadisticasTotales['preguntas_count'] }}</strong> preguntas</li>
                                    <li><strong>{{ $estadisticasTotales['respuestas_count'] }}</strong> opciones de respuesta</li>
                                    <li><strong>{{ $estadisticasTotales['respuestas_usuarios_count'] }}</strong> respuestas de usuarios</li>
                                    <li><strong>{{ $estadisticasTotales['bloques_envio_count'] }}</strong> bloques de envío</li>
                                    <li><strong>{{ $estadisticasTotales['tokens_acceso_count'] }}</strong> tokens de acceso</li>
                                    <li><strong>{{ $estadisticasTotales['configuraciones_envio_count'] }}</strong> configuraciones de envío</li>
                                    <li><strong>{{ $estadisticasTotales['correos_enviados_count'] }}</strong> correos enviados</li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h5><i class="fas fa-chart-bar"></i> Impacto por Tabla</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tabla</th>
                                            <th>Registros</th>
                                            <th>Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-danger">
                                            <td><strong>encuestas</strong></td>
                                            <td>{{ $estadisticasTotales['encuestas_count'] }}</td>
                                            <td><span class="badge badge-danger">ELIMINAR</span></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td><strong>preguntas</strong></td>
                                            <td>{{ $estadisticasTotales['preguntas_count'] }}</td>
                                            <td><span class="badge badge-danger">CASCADE</span></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td><strong>respuestas</strong></td>
                                            <td>{{ $estadisticasTotales['respuestas_count'] }}</td>
                                            <td><span class="badge badge-danger">CASCADE</span></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td><strong>respuestas_usuario</strong></td>
                                            <td>{{ $estadisticasTotales['respuestas_usuarios_count'] }}</td>
                                            <td><span class="badge badge-danger">CASCADE</span></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td><strong>bloques_envio</strong></td>
                                            <td>{{ $estadisticasTotales['bloques_envio_count'] }}</td>
                                            <td><span class="badge badge-danger">CASCADE</span></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td><strong>tokens_encuesta</strong></td>
                                            <td>{{ $estadisticasTotales['tokens_acceso_count'] }}</td>
                                            <td><span class="badge badge-danger">CASCADE</span></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td><strong>configuracion_envios</strong></td>
                                            <td>{{ $estadisticasTotales['configuraciones_envio_count'] }}</td>
                                            <td><span class="badge badge-danger">CASCADE</span></td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td><strong>sent_mails</strong></td>
                                            <td>{{ $estadisticasTotales['correos_enviados_count'] }}</td>
                                            <td><span class="badge badge-warning">SET NULL</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de encuestas a eliminar -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5><i class="fas fa-list"></i> Encuestas que se Eliminarán</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Título</th>
                                            <th>Empresa</th>
                                            <th>Usuario</th>
                                            <th>Estado</th>
                                            <th>Preguntas</th>
                                            <th>Creada</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($encuestas as $encuesta)
                                        <tr>
                                            <td>{{ $encuesta->id }}</td>
                                            <td>
                                                <strong>{{ $encuesta->titulo }}</strong>
                                                @if($encuesta->fecha_fin)
                                                    <br><small class="text-muted">Válida hasta: {{ $encuesta->fecha_fin->format('d/m/Y') }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}</td>
                                            <td>{{ $encuesta->user->name ?? 'Sin usuario' }}</td>
                                            <td>
                                                <span class="badge badge-{{ $encuesta->estado === 'publicada' ? 'success' : ($encuesta->estado === 'borrador' ? 'secondary' : 'info') }}">
                                                    {{ ucfirst($encuesta->estado) }}
                                                </span>
                                            </td>
                                            <td>{{ $encuesta->preguntas->count() }}</td>
                                            <td>{{ $encuesta->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmación final -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <h6><i class="fas fa-exclamation-triangle"></i> Confirmación Final</h6>
                                <p class="mb-2">
                                    Para confirmar la eliminación masiva, escribe <strong>"ELIMINAR MASIVO"</strong> en el campo de abajo:
                                </p>
                                <div class="form-group">
                                    <input type="text" id="confirmacion-texto" class="form-control"
                                           placeholder="Escribe 'ELIMINAR MASIVO' para confirmar"
                                           style="max-width: 400px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                                        <a href="{{ route('encuestas.eliminacion-masiva') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>

                                <div>
                                    <button type="button" id="btn-eliminar" class="btn btn-danger" disabled>
                                        <i class="fas fa-trash"></i> Eliminar {{ count($encuestas) }} Encuestas
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
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmación Final de Eliminación Masiva
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h6>⚠️ ÚLTIMA ADVERTENCIA</h6>
                    <p>Estás a punto de eliminar <strong>{{ count($encuestas) }} encuestas</strong> de forma permanente.</p>
                    <p><strong>Esta acción no se puede deshacer.</strong></p>
                </div>

                <p>¿Estás completamente seguro de que quieres proceder con la eliminación masiva?</p>

                <div class="alert alert-info">
                    <small>
                        <strong>Resumen:</strong><br>
                        • {{ $estadisticasTotales['encuestas_count'] }} encuestas<br>
                        • {{ $estadisticasTotales['preguntas_count'] }} preguntas<br>
                        • {{ $estadisticasTotales['respuestas_usuarios_count'] }} respuestas de usuarios<br>
                        • {{ $estadisticasTotales['tokens_acceso_count'] }} tokens de acceso<br>
                        • {{ $estadisticasTotales['configuraciones_envio_count'] }} configuraciones de envío
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <form action="{{ route('encuestas.ejecutar-eliminacion-masiva') }}" method="POST" style="display: inline;">
                    @csrf
                    @foreach($encuestas as $encuesta)
                        <input type="hidden" name="encuesta_ids[]" value="{{ $encuesta->id }}">
                    @endforeach
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, Eliminar {{ count($encuestas) }} Encuestas Definitivamente
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
        if (texto === 'ELIMINAR MASIVO') {
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
