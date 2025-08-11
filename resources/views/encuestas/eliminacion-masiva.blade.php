@extends('layouts.app')

@section('title', 'Eliminación Masiva de Encuestas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h3 class="card-title">
                        <i class="fas fa-trash-alt"></i>
                        Eliminación Masiva de Encuestas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h4><i class="fas fa-exclamation-triangle"></i> Selección de Encuestas</h4>
                        <p class="mb-0">
                            Selecciona las encuestas que deseas eliminar. Esta acción eliminará permanentemente
                            todas las encuestas seleccionadas junto con todos sus datos relacionados.
                        </p>
                    </div>

                    @if($encuestas->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            No hay encuestas disponibles para eliminar.
                            <a href="{{ route('encuestas.index') }}" class="btn btn-primary btn-sm ml-2">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    @else
                        <form action="{{ route('encuestas.confirmar-eliminacion-masiva') }}" method="POST" id="form-eliminacion-masiva">
                            @csrf

                            <!-- Controles de selección -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-outline-primary" id="seleccionar-todas">
                                            <i class="fas fa-check-square"></i> Seleccionar Todas
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" id="deseleccionar-todas">
                                            <i class="fas fa-square"></i> Deseleccionar Todas
                                        </button>
                                        <button type="button" class="btn btn-outline-info" id="seleccionar-borradores">
                                            <i class="fas fa-file-alt"></i> Solo Borradores
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span class="badge badge-info" id="contador-seleccionadas">
                                        0 encuestas seleccionadas
                                    </span>
                                </div>
                            </div>

                            <!-- Tabla de encuestas -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">
                                                <input type="checkbox" id="seleccionar-todo" class="form-check-input">
                                            </th>
                                            <th width="5%">ID</th>
                                            <th width="25%">Título</th>
                                            <th width="15%">Empresa</th>
                                            <th width="15%">Usuario</th>
                                            <th width="10%">Estado</th>
                                            <th width="15%">Datos</th>
                                            <th width="10%">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($encuestas as $encuesta)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="encuesta_ids[]" value="{{ $encuesta->id }}"
                                                       class="form-check-input encuesta-checkbox">
                                            </td>
                                            <td>{{ $encuesta->id }}</td>
                                            <td>
                                                <strong>{{ $encuesta->titulo }}</strong>
                                                @if($encuesta->fecha_fin)
                                                    <br><small class="text-muted">Válida hasta: {{ $encuesta->fecha_fin->format('d/m/Y') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $encuesta->empresa->nombre ?? 'Sin empresa' }}
                                                @if($encuesta->empresa)
                                                    <br><small class="text-muted">{{ $encuesta->empresa->nit ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $encuesta->user->name ?? 'Sin usuario' }}
                                                @if($encuesta->user)
                                                    <br><small class="text-muted">{{ $encuesta->user->email ?? '' }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $encuesta->estado === 'publicada' ? 'success' : ($encuesta->estado === 'borrador' ? 'secondary' : 'info') }}">
                                                    {{ ucfirst($encuesta->estado) }}
                                                </span>
                                                @if($encuesta->created_at)
                                                    <br><small class="text-muted">{{ $encuesta->created_at->format('d/m/Y') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-question-circle"></i> {{ $encuesta->preguntas->count() }} preguntas<br>
                                                    @if($encuesta->preguntas->count() > 0)
                                                        <i class="fas fa-list"></i> {{ $encuesta->preguntas->sum(function($p) { return $p->respuestas->count(); }) }} respuestas
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                <a href="{{ route('encuestas.show', $encuesta->id) }}"
                                                   class="btn btn-info btn-sm" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('encuestas.confirmar-eliminacion', $encuesta->id) }}"
                                                   class="btn btn-danger btn-sm" title="Eliminar individual">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Botones de acción -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('encuestas.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Cancelar
                                        </a>

                                        <div>
                                            <button type="submit" class="btn btn-danger" id="btn-continuar" disabled>
                                                <i class="fas fa-trash"></i> Continuar con Eliminación
                                                <span id="contador-boton">(0)</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function() {
    const checkboxes = $('.encuesta-checkbox');
    const selectAllCheckbox = $('#seleccionar-todo');
    const contadorSeleccionadas = $('#contador-seleccionadas');
    const contadorBoton = $('#contador-boton');
    const btnContinuar = $('#btn-continuar');
    const form = $('#form-eliminacion-masiva');

    // Función para actualizar contadores
    function actualizarContadores() {
        const seleccionadas = checkboxes.filter(':checked').length;
        contadorSeleccionadas.text(seleccionadas + ' encuestas seleccionadas');
        contadorBoton.text('(' + seleccionadas + ')');

        if (seleccionadas > 0) {
            btnContinuar.prop('disabled', false);
        } else {
            btnContinuar.prop('disabled', true);
        }
    }

    // Seleccionar/Deseleccionar todo
    selectAllCheckbox.on('change', function() {
        checkboxes.prop('checked', this.checked);
        actualizarContadores();
    });

    // Botón seleccionar todas
    $('#seleccionar-todas').on('click', function() {
        checkboxes.prop('checked', true);
        selectAllCheckbox.prop('checked', true);
        actualizarContadores();
    });

    // Botón deseleccionar todas
    $('#deseleccionar-todas').on('click', function() {
        checkboxes.prop('checked', false);
        selectAllCheckbox.prop('checked', false);
        actualizarContadores();
    });

    // Botón seleccionar solo borradores
    $('#seleccionar-borradores').on('click', function() {
        checkboxes.prop('checked', false);
        checkboxes.each(function() {
            const row = $(this).closest('tr');
            const estado = row.find('.badge').text().toLowerCase();
            if (estado.includes('borrador')) {
                $(this).prop('checked', true);
            }
        });
        selectAllCheckbox.prop('checked', checkboxes.filter(':checked').length === checkboxes.length);
        actualizarContadores();
    });

    // Cambio en checkboxes individuales
    checkboxes.on('change', function() {
        selectAllCheckbox.prop('checked', checkboxes.filter(':checked').length === checkboxes.length);
        actualizarContadores();
    });

    // Confirmación antes de enviar formulario
    form.on('submit', function(e) {
        const seleccionadas = checkboxes.filter(':checked').length;

        if (seleccionadas === 0) {
            e.preventDefault();
            alert('Debes seleccionar al menos una encuesta para eliminar.');
            return false;
        }

        const confirmacion = confirm(
            `¿Estás seguro de que quieres eliminar ${seleccionadas} encuesta(s)?\n\n` +
            'Esta acción no se puede deshacer y eliminará permanentemente:\n' +
            '• Todas las preguntas de las encuestas\n' +
            '• Todas las respuestas de usuarios\n' +
            '• Todos los tokens de acceso\n' +
            '• Todas las configuraciones de envío\n' +
            '• Todos los bloques de envío\n\n' +
            '¿Continuar con la eliminación?'
        );

        if (!confirmacion) {
            e.preventDefault();
            return false;
        }
    });

    // Inicializar contadores
    actualizarContadores();
});
</script>
@endpush
