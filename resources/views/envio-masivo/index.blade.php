@extends('adminlte::page')

@section('title', 'Envío Masivo de Encuestas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Envío Masivo de Encuestas
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('envio-masivo.estadisticas') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Estadísticas
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error</h5>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <form action="{{ route('envio-masivo.enviar') }}" method="POST" id="form-envio">
                                @csrf

                                <div class="form-group">
                                    <label for="encuesta_id">
                                        <i class="fas fa-poll mr-1"></i>
                                        Seleccionar Encuesta
                                    </label>
                                    <select name="encuesta_id" id="encuesta_id" class="form-control @error('encuesta_id') is-invalid @enderror" required>
                                        <option value="">-- Selecciona una encuesta --</option>
                                        @foreach($encuestas as $encuesta)
                                            <option value="{{ $encuesta->id }}" {{ old('encuesta_id') == $encuesta->id ? 'selected' : '' }}>
                                                {{ $encuesta->titulo }}
                                                ({{ $encuesta->empresa ? $encuesta->empresa->nombre : 'Sin empresa' }})
                                                - {{ ucfirst($encuesta->estado) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('encuesta_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Solo se muestran encuestas publicadas o enviadas
                                    </small>
                                </div>

                                <!-- Información de la encuesta seleccionada -->
                                <div id="info-encuesta" class="alert alert-info" style="display: none;">
                                    <h6><i class="fas fa-info-circle mr-1"></i>Información de la Encuesta</h6>
                                    <div id="detalles-encuesta"></div>
                                </div>

                                <!-- Información de empleados -->
                                <div id="info-empleados" class="alert alert-success" style="display: none;">
                                    <h6><i class="fas fa-users mr-1"></i>Empleados Destinatarios</h6>
                                    <div id="detalles-empleados"></div>
                                </div>

                                <!-- Vista previa del correo -->
                                <div id="vista-previa" class="alert alert-warning" style="display: none;">
                                    <h6><i class="fas fa-eye mr-1"></i>Vista Previa del Correo</h6>
                                    <div id="contenido-previa"></div>
                                    <a href="#" id="btn-vista-previa" class="btn btn-sm btn-outline-warning mt-2">
                                        <i class="fas fa-eye mr-1"></i>
                                        Ver Vista Previa Completa
                                    </a>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary" id="btn-enviar">
                                        <i class="fas fa-paper-plane mr-1"></i>
                                        Enviar Encuesta
                                    </button>
                                    <a href="{{ route('home') }}" class="btn btn-secondary ml-2">
                                        <i class="fas fa-arrow-left mr-1"></i>
                                        Volver
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Información del Módulo
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <h6><i class="fas fa-question-circle mr-1"></i> ¿Cómo funciona?</h6>
                                    <ol class="pl-3">
                                        <li>Selecciona una encuesta publicada</li>
                                        <li>El sistema obtiene automáticamente los empleados de la empresa</li>
                                        <li>Se genera un link público único para la encuesta</li>
                                        <li>Se envían correos a todos los empleados con emails válidos</li>
                                        <li>Se muestra un resumen del envío</li>
                                    </ol>

                                    <h6><i class="fas fa-exclamation-triangle mr-1"></i> Requisitos</h6>
                                    <ul class="pl-3">
                                        <li>La encuesta debe estar <strong>publicada</strong></li>
                                        <li>Debe estar asociada a una empresa</li>
                                        <li>Los empleados deben tener emails válidos</li>
                                        <li>La configuración SMTP debe estar correcta</li>
                                    </ul>

                                    <h6><i class="fas fa-cog mr-1"></i> Configuración</h6>
                                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-validar-config">
                                        <i class="fas fa-check mr-1"></i>
                                        Validar Configuración SMTP
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Cargar información cuando se selecciona una encuesta
    $('#encuesta_id').on('change', function() {
        const encuestaId = $(this).val();

        if (encuestaId) {
            cargarInformacionEncuesta(encuestaId);
        } else {
            ocultarInformacion();
        }
    });

    // Validar configuración SMTP
    $('#btn-validar-config').on('click', function() {
        validarConfiguracion();
    });

    // Vista previa
    $('#btn-vista-previa').on('click', function(e) {
        e.preventDefault();
        const encuestaId = $('#encuesta_id').val();
        if (encuestaId) {
            window.open(`{{ route('envio-masivo.vista-previa') }}?encuesta_id=${encuestaId}`, '_blank');
        }
    });

    // Confirmar envío
    $('#form-envio').on('submit', function(e) {
        const encuestaId = $('#encuesta_id').val();

        if (!encuestaId) {
            alert('Por favor selecciona una encuesta.');
            e.preventDefault();
            return false;
        }

        return confirm('¿Estás seguro de que deseas enviar esta encuesta a todos los empleados? Esta acción no se puede deshacer.');
    });
});

function cargarInformacionEncuesta(encuestaId) {
    $.ajax({
        url: '{{ route("envio-masivo.obtener-empleados") }}',
        method: 'POST',
        data: {
            encuesta_id: encuestaId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            mostrarInformacion(response);
        },
        error: function() {
            alert('Error cargando información de la encuesta.');
        }
    });
}

function mostrarInformacion(data) {
    // Mostrar información de la encuesta
    $('#detalles-encuesta').html(`
        <strong>Empresa:</strong> ${data.empresa}<br>
        <strong>Estado:</strong> ${data.estado}<br>
        <strong>Preguntas:</strong> ${data.preguntas_count}
    `);
    $('#info-encuesta').show();

    // Mostrar información de empleados
    $('#detalles-empleados').html(`
        <strong>Total empleados:</strong> ${data.empleados.total}<br>
        <strong>Con email válido:</strong> ${data.empleados_con_email}<br>
        <strong>Destinatarios:</strong> ${data.destinatarios}
    `);
    $('#info-empleados').show();

    // Mostrar vista previa
    $('#contenido-previa').html(`
        <strong>Asunto:</strong> ${data.asunto}<br>
        <strong>Link:</strong> ${data.link}<br>
        <strong>Empleados:</strong> ${data.destinatarios} recibirán el correo
    `);
    $('#vista-previa').show();
}

function ocultarInformacion() {
    $('#info-encuesta').hide();
    $('#info-empleados').hide();
    $('#vista-previa').hide();
}

function validarConfiguracion() {
    $.get('{{ route("envio-masivo.validar-configuracion") }}', function(response) {
        let mensaje = '<h6>Configuración SMTP:</h6><ul>';

        if (response.valido) {
            mensaje += '<li class="text-success">✅ Configuración válida</li>';
        } else {
            response.errores.forEach(function(error) {
                mensaje += `<li class="text-danger">❌ ${error}</li>`;
            });
        }

        if (response.advertencias.length > 0) {
            response.advertencias.forEach(function(advertencia) {
                mensaje += `<li class="text-warning">⚠️ ${advertencia}</li>`;
            });
        }

        mensaje += '</ul>';

        Swal.fire({
            title: 'Validación de Configuración',
            html: mensaje,
            icon: response.valido ? 'success' : 'warning',
            confirmButtonText: 'Entendido'
        });
    });
}
</script>
@endpush
