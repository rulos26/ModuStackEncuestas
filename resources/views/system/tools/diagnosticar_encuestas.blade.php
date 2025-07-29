@extends('adminlte::page')

@section('title', 'Diagnóstico de Encuestas')

@section('content_header')
    <h1><i class="fas fa-clipboard-check"></i> Diagnóstico de Encuestas</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Configuración del Diagnóstico</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('system.tools.diagnosticar-encuestas') }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="encuesta_id">ID de Encuesta (Opcional):</label>
                                <input type="number" class="form-control" id="encuesta_id" name="encuesta_id"
                                       value="{{ $encuestaId }}" placeholder="Dejar vacío para diagnóstico general">
                                <small class="form-text text-muted">
                                    Si especificas un ID, el diagnóstico se enfocará en esa encuesta específica.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
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
                        <li><strong>Verificación de Base de Datos:</strong> Comprueba la conexión y estructura de las tablas.</li>
                        <li><strong>Verificación de Modelos:</strong> Valida que todos los modelos estén correctamente definidos.</li>
                        <li><strong>Verificación de Datos:</strong> Revisa la integridad de los datos existentes.</li>
                        <li><strong>Verificación de Flujo:</strong> Comprueba que el flujo de trabajo funcione correctamente.</li>
                        <li><strong>Diagnóstico Específico:</strong> Si se especifica un ID, analiza esa encuesta en particular.</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-shield-alt"></i> Recomendaciones:</h6>
                    <ul class="mb-0">
                        <li>Ejecuta este diagnóstico cuando tengas problemas con las encuestas.</li>
                        <li>Si hay errores, revisa los logs del sistema para más detalles.</li>
                        <li>El diagnóstico puede tardar varios segundos dependiendo del tamaño de la base de datos.</li>
                        <li>Para problemas específicos, usa el ID de la encuesta afectada.</li>
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
