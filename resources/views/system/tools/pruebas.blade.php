@extends('adminlte::page')

@section('title', 'Pruebas del Sistema')

@section('content_header')
    <h1><i class="fas fa-vial"></i> Pruebas del Sistema</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Configuración de Pruebas</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('system.tools.pruebas') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo">Tipo de Prueba:</label>
                                <select class="form-control" id="tipo" name="tipo" required>
                                    <option value="">Selecciona un tipo de prueba...</option>
                                    <option value="encuestas" {{ $tipo === 'encuestas' ? 'selected' : '' }}>
                                        Prueba de Creación de Encuestas
                                    </option>
                                    <option value="preguntas" {{ $tipo === 'preguntas' ? 'selected' : '' }}>
                                        Prueba de Creación de Preguntas
                                    </option>
                                    <option value="sistema" {{ $tipo === 'sistema' ? 'selected' : '' }}>
                                        Prueba Completa del Sistema
                                    </option>
                                    <option value="fechas" {{ $tipo === 'fechas' ? 'selected' : '' }}>
                                        Diagnosticar Columnas de Fecha
                                    </option>
                                    <option value="limpiar" {{ $tipo === 'limpiar' ? 'selected' : '' }}>
                                        Limpiar Migraciones Duplicadas
                                    </option>
                                    <option value="creacion_preguntas" {{ $tipo === 'creacion_preguntas' ? 'selected' : '' }}>
                                        Diagnosticar Creación de Preguntas
                                    </option>
                                    <option value="simular_pregunta" {{ $tipo === 'simular_pregunta' ? 'selected' : '' }}>
                                        Simular Creación de Pregunta
                                    </option>
                                    <option value="verificar_bd" {{ $tipo === 'verificar_bd' ? 'selected' : '' }}>
                                        Verificar Configuración BD
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    Selecciona el tipo de prueba que deseas ejecutar.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="encuesta_id">ID de Encuesta (Para preguntas):</label>
                                <input type="number" class="form-control" id="encuesta_id" name="encuesta_id"
                                       placeholder="Opcional para pruebas de preguntas">
                                <small class="form-text text-muted">
                                    Solo necesario para pruebas de preguntas específicas.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" name="ejecutar" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Ejecutar Prueba
                                    </button>
                                    <a href="{{ route('system.tools.dashboard') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Volver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Resultado de la Prueba -->
