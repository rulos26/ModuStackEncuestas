@extends('adminlte::page')

@section('title', 'Configuración de Envío')

@section('content_header')
    <h1>
        <i class="fas fa-paper-plane"></i> Configuración de Envío
        <small>{{ $encuesta->titulo }}</small>
    </h1>
@endsection

@section('content')
    <!-- BREADCRUMBS DE NAVEGACIÓN -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('encuestas.index') }}">Encuestas</a></li>
            <li class="breadcrumb-item"><a href="{{ route('encuestas.show', $encuesta->id) }}">{{ $encuesta->titulo }}</a></li>
            <li class="breadcrumb-item active">Configuración de Envío</li>
        </ol>
    </nav>

    <!-- PROGRESO DEL FLUJO -->
    <div class="progress mb-4" style="height: 25px;">
        <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
            <strong>1. Crear Encuesta ✓</strong>
        </div>
        <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
            <strong>2. Agregar Preguntas ✓</strong>
        </div>
        <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
            <strong>3. Configurar Respuestas ✓</strong>
        </div>
        <div class="progress-bar bg-success" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
            <strong>4. Configurar Lógica ✓</strong>
        </div>
        <div class="progress-bar bg-primary" role="progressbar" style="width: 20%;" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
            <strong>5. Configurar Envío</strong>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-check"></i> ¡Éxito!</h5>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            <h5><i class="icon fas fa-ban"></i> ¡Error!</h5>
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- CONFIGURACIÓN DE ENVÍO -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users"></i> Configuración de Correos Masivos
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('encuestas.envio.store', $encuesta->id) }}" method="POST" id="envioForm">
                        @csrf

                        <!-- ESTADÍSTICAS -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Empleados</span>
                                        <span class="info-box-number">{{ $totalEmpleados }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-user-friends"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Usuarios</span>
                                        <span class="info-box-number">{{ $totalUsuarios }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-envelope"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Encuestas</span>
                                        <span class="info-box-number">{{ $encuestasDisponibles }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary"><i class="fas fa-check-circle"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Disponibles</span>
                                        <span class="info-box-number">{{ $totalDisponibles }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DESTINATARIOS -->
                        <div class="form-group">
                            <label for="destinatarios">
                                <i class="fas fa-user-plus"></i> Seleccionar Destinatarios
                                <small class="text-muted">(Máximo {{ $encuestasDisponibles }} destinatarios)</small>
                            </label>

                            <!-- EMPLEADOS -->
                            @if($empleados->isNotEmpty())
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-briefcase"></i> Empleados Registrados
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($empleados as $empleado)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               name="destinatarios[]"
                                                               value="{{ $empleado->correo_electronico }}"
                                                               class="form-check-input destinatario-checkbox"
                                                               id="empleado_{{ $empleado->id }}">
                                                        <label class="form-check-label" for="empleado_{{ $empleado->id }}">
                                                            <strong>{{ $empleado->nombre }}</strong><br>
                                                            <small class="text-muted">
                                                                {{ $empleado->correo_electronico }}
                                                                @if($empleado->empresa)
                                                                    <br><span class="text-info">{{ $empleado->empresa->nombre }}</span>
                                                                @endif
                                                            </small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- USUARIOS DEL SISTEMA -->
                            @if($usuarios->isNotEmpty())
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user-cog"></i> Usuarios del Sistema
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($usuarios as $usuario)
                                                <div class="col-md-6 mb-2">
                                                    <div class="form-check">
                                                        <input type="checkbox"
                                                               name="destinatarios[]"
                                                               value="{{ $usuario->email }}"
                                                               class="form-check-input destinatario-checkbox"
                                                               id="usuario_{{ $usuario->id }}">
                                                        <label class="form-check-label" for="usuario_{{ $usuario->id }}">
                                                            <strong>{{ $usuario->name }}</strong><br>
                                                            <small class="text-muted">{{ $usuario->email }}</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @error('destinatarios')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- AGREGAR USUARIO MANUALMENTE -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-plus-circle"></i> Agregar Usuario Manualmente
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <input type="text" id="nuevoNombre" class="form-control" placeholder="Nombre completo">
                                    </div>
                                    <div class="col-md-4">
                                        <input type="email" id="nuevoEmail" class="form-control" placeholder="Correo electrónico">
                                    </div>

                                    <div class="col-md-1">
                                        <button type="button" id="btnAgregarUsuario" class="btn btn-success">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CONFIGURACIÓN DE ENVÍO -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fecha_envio">
                                        <i class="fas fa-calendar"></i> Fecha de Envío
                                    </label>
                                    <input type="date" name="fecha_envio" id="fecha_envio"
                                           class="form-control @error('fecha_envio') is-invalid @enderror"
                                           value="{{ old('fecha_envio', date('Y-m-d', strtotime('+1 day'))) }}" required>
                                    @error('fecha_envio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hora_envio">
                                        <i class="fas fa-clock"></i> Hora de Envío
                                    </label>
                                    <input type="time" name="hora_envio" id="hora_envio"
                                           class="form-control @error('hora_envio') is-invalid @enderror"
                                           value="{{ old('hora_envio', '09:00') }}" required>
                                    @error('hora_envio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="asunto_correo">
                                <i class="fas fa-tag"></i> Asunto del Correo
                            </label>
                            <input type="text" name="asunto_correo" id="asunto_correo"
                                   class="form-control @error('asunto_correo') is-invalid @enderror"
                                   value="{{ old('asunto_correo', 'Invitación a participar en encuesta: ' . $encuesta->titulo) }}"
                                   maxlength="255" required>
                            @error('asunto_correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="plantilla_correo">
                                <i class="fas fa-envelope-open-text"></i> Plantilla de Correo
                                <small class="text-muted">(Opcional - Variables disponibles: {NOMBRE_ENCUESTA}, {URL_ENCUESTA}, {TOKEN})</small>
                            </label>
                            <textarea name="plantilla_correo" id="plantilla_correo" rows="8"
                                      class="form-control @error('plantilla_correo') is-invalid @enderror"
                                      placeholder="Plantilla HTML personalizada...">{{ old('plantilla_correo') }}</textarea>
                            @error('plantilla_correo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="enviar_ahora" id="enviar_ahora"
                                       class="form-check-input" value="1">
                                <label class="form-check-label" for="enviar_ahora">
                                    <i class="fas fa-paper-plane"></i> Enviar ahora (ignorar fecha y hora programadas)
                                </label>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Configurar y Enviar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- PANEL DE AYUDA -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </h3>
                </div>
                <div class="card-body">
                    <h5><i class="fas fa-info-circle"></i> Información Importante</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> <strong>Empleados:</strong> Usuarios registrados en el sistema de empleados</li>
                        <li><i class="fas fa-check text-success"></i> <strong>Usuarios:</strong> Usuarios del sistema con acceso</li>
                        <li><i class="fas fa-check text-success"></i> <strong>Encuestas:</strong> Número máximo de encuestas a enviar</li>
                        <li><i class="fas fa-check text-success"></i> <strong>Disponibles:</strong> Total de destinatarios disponibles</li>
                    </ul>

                    <h5><i class="fas fa-cog"></i> Variables de Plantilla</h5>
                    <ul class="list-unstyled">
                        <li><code>{NOMBRE_ENCUESTA}</code> - Nombre de la encuesta</li>
                        <li><code>{URL_ENCUESTA}</code> - Enlace directo a la encuesta</li>
                        <li><code>{TOKEN}</code> - Token único de acceso</li>
                    </ul>

                    <h5><i class="fas fa-exclamation-triangle text-warning"></i> Notas</h5>
                    <ul class="list-unstyled">
                        <li>• Los correos se enviarán con tokens únicos</li>
                        <li>• Cada destinatario recibirá un enlace personalizado</li>
                        <li>• Se puede agregar usuarios manualmente si es necesario</li>
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

    // Contador de destinatarios seleccionados
    function actualizarContador() {
        var seleccionados = $('.destinatario-checkbox:checked').length;
        var maximo = {{ $encuestasDisponibles }};

        if (seleccionados > maximo) {
            alert('No puede seleccionar más de ' + maximo + ' destinatarios.');
            $(this).prop('checked', false);
            return;
        }

        $('.info-box-number').last().text(seleccionados + '/' + maximo);
    }

    $('.destinatario-checkbox').on('change', actualizarContador);

    // Agregar usuario manualmente
    $('#btnAgregarUsuario').click(function() {
        var nombre = $('#nuevoNombre').val();
        var email = $('#nuevoEmail').val();
        var cargo = $('#nuevoCargo').val();

        if (!nombre || !email) {
            alert('Por favor complete el nombre y correo electrónico.');
            return;
        }

        $.ajax({
            url: '{{ route("encuestas.envio.agregar-usuario", $encuesta->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                nombre: nombre,
                email: email,
                cargo: cargo
            },
            success: function(response) {
                if (response.success) {
                    // Agregar checkbox automáticamente
                    var nuevoCheckbox = `
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input type="checkbox" name="destinatarios[]" value="${email}"
                                       class="form-check-input destinatario-checkbox" checked>
                                <label class="form-check-label">
                                    <strong>${nombre}</strong><br>
                                    <small class="text-muted">${email} - ${cargo || 'Usuario agregado'}</small>
                                </label>
                            </div>
                        </div>
                    `;

                    $('.card-body .row').first().append(nuevoCheckbox);

                    // Limpiar campos
                    $('#nuevoNombre, #nuevoEmail, #nuevoCargo').val('');

                    alert('Usuario agregado correctamente.');
                    actualizarContador();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al agregar usuario.');
            }
        });
    });

    // Validación del formulario
    $('#envioForm').on('submit', function(e) {
        var seleccionados = $('.destinatario-checkbox:checked').length;

        if (seleccionados === 0) {
            e.preventDefault();
            alert('Debe seleccionar al menos un destinatario.');
            return false;
        }

        if (seleccionados > {{ $encuestasDisponibles }}) {
            e.preventDefault();
            alert('No puede seleccionar más de {{ $encuestasDisponibles }} destinatarios.');
            return false;
        }
    });

    // Inicializar contador
    actualizarContador();
});
</script>
@endsection
