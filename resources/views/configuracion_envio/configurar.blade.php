@extends('adminlte::page')

@section('title', 'Configurar Envío de Correos')

@section('content_header')
    <h1>
        <i class="fas fa-cog"></i> Configurar Envío de Correos
        <small>Paso 3: Configuración</small>
    </h1>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Información de la empresa -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building"></i> Empresa: {{ $empresa->nombre ?? 'Empresa' }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Encuestas seleccionadas:</strong> {{ $encuestas->count() }}
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha de configuración:</strong> {{ now()->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de configuración -->
            <form id="configuracion-form" method="POST" action="{{ route('configuracion-envio.store') }}">
                @csrf
                <input type="hidden" name="empresa_id" value="{{ $empresa->id ?? '' }}">

                @foreach($encuestas as $encuesta)
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-poll"></i> {{ $encuesta->titulo }}
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-primary">{{ $encuesta->estado }}</span>
                            </div>
                        </div>
                        <div class="card-body">
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
                                               value="{{ old('encuestas.' . $loop->index . '.nombre_remitente', $empresa->nombre ?? '') }}"
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
                                               value="{{ old('encuestas.' . $loop->index . '.correo_remitente', $empresa->correo_electronico ?? '') }}"
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
                                            Asunto que aparecerá en el correo electrónico
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
                                        <select class="form-control"
                                                id="tipo_envio_{{ $encuesta->id }}"
                                                name="encuestas[{{ $loop->index }}][tipo_envio]"
                                                required>
                                            @foreach($tiposEnvio as $key => $value)
                                                <option value="{{ $key }}"
                                                        {{ old('encuestas.' . $loop->index . '.tipo_envio', 'automatico') == $key ? 'selected' : '' }}>
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
                    <i class="fas fa-eye"></i> Vista Previa del Correo
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="preview-content">
                    <!-- El contenido de la vista previa se cargará aquí -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<style>
.custom-switch .custom-control-label::before {
    width: 2rem;
    height: 1rem;
    border-radius: 1rem;
}

.custom-switch .custom-control-label::after {
    width: calc(1rem - 4px);
    height: calc(1rem - 4px);
    border-radius: calc(1rem - 4px);
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(1rem);
}

.form-group label {
    font-weight: 600;
    color: #495057;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.card-outline.card-primary {
    border-top: 3px solid #007bff;
}

.card-outline.card-info {
    border-top: 3px solid #17a2b8;
}

.card-outline.card-secondary {
    border-top: 3px solid #6c757d;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('configuracion-form');
    const btnPreview = document.getElementById('btn-preview');
    const previewModal = document.getElementById('previewModal');

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (validateForm()) {
            submitForm();
        }
    });

    // Vista previa
    btnPreview.addEventListener('click', function() {
        showPreview();
    });

    function validateForm() {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Validar emails
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            const email = field.value.trim();
            if (email && !isValidEmail(email)) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });

        if (!isValid) {
            showError('Por favor, complete todos los campos requeridos correctamente.');
        }

        return isValid;
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function submitForm() {
        const formData = new FormData(form);

        // Mostrar loading
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccess('Configuración guardada exitosamente');
                setTimeout(() => {
                    window.location.href = '{{ route("configuracion-envio.resumen") }}?empresa_id={{ $empresa->id }}';
                }, 2000);
            } else {
                showError(data.message || 'Error al guardar la configuración');
            }
        })
        .catch(error => {
            showError('Error de conexión: ' + error.message);
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    function showPreview() {
        const previewContent = document.getElementById('preview-content');
        let previewHtml = '<div class="row">';

        // Obtener datos del primer formulario como ejemplo
        const firstEncuesta = document.querySelector('[id^="nombre_remitente_"]');
        if (firstEncuesta) {
            const encuestaId = firstEncuesta.id.split('_')[2];
            const nombreRemitente = document.getElementById(`nombre_remitente_${encuestaId}`).value;
            const correoRemitente = document.getElementById(`correo_remitente_${encuestaId}`).value;
            const asunto = document.getElementById(`asunto_${encuestaId}`).value;
            const cuerpoMensaje = document.getElementById(`cuerpo_mensaje_${encuestaId}`).value;

            previewHtml += `
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h6><strong>De:</strong> ${nombreRemitente} &lt;${correoRemitente}&gt;</h6>
                            <h6><strong>Asunto:</strong> ${asunto}</h6>
                        </div>
                        <div class="card-body">
                            <pre style="white-space: pre-wrap; font-family: inherit;">${cuerpoMensaje}</pre>
                        </div>
                    </div>
                </div>
            `;
        }

        previewHtml += '</div>';
        previewContent.innerHTML = previewHtml;

        $(previewModal).modal('show');
    }

    function showSuccess(message) {
        // Usar SweetAlert si está disponible, sino alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: message,
                timer: 3000
            });
        } else {
            alert('Éxito: ' + message);
        }
    }

    function showError(message) {
        // Usar SweetAlert si está disponible, sino alert
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        } else {
            alert('Error: ' + message);
        }
    }
});
</script>
@endsection
