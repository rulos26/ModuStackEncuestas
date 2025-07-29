@extends('adminlte::page')

@section('title', 'Gestión de Migraciones')

@section('content_header')
    <h1><i class="fas fa-database"></i> Gestión de Migraciones</h1>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-cogs"></i> Acciones de Migración</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Estado de Migraciones -->
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info">
                            <div class="card-body text-center">
                                <i class="fas fa-list fa-2x mb-2"></i>
                                <h6>Ver Estado</h6>
                                <form method="POST" action="{{ route('system.tools.migraciones') }}">
                                    @csrf
                                    <input type="hidden" name="tipo" value="status">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm">
                                        <i class="fas fa-eye"></i> Ver Estado
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Ejecutar Migraciones -->
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success">
                            <div class="card-body text-center">
                                <i class="fas fa-play fa-2x mb-2"></i>
                                <h6>Ejecutar Migraciones</h6>
                                <form method="POST" action="{{ route('system.tools.migraciones') }}">
                                    @csrf
                                    <input type="hidden" name="tipo" value="run">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm"
                                            onclick="return confirm('¿Estás seguro de ejecutar las migraciones?')">
                                        <i class="fas fa-arrow-up"></i> Ejecutar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Rollback -->
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-undo fa-2x mb-2"></i>
                                <h6>Rollback</h6>
                                <form method="POST" action="{{ route('system.tools.migraciones') }}">
                                    @csrf
                                    <input type="hidden" name="tipo" value="rollback">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm"
                                            onclick="return confirm('¿Estás seguro de hacer rollback? Esto puede perder datos.')">
                                        <i class="fas fa-arrow-down"></i> Rollback
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Refresh -->
                    <div class="col-md-3 mb-3">
                        <div class="card bg-danger">
                            <div class="card-body text-center">
                                <i class="fas fa-redo fa-2x mb-2"></i>
                                <h6>Refresh</h6>
                                <form method="POST" action="{{ route('system.tools.migraciones') }}">
                                    @csrf
                                    <input type="hidden" name="tipo" value="refresh">
                                    <button type="submit" name="ejecutar" class="btn btn-light btn-sm"
                                            onclick="return confirm('¿Estás seguro de hacer refresh? Esto eliminará todos los datos.')">
                                        <i class="fas fa-sync"></i> Refresh
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                                <!-- Migraciones Específicas -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="card bg-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-question-circle fa-2x mb-2"></i>
                                <h6>Actualizar Tabla de Preguntas</h6>
                                <p class="card-text">Ejecuta la migración específica para actualizar la estructura de la tabla preguntas</p>
                                <form method="POST" action="{{ route('system.tools.migraciones') }}">
                                    @csrf
                                    <input type="hidden" name="tipo" value="preguntas">
                                    <button type="submit" name="ejecutar" class="btn btn-light">
                                        <i class="fas fa-database"></i> Actualizar Preguntas
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-success">
                            <div class="card-body text-center">
                                <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                <h6>Agregar Campos de Fecha</h6>
                                <p class="card-text">Ejecuta la migración para agregar los campos fecha_inicio y fecha_fin a la tabla encuestas</p>
                                <form method="POST" action="{{ route('system.tools.migraciones') }}">
                                    @csrf
                                    <input type="hidden" name="tipo" value="fechas_encuestas">
                                    <button type="submit" name="ejecutar" class="btn btn-light">
                                        <i class="fas fa-calendar-plus"></i> Agregar Fechas
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-broom fa-2x mb-2"></i>
                                <h6>Limpiar Migraciones Duplicadas</h6>
                                <p class="card-text">Elimina migraciones duplicadas y ejecuta la migración consolidada del sistema de encuestas</p>
                                <form method="POST" action="{{ route('system.tools.migraciones') }}">
                                    @csrf
                                    <input type="hidden" name="tipo" value="limpiar_encuestas">
                                    <button type="submit" name="ejecutar" class="btn btn-light"
                                            onclick="return confirm('¿Estás seguro? Esto eliminará migraciones duplicadas y recreará las tablas.')">
                                        <i class="fas fa-trash"></i> Limpiar y Consolidar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resultado de la Ejecución -->
@if($resultado)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-terminal"></i>
                    Resultado de la Ejecución
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

<!-- Estado Actual de Migraciones -->
@if($migrationStatus['success'])
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list"></i> Estado Actual de Migraciones</h3>
            </div>
            <div class="card-body">
                <pre class="bg-dark text-light p-3 rounded" style="max-height: 300px; overflow-y: auto; font-size: 12px;">{{ $migrationStatus['output'] }}</pre>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Información Adicional -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> Información Importante</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-exclamation-triangle"></i> Notas Importantes:</h6>
                    <ul class="mb-0">
                        <li><strong>Ejecutar:</strong> Aplica las migraciones pendientes de forma segura.</li>
                        <li><strong>Rollback:</strong> Revierte la última migración ejecutada.</li>
                        <li><strong>Refresh:</strong> Revierte todas las migraciones y las vuelve a ejecutar (¡CUIDADO! Elimina datos).</li>
                        <li><strong>Actualizar Preguntas:</strong> Ejecuta la migración específica para corregir la estructura de la tabla preguntas.</li>
                        <li><strong>Limpiar y Consolidar:</strong> Elimina migraciones duplicadas y ejecuta la migración consolidada (¡CUIDADO! Recrea tablas).</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-shield-alt"></i> Recomendaciones de Seguridad:</h6>
                    <ul class="mb-0">
                        <li>Siempre haz una copia de seguridad antes de ejecutar migraciones en producción.</li>
                        <li>Verifica el estado antes de ejecutar cualquier acción.</li>
                        <li>El comando "Refresh" eliminará todos los datos de la base de datos.</li>
                        <li>Ejecuta las migraciones en un entorno de desarrollo primero.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.card-body .card {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
.card-body .card:hover {
    transform: translateY(-2px);
    transition: transform 0.2s;
}
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
    if ($('.card:contains("Resultado de la Ejecución")').length) {
        $('html, body').animate({
            scrollTop: $('.card:contains("Resultado de la Ejecución")').offset().top - 100
        }, 500);
    }
});
</script>
@endsection
