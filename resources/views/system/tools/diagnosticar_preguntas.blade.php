@extends('adminlte::page')

@section('title', 'Diagnóstico de Preguntas')

@section('content_header')
    <h1><i class="fas fa-question-circle"></i> Diagnóstico de Preguntas</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Configuración del Diagnóstico</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('system.tools.diagnosticar-preguntas') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="encuesta_id">ID de Encuesta (Opcional):</label>
                                <input type="number" class="form-control" id="encuesta_id" name="encuesta_id"
                                       value="{{ $encuestaId }}" placeholder="Dejar vacío para diagnóstico general">
                                <small class="form-text text-muted">
                                    Si especificas un ID, el diagnóstico se enfocará en esa encuesta específica.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="crear_prueba" name="crear_prueba"
                                           {{ $crearPrueba ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="crear_prueba">
                                        Crear pregunta de prueba
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Crea una pregunta de prueba para verificar el funcionamiento.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" name="ejecutar" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Ejecutar Diagnóstico
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

<!-- Resultado del Diagnóstico -->
@if($resultado)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-terminal"></i>
                    Resultado del Diagnóstico
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

<!-- Información del Diagnóstico -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información del Diagnóstico</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-exclamation-triangle"></i> ¿Qué hace este diagnóstico?</h6>
                    <ul class="mb-0">
                        <li><strong>Verificación de Tabla:</strong> Comprueba que la tabla 'preguntas' existe y tiene la estructura correcta.</li>
                        <li><strong>Verificación de Modelo:</strong> Valida que el modelo Pregunta esté correctamente definido.</li>
                        <li><strong>Verificación de Métodos:</strong> Comprueba que todos los métodos necesarios estén disponibles.</li>
                        <li><strong>Verificación de Relaciones:</strong> Valida las relaciones con otros modelos.</li>
                        <li><strong>Prueba de Creación:</strong> Si está habilitado, crea una pregunta de prueba.</li>
                        <li><strong>Diagnóstico Específico:</strong> Si se especifica un ID, analiza esa encuesta en particular.</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-shield-alt"></i> Recomendaciones:</h6>
                    <ul class="mb-0">
                        <li>Ejecuta este diagnóstico cuando tengas problemas con las preguntas.</li>
                        <li>Si hay errores en la estructura de la tabla, ejecuta la migración de preguntas.</li>
                        <li>La opción "Crear pregunta de prueba" es útil para verificar el funcionamiento.</li>
                        <li>Revisa los logs del sistema si hay errores persistentes.</li>
                    </ul>
                </div>

                <div class="alert alert-success">
                    <h6><i class="fas fa-lightbulb"></i> Soluciones Comunes:</h6>
                    <ul class="mb-0">
                        <li><strong>Tabla no existe:</strong> Ejecuta las migraciones desde el menú de migraciones.</li>
                        <li><strong>Estructura incorrecta:</strong> Ejecuta la migración específica de preguntas.</li>
                        <li><strong>Modelo no encontrado:</strong> Verifica que el archivo del modelo exista.</li>
                        <li><strong>Métodos faltantes:</strong> Revisa la definición del modelo Pregunta.</li>
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
.custom-control-label {
    cursor: pointer;
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Auto-scroll al resultado si existe
    if ($('.card:contains("Resultado del Diagnóstico")').length) {
        $('html, body').animate({
            scrollTop: $('.card:contains("Resultado del Diagnóstico")').offset().top - 100
        }, 500);
    }
});
</script>
@endsection
