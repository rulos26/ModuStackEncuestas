@extends('adminlte::page')

@section('title', 'Configurar Respuestas - Pregunta ' . ($preguntaIndex + 1))

@section('css')
<style>
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
</style>
@endsection

@section('js')
<script>
// Definir funciones globales inmediatamente en el head
window.verificarBotones = function() {
    var btnAgregar = document.getElementById('btnAgregarRespuesta');
    var container = document.getElementById('respuestasContainer');

    var mensaje = 'Verificación de elementos:\n';
    mensaje += 'Botón agregar: ' + (btnAgregar ? 'ENCONTRADO' : 'NO ENCONTRADO') + '\n';
    mensaje += 'Contenedor: ' + (container ? 'ENCONTRADO' : 'NO ENCONTRADO') + '\n';
    mensaje += 'Total respuestas actuales: ' + document.querySelectorAll('.respuesta-item').length;

    alert(mensaje);
};

window.agregarRespuestaDirecto = function() {
    console.log('Función agregarRespuestaDirecto llamada');

    var container = document.getElementById('respuestasContainer');
    var contadorElement = document.getElementById('numRespuestas');

    if (!container) {
        alert('Error: Contenedor no encontrado');
        return;
    }

    var items = document.querySelectorAll('.respuesta-item');
    var contador = items.length;

    var html = '<div class="row mb-2 respuesta-item">';
    html += '<div class="col-md-8">';
    html += '<input type="text" name="respuestas[' + contador + '][texto]" class="form-control" placeholder="Escribe la opción de respuesta..." required>';
    html += '</div>';
    html += '<div class="col-md-2">';
    html += '<input type="number" name="respuestas[' + contador + '][orden]" class="form-control" placeholder="Orden" value="' + (contador + 1) + '" min="1" required>';
    html += '</div>';
    html += '<div class="col-md-2">';
    html += '<button type="button" class="btn btn-danger btn-block btn-remove-respuesta" onclick="eliminarRespuestaDirecto(this)">';
    html += '<i class="fas fa-trash"></i> Eliminar';
    html += '</button>';
    html += '</div>';
    html += '</div>';

    container.insertAdjacentHTML('beforeend', html);

    // Actualizar contador visual
    var newItems = document.querySelectorAll('.respuesta-item');
    contadorElement.textContent = newItems.length;

    console.log('Respuesta agregada. Total:', newItems.length);
    alert('¡Respuesta agregada!');
};

window.eliminarRespuestaDirecto = function(elemento) {
    var item = elemento.closest('.respuesta-item');
    item.remove();

    var contadorElement = document.getElementById('numRespuestas');
    var items = document.querySelectorAll('.respuesta-item');
    contadorElement.textContent = items.length;

    console.log('Respuesta eliminada. Total:', items.length);
};