@if($resultado)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-terminal"></i>
                    Resultado de la Prueba
                    @if($resultado['success'])
                        <span class="badge badge-success">Éxito</span>
                    @else
                        <span class="badge badge-danger">Error</span>
                    @endif
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Comando Ejecutado:</h6>
                        <code>{{ $resultado['comando'] }}</code>

                        @if(!empty($resultado['opciones']))
                        <h6 class="mt-3">Opciones:</h6>
                        <ul>
                            @foreach($resultado['opciones'] as $key => $value)
                            <li><code>{{ $key }}: {{ $value }}</code></li>
                            @endforeach
                        </ul>
                        @endif

                        <h6 class="mt-3">Código de Salida:</h6>
                        <span class="badge badge-{{ $resultado['exit_code'] === 0 ? 'success' : 'danger' }}">
                            {{ $resultado['exit_code'] }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <h6>Salida del Comando:</h6>
                        <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto; font-size: 12px;">{{ $resultado['output'] }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Tipos de Pruebas Disponibles -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Tipos de Pruebas Disponibles</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Prueba de Encuestas -->
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <h6>Prueba de Encuestas</h6>
                                <p class="card-text">Prueba la creación completa de encuestas</p>
                                <ul class="text-left small">
                                    <li>Verifica la creación de encuestas</li>
                                    <li>Valida los modelos y relaciones</li>
                                    <li>Comprueba el flujo de trabajo</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Prueba de Preguntas -->
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle fa-2x mb-2"></i>
                                <h6>Prueba de Preguntas</h6>
                                <p class="card-text">Prueba la creación de diferentes tipos de preguntas</p>
                                <ul class="text-left small">
                                    <li>Prueba múltiples tipos de preguntas</li>
                                    <li>Verifica la validación de datos</li>
                                    <li>Comprueba las relaciones</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Prueba del Sistema -->
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                <h6>Prueba Completa del Sistema</h6>
                                <p class="card-text">Prueba integral de todo el sistema</p>
                                <ul class="text-left small">
                                    <li>Prueba todos los módulos</li>
                                    <li>Verifica la integración</li>
                                    <li>Comprueba el rendimiento</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-info">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                <h6>Diagnóstico de Fechas</h6>
                                <p class="card-text">Diagnostica problemas con columnas de fecha</p>
                                <ul class="text-left small">
                                    <li>Verifica columnas fecha_inicio/fin</li>
                                    <li>Comprueba tipos de datos</li>
                                    <li>Identifica problemas de estructura</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-danger">
                            <div class="card-body text-center">
                                <i class="fas fa-broom fa-2x mb-2"></i>
                                <h6>Limpiar Migraciones</h6>
                                <p class="card-text">Limpia migraciones duplicadas</p>
                                <ul class="text-left small">
                                    <li>Elimina migraciones duplicadas</li>
                                    <li>Ejecuta migración consolidada</li>
                                    <li>Optimiza estructura de BD</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-purple">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle fa-2x mb-2"></i>
                                <h6>Diagnóstico Preguntas</h6>
                                <p class="card-text">Diagnostica problemas de creación</p>
                                <ul class="text-left small">
                                    <li>Verifica estructura de tabla</li>
                                    <li>Comprueba modelo y métodos</li>
                                    <li>Prueba creación de preguntas</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-teal">
                            <div class="card-body text-center">
                                <i class="fas fa-play-circle fa-2x mb-2"></i>
                                <h6>Simular Pregunta</h6>
                                <p class="card-text">Simula creación completa</p>
                                <ul class="text-left small">
                                    <li>Simula datos del request</li>
                                    <li>Prueba validación y creación</li>
                                    <li>Muestra errores detallados</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-orange">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-2x mb-2"></i>
                                <h6>Verificar BD</h6>
                                <p class="card-text">Verifica configuración</p>
                                <ul class="text-left small">
                                    <li>Verifica conexión a BD</li>
                                    <li>Corrige configuración</li>
                                    <li>Diagnostica problemas</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información de las Pruebas -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información de las Pruebas</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-exclamation-triangle"></i> ¿Qué hacen estas pruebas?</h6>
                    <ul class="mb-0">
                        <li><strong>Prueba de Encuestas:</strong> Simula la creación completa de encuestas y verifica que todo funcione correctamente.</li>
                        <li><strong>Prueba de Preguntas:</strong> Crea diferentes tipos de preguntas para verificar que la funcionalidad esté operativa.</li>
                        <li><strong>Prueba del Sistema:</strong> Ejecuta una batería completa de pruebas para verificar la integridad del sistema.</li>
                        <li><strong>Diagnóstico de Fechas:</strong> Verifica y diagnostica problemas con las columnas de fecha en la tabla encuestas.</li>
                        <li><strong>Limpiar Migraciones:</strong> Elimina migraciones duplicadas y ejecuta la migración consolidada del sistema de encuestas.</li>
                        <li><strong>Diagnóstico de Preguntas:</strong> Verifica y diagnostica problemas específicos en la creación de preguntas, incluyendo estructura de tabla, modelo y métodos.</li>
                        <li><strong>Simulación de Pregunta:</strong> Simula el proceso completo de creación de una pregunta, mostrando cada paso y posibles errores.</li>
                        <li><strong>Verificación de BD:</strong> Verifica y corrige la configuración de la base de datos para desarrollo local.</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-shield-alt"></i> Recomendaciones:</h6>
                    <ul class="mb-0">
                        <li>Ejecuta las pruebas después de hacer cambios importantes en el sistema.</li>
                        <li>Las pruebas pueden crear datos de prueba en la base de datos.</li>
                        <li>Si una prueba falla, revisa los logs para identificar el problema.</li>
                        <li>Ejecuta las pruebas en un entorno de desarrollo antes que en producción.</li>
                    </ul>
                </div>

                <div class="alert alert-success">
                    <h6><i class="fas fa-lightbulb"></i> Interpretación de Resultados:</h6>
                    <ul class="mb-0">
                        <li><strong>Éxito (Código 0):</strong> La prueba se ejecutó correctamente sin errores.</li>
                        <li><strong>Error (Código 1):</strong> Hubo un problema durante la ejecución de la prueba.</li>
                        <li><strong>Salida detallada:</strong> Revisa la salida para ver exactamente qué se probó y los resultados.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
pre {
    white-space: pre-wrap;
    word-wrap: break-word;
}
.card-body .card {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.card-body .card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s;
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-scroll al resultado si existe
    if ($('.card:contains("Resultado de la Prueba")').length) {
        $('html, body').animate({
            scrollTop: $('.card:contains("Resultado de la Prueba")').offset().top - 100
        }, 500);
    }

    // Mostrar/ocultar campo encuesta_id según el tipo seleccionado
    $('#tipo').change(function() {
        if ($(this).val() === 'preguntas') {
            $('#encuesta_id').closest('.form-group').show();
        } else {
            $('#encuesta_id').closest('.form-group').hide();
        }
    });

    // Ejecutar al cargar la página
    $('#tipo').trigger('change');
});
</script>
@endsection
