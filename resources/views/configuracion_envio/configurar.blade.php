@extends('adminlte::page')

@section('title', 'Configurar Envío de Encuestas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Configurar Envío de Encuestas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-building"></i> Empresa: {{ $empresa->nombre }}</h5>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-list"></i> Encuestas seleccionadas: {{ $encuestas->count() }}</h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wizard Form -->
            <form id="configuracionForm" method="POST" action="{{ route('configuracion-envio.store') }}">
                @csrf
                <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">

                @foreach($encuestas as $encuesta)
                    <div class="card card-outline card-info mb-4">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="fas fa-poll"></i> {{ $encuesta->titulo }}
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Paso 1: Configuración Básica -->
                            <div class="wizard-step" data-step="1">
                                <h5><i class="fas fa-envelope"></i> Configuración de Correo</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombre_remitente_{{ $encuesta->id }}">
                                                <i class="fas fa-user"></i> Nombre del Remitente
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="nombre_remitente_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][nombre_remitente]"
                                                   value="{{ old('encuestas.' . $loop->index . '.nombre_remitente', $empresa->nombre) }}"
                                                   required>
                                            <small class="form-text text-muted">
                                                Nombre que aparecerá como remitente del correo
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="correo_remitente_{{ $encuesta->id }}">
                                                <i class="fas fa-envelope"></i> Correo del Remitente
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="email"
                                                   class="form-control"
                                                   id="correo_remitente_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][correo_remitente]"
                                                   value="{{ old('encuestas.' . $loop->index . '.correo_remitente', $empresa->correo_electronico) }}"
                                                   required>
                                            <small class="form-text text-muted">
                                                Correo electrónico del remitente
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="asunto_{{ $encuesta->id }}">
                                                <i class="fas fa-tag"></i> Asunto del Mensaje
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="asunto_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][asunto]"
                                                   value="{{ old('encuestas.' . $loop->index . '.asunto', 'Invitación a participar en: ' . $encuesta->titulo) }}"
                                                   required>
                                            <small class="form-text text-muted">
                                                Asunto del correo electrónico
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="cuerpo_mensaje_{{ $encuesta->id }}">
                                                <i class="fas fa-file-alt"></i> Cuerpo del Mensaje
                                                <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control"
                                                      id="cuerpo_mensaje_{{ $encuesta->id }}"
                                                      name="encuestas[{{ $loop->index }}][cuerpo_mensaje]"
                                                      rows="6"
                                                      required>{{ old('encuestas.' . $loop->index . '.cuerpo_mensaje', 'Estimado participante,

Le invitamos a participar en nuestra encuesta: ' . $encuesta->titulo . '

Su opinión es muy importante para nosotros.

Haga clic en el siguiente enlace para acceder a la encuesta:
' . $link_encuesta . '

Gracias por su participación.

Saludos cordiales,
' . $empresa->nombre) }}</textarea>
                                            <small class="form-text text-muted">
                                                Contenido del correo electrónico. Puede usar {{ $link_encuesta }} para el enlace de la encuesta.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipo_envio_{{ $encuesta->id }}">
                                                <i class="fas fa-clock"></i> Tipo de Envío
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control tipo-envio-select"
                                                    id="tipo_envio_{{ $encuesta->id }}"
                                                    name="encuestas[{{ $loop->index }}][tipo_envio]"
                                                    data-encuesta-id="{{ $encuesta->id }}"
                                                    required>
                                                @foreach($tiposEnvio as $key => $value)
                                                    <option value="{{ $key }}"
                                                            {{ old('encuestas.' . $loop->index . '.tipo_envio', 'manual') == $key ? 'selected' : '' }}>
                                                        {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                Define cuándo se enviará el correo
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="plantilla_{{ $encuesta->id }}">
                                                <i class="fas fa-palette"></i> Plantilla (Opcional)
                                            </label>
                                            <input type="text"
                                                   class="form-control"
                                                   id="plantilla_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][plantilla]"
                                                   value="{{ old('encuestas.' . $loop->index . '.plantilla') }}"
                                                   placeholder="Nombre de la plantilla">
                                            <small class="form-text text-muted">
                                                Nombre de la plantilla de correo a usar
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Paso 2: Configuración de Envío Programado (solo si se selecciona programado) -->
                            <div class="wizard-step configuracion-programado" data-step="2" style="display: none;">
                                <h5><i class="fas fa-calendar-alt"></i> Configuración de Envío Programado</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="fecha_envio_{{ $encuesta->id }}">
                                                <i class="fas fa-calendar"></i> Fecha de Envío
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="date"
                                                   class="form-control"
                                                   id="fecha_envio_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][fecha_envio]"
                                                   value="{{ old('encuestas.' . $loop->index . '.fecha_envio', date('Y-m-d')) }}"
                                                   min="{{ date('Y-m-d') }}">
                                            <small class="form-text text-muted">
                                                Fecha en la que se enviarán los correos
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="hora_envio_{{ $encuesta->id }}">
                                                <i class="fas fa-clock"></i> Hora de Envío
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="time"
                                                   class="form-control"
                                                   id="hora_envio_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][hora_envio]"
                                                   value="{{ old('encuestas.' . $loop->index . '.hora_envio', '09:00') }}">
                                            <small class="form-text text-muted">
                                                Hora exacta del envío
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tipo_destinatario_{{ $encuesta->id }}">
                                                <i class="fas fa-users"></i> Tipo de Destinatario
                                                <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-control tipo-destinatario-select"
                                                    id="tipo_destinatario_{{ $encuesta->id }}"
                                                    name="encuestas[{{ $loop->index }}][tipo_destinatario]"
                                                    data-encuesta-id="{{ $encuesta->id }}">
                                                <option value="">Seleccione un tipo</option>
                                                @foreach($tiposDestinatario as $key => $value)
                                                    <option value="{{ $key }}"
                                                            data-cantidad="{{ $estadisticasDestinatarios[$key] ?? 0 }}">
                                                        {{ $value }} ({{ $estadisticasDestinatarios[$key] ?? 0 }} disponibles)
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                Seleccione el tipo de destinatarios para el envío
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="numero_bloques_{{ $encuesta->id }}">
                                                <i class="fas fa-layer-group"></i> Número de Bloques
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="number"
                                                   class="form-control"
                                                   id="numero_bloques_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][numero_bloques]"
                                                   value="{{ old('encuestas.' . $loop->index . '.numero_bloques', 1) }}"
                                                   min="1"
                                                   max="10">
                                            <small class="form-text text-muted">
                                                Número de bloques en los que se dividirá el envío
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="correo_prueba_{{ $encuesta->id }}">
                                                <i class="fas fa-vial"></i> Correo de Prueba
                                            </label>
                                            <input type="email"
                                                   class="form-control"
                                                   id="correo_prueba_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][correo_prueba]"
                                                   value="{{ old('encuestas.' . $loop->index . '.correo_prueba') }}"
                                                   placeholder="correo@ejemplo.com">
                                            <small class="form-text text-muted">
                                                Correo para enviar prueba antes del envío masivo
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox"
                                                       class="custom-control-input"
                                                       id="modo_prueba_{{ $encuesta->id }}"
                                                       name="encuestas[{{ $loop->index }}][modo_prueba]"
                                                       value="1"
                                                       {{ old('encuestas.' . $loop->index . '.modo_prueba') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="modo_prueba_{{ $encuesta->id }}">
                                                    <i class="fas fa-bug"></i> Modo Debug/Prueba
                                                </label>
                                            </div>
                                            <small class="form-text text-muted">
                                                Activar para enviar solo correo de prueba
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón para enviar correo de prueba -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button"
                                                class="btn btn-warning btn-enviar-prueba"
                                                data-encuesta-id="{{ $encuesta->id }}"
                                                style="display: none;">
                                            <i class="fas fa-paper-plane"></i> Enviar Correo de Prueba
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Configuración Activa -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox"
                                                   class="custom-control-input"
                                                   id="activo_{{ $encuesta->id }}"
                                                   name="encuestas[{{ $loop->index }}][activo]"
                                                   value="1"
                                                   {{ old('encuestas.' . $loop->index . '.activo', '1') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="activo_{{ $encuesta->id }}">
                                                <i class="fas fa-toggle-on"></i> Configuración Activa
                                            </label>
                                        </div>
                                        <small class="form-text text-muted">
                                            Activa o desactiva el envío automático de correos
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos ocultos necesarios -->
                            <input type="hidden" name="encuestas[{{ $loop->index }}][encuesta_id]" value="{{ $encuesta->id }}">
                        </div>
                    </div>
                @endforeach

                <!-- Botones de acción -->
                <div class="card card-outline card-secondary">
                    <div class="card-footer">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('configuracion-envio.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Volver
                                </a>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-info" id="btn-preview">
                                    <i class="fas fa-eye"></i> Vista Previa
                                </button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Guardar Configuración
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Vista Previa -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Vista Previa de Configuración
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Contenido de vista previa -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Manejar cambio de tipo de envío
    $('.tipo-envio-select').change(function() {
        const encuestaId = $(this).data('encuesta-id');
        const tipoEnvio = $(this).val();
        const configProgramado = $(`.configuracion-programado[data-encuesta-id="${encuestaId}"]`);

        if (tipoEnvio === 'programado') {
            configProgramado.show();
            $(`.btn-enviar-prueba[data-encuesta-id="${encuestaId}"]`).show();
        } else {
            configProgramado.hide();
            $(`.btn-enviar-prueba[data-encuesta-id="${encuestaId}"]`).hide();
        }
    });

    // Calcular bloques sugeridos cuando cambia el tipo de destinatario
    $('.tipo-destinatario-select').change(function() {
        const encuestaId = $(this).data('encuesta-id');
        const selectedOption = $(this).find('option:selected');
        const cantidad = selectedOption.data('cantidad') || 0;

        if (cantidad > 0) {
            const bloquesSugeridos = calcularBloquesSugeridos(cantidad);
            $(`#numero_bloques_${encuestaId}`).val(bloquesSugeridos);
        }
    });

    // Función para calcular bloques sugeridos
    function calcularBloquesSugeridos(cantidad) {
        if (cantidad <= 50) return 1;
        const bloques = Math.max(2, Math.ceil(cantidad / 100));
        return Math.min(bloques, 10);
    }

    // Enviar correo de prueba
    $('.btn-enviar-prueba').click(function() {
        const encuestaId = $(this).data('encuesta-id');
        const correoPrueba = $(`#correo_prueba_${encuestaId}`).val();

        if (!correoPrueba) {
            alert('Debe especificar un correo de prueba');
            return;
        }

        if (!confirm('¿Está seguro de enviar un correo de prueba?')) {
            return;
        }

        // Aquí se enviaría la petición AJAX para enviar el correo de prueba
        $.post('{{ route("configuracion-envio.enviar-prueba") }}', {
            configuracion_id: encuestaId,
            correo_prueba: correoPrueba,
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                alert('Correo de prueba enviado correctamente');
            } else {
                alert('Error: ' + response.message);
            }
        })
        .fail(function() {
            alert('Error al enviar correo de prueba');
        });
    });

    // Vista previa
    $('#btn-preview').click(function() {
        // Implementar lógica de vista previa
        $('#previewModal').modal('show');
    });

    // Validación del formulario
    $('#configuracionForm').submit(function(e) {
        let isValid = true;

        // Validar campos requeridos para envío programado
        $('.tipo-envio-select').each(function() {
            const encuestaId = $(this).data('encuesta-id');
            const tipoEnvio = $(this).val();

            if (tipoEnvio === 'programado') {
                const fechaEnvio = $(`#fecha_envio_${encuestaId}`).val();
                const horaEnvio = $(`#hora_envio_${encuestaId}`).val();
                const tipoDestinatario = $(`#tipo_destinatario_${encuestaId}`).val();

                if (!fechaEnvio || !horaEnvio || !tipoDestinatario) {
                    alert('Para envío programado, debe completar fecha, hora y tipo de destinatario');
                    isValid = false;
                    return false;
                }
            }
        });

        if (!isValid) {
            e.preventDefault();
        }
    });
});
</script>
@endpush