console.log('=== FUNCIONES GLOBALES DEFINIDAS EN HEAD ===');
</script>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- PROGRESO DEL WIZARD -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                             style="width: {{ (($preguntaIndex + 1) / $totalPreguntas) * 100 }}%;"
                             aria-valuenow="{{ $preguntaIndex + 1 }}" aria-valuemin="0" aria-valuemax="{{ $totalPreguntas }}">
                            <strong><i class="fas fa-cogs"></i> Pregunta {{ $preguntaIndex + 1 }} de {{ $totalPreguntas }}</strong>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Progreso: {{ round((($preguntaIndex + 1) / $totalPreguntas) * 100) }}% completado
                        </small>
                    </div>
                </div>
            </div>

            <!-- INFORMACIÓN DE LA ENCUESTA -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-poll"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Encuesta</span>
                            <span class="info-box-number">{{ $encuesta->titulo }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                ID: {{ $encuesta->id }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pregunta Actual</span>
                            <span class="info-box-number">{{ $preguntaIndex + 1 }} / {{ $totalPreguntas }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $preguntaActual->tipo === 'seleccion_unica' ? 'Selección Única' : ($preguntaActual->tipo === 'casillas_verificacion' ? 'Casillas de Verificación' : 'Selección Múltiple') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-cogs"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Respuestas Configuradas</span>
                            <span class="info-box-number">{{ Session::get('wizard_respuestas_count', 0) }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                En esta sesión
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-primary">
                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Sesión Activa</span>
                            <span class="info-box-number">{{ $encuesta->empresa->nombre ?? 'Sin empresa' }}</span>
                            <div class="progress">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                {{ $encuesta->estado }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORMULARIO PRINCIPAL -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Configurar Respuestas para Pregunta
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-light">
                            <i class="fas fa-info-circle"></i> Uso Administrativo
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- INFORMACIÓN DE LA PREGUNTA -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-question-circle"></i> Pregunta a Configurar:</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <p class="mb-1"><strong>{{ $preguntaActual->pregunta }}</strong></p>
                                @if($preguntaActual->descripcion)
                                    <p class="mb-0 text-muted"><small>{{ $preguntaActual->descripcion }}</small></p>
                                @endif
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="badge badge-{{ $preguntaActual->tipo === 'seleccion_unica' ? 'primary' : ($preguntaActual->tipo === 'casillas_verificacion' ? 'success' : 'info') }}">
                                    <i class="fas fa-{{ $preguntaActual->tipo === 'seleccion_unica' ? 'dot-circle' : ($preguntaActual->tipo === 'casillas_verificacion' ? 'check-square' : 'list-check') }}"></i>
                                    @if($preguntaActual->tipo === 'seleccion_unica')
                                        Selección Única
                                    @elseif($preguntaActual->tipo === 'casillas_verificacion')
                                        Casillas de Verificación
                                    @elseif($preguntaActual->tipo === 'seleccion_multiple')
                                        Selección Múltiple
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- FORMULARIO DE RESPUESTAS -->
                    <form action="{{ route('respuestas.wizard.store') }}" method="POST" id="respuestasForm">
                        @csrf
                        <input type="hidden" id="pregunta_id" name="pregunta_id" value="{{ $preguntaActual->id }}">

                        <div class="form-group">
                            <h5><i class="fas fa-list"></i> Opciones de Respuesta <span class="text-danger">*</span></h5>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Importante:</strong> Agrega las opciones de respuesta que estarán disponibles para los usuarios.
                                @if($preguntaActual->tipo === 'seleccion_unica')
                                    Los usuarios podrán seleccionar <strong>una sola opción</strong>.
                                @elseif($preguntaActual->tipo === 'casillas_verificacion')
                                    Los usuarios podrán seleccionar <strong>múltiples opciones</strong>.
                                @elseif($preguntaActual->tipo === 'seleccion_multiple')
                                    Los usuarios podrán seleccionar <strong>múltiples opciones</strong>.
                                @endif
                            </div>

                            <div id="respuestasContainer">
                                <div class="row mb-2 respuesta-item">
                                    <div class="col-md-8">
                                        <input type="text" id="respuesta-texto-0" name="respuestas[0][texto]" class="form-control"
                                               placeholder="Escribe la opción de respuesta..." required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" id="respuesta-orden-0" name="respuestas[0][orden]" class="form-control"
                                               placeholder="Orden" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" id="btn-remove-0" class="btn btn-danger btn-block btn-remove-respuesta" onclick="eliminarRespuestaDirecto(this)" disabled>
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-success" id="btnAgregarRespuesta" onclick="agregarRespuestaDirecto()">
                                        <i class="fas fa-plus"></i> Agregar Otra Opción
                                    </button>
                                    <span class="badge badge-info" id="contadorRespuestas">
                                        <i class="fas fa-list"></i> <span id="numRespuestas">1</span> opción(es)
                                    </span>
                                </div>
                                <!-- Botón de prueba -->
                                <div class="mt-2">
                                    <button type="button" class="btn btn-warning btn-sm" onclick="alert('JavaScript funciona!')">
                                        <i class="fas fa-check"></i> Probar JavaScript
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" onclick="verificarBotones()">
                                        <i class="fas fa-search"></i> Verificar Botones
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- BOTONES DE ACCIÓN -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="{{ route('respuestas.wizard.cancel') }}" class="btn btn-outline-danger btn-lg btn-block">
                                    <i class="fas fa-times"></i> Cancelar Wizard
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" id="btnGuardar" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-save"></i> Guardar Respuestas y Continuar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Código adicional para debugging
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== WIZARD DE RESPUESTAS INICIADO ===');
    console.log('Funciones disponibles:', {
        verificarBotones: typeof window.verificarBotones,
        agregarRespuestaDirecto: typeof window.agregarRespuestaDirecto,
        eliminarRespuestaDirecto: typeof window.eliminarRespuestaDirecto
    });
    console.log('=== WIZARD LISTO ===');
});
</script>
@endsection
