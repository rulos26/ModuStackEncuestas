@extends('adminlte::page')

@section('title', 'Sistema de Pruebas - Cron Job y Envío Programado')

@section('content_header')
    <h1>
        <i class="fas fa-vial"></i> Sistema de Pruebas
        <small>Cron Job y Envío Programado</small>
    </h1>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Alertas -->
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
                    <h5><i class="icon fas fa-ban"></i> Error!</h5>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Panel de Comandos de Diagnóstico -->
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-stethoscope"></i> Diagnóstico del Sistema
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-database"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Conexión BD</span>
                                    <span class="info-box-number">Verificar</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Estado de la base de datos
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cron Job</span>
                                    <span class="info-box-number">Verificar</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 100%"></div>
                                    </div>
                                    <span class="progress-description">
                                        Estado del sistema de envío
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comandos de Prueba -->
            <div class="row">
                <!-- Diagnóstico General -->
                <div class="col-md-6">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-search"></i> Diagnóstico General
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('testing.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="probar:cron-job">
                                <div class="form-group">
                                    <label>Probar Cron Job Completo</label>
                                    <p class="text-muted">Verifica conexión, configuraciones, empleados y jobs</p>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="debug" name="options[]" value="--debug">
                                        <label class="custom-control-label" for="debug">Modo debug (información detallada)</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fas fa-play"></i> Ejecutar Diagnóstico
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sistema de Colas -->
                <div class="col-md-6">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i> Sistema de Colas
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('testing.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="verificar:sistema-colas">
                                <div class="form-group">
                                    <label>Verificar Sistema de Colas</label>
                                    <p class="text-muted">Verifica tablas, jobs fallidos y configuración</p>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="fix" name="options[]" value="--fix">
                                        <label class="custom-control-label" for="fix">Intentar arreglar problemas</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-warning btn-block">
                                    <i class="fas fa-tools"></i> Verificar Colas
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Envío de Correos -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-envelope"></i> Pruebas de Envío de Correos
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <form method="POST" action="{{ route('testing.run') }}">
                                        @csrf
                                        <input type="hidden" name="command" value="probar:envio-correos">
                                        <input type="hidden" name="options[]" value="--test">
                                        <div class="form-group">
                                            <label>Correo de Prueba</label>
                                            <p class="text-muted">Envía solo correo de prueba</p>
                                        </div>
                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-paper-plane"></i> Enviar Prueba
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <form method="POST" action="{{ route('testing.run') }}">
                                        @csrf
                                        <input type="hidden" name="command" value="probar:envio-correos">
                                        <input type="hidden" name="options[]" value="--force">
                                        <div class="form-group">
                                            <label>Enviar Todos (Forzado)</label>
                                            <p class="text-muted">Fuerza envío sin verificar fecha/hora</p>
                                        </div>
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <i class="fas fa-rocket"></i> Enviar Forzado
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4">
                                    <form method="POST" action="{{ route('testing.run') }}">
                                        @csrf
                                        <input type="hidden" name="command" value="ejecutar:cron-job">
                                        <input type="hidden" name="options[]" value="--force">
                                        <div class="form-group">
                                            <label>Ejecutar Cron Job</label>
                                            <p class="text-muted">Ejecuta manualmente el cron job</p>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-cog"></i> Ejecutar Cron
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración Específica -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cogs"></i> Prueba de Configuración Específica
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('testing.run') }}">
                                @csrf
                                <input type="hidden" name="command" value="probar:envio-correos">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="configuracion_id">ID de Configuración</label>
                                            <input type="number" class="form-control" id="configuracion_id" name="configuracion_id" placeholder="Ej: 1" min="1">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="test_mode" name="options[]" value="--test">
                                                <label class="custom-control-label" for="test_mode">Modo prueba</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="force_mode" name="options[]" value="--force">
                                                <label class="custom-control-label" for="force_mode">Forzar envío</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn btn-secondary btn-block">
                                                <i class="fas fa-search"></i> Probar Configuración
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados -->
            @if(isset($output))
                <div class="card card-outline card-default">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-terminal"></i> Resultado del Comando
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" onclick="copyToClipboard()">
                                <i class="fas fa-copy"></i> Copiar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <pre id="output" style="background:#2d3748;color:#f8f9fa;padding:1em;border-radius:8px;max-height:500px;overflow-y:auto;font-family:monospace;font-size:12px;">{{ $output }}</pre>
                    </div>
                </div>
            @endif

            <!-- Información del Sistema -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-info-circle"></i> Información del Sistema
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box bg-success">
                                        <span class="info-box-icon"><i class="fas fa-database"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Base de Datos</span>
                                            <span class="info-box-number">{{ config('database.default') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-warning">
                                        <span class="info-box-icon"><i class="fas fa-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Colas</span>
                                            <span class="info-box-number">{{ config('queue.default') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-info">
                                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Zona Horaria</span>
                                            <span class="info-box-number">{{ config('app.timezone') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-primary">
                                        <span class="info-box-icon"><i class="fas fa-calendar"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Hora Actual</span>
                                            <span class="info-box-number">{{ now()->format('H:i:s') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const output = document.getElementById('output');
    const text = output.textContent;

    navigator.clipboard.writeText(text).then(function() {
        // Mostrar notificación
        const notification = document.createElement('div');
        notification.className = 'alert alert-success alert-dismissible';
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '9999';
        notification.innerHTML = `
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check"></i> Resultado copiado al portapapeles
        `;

        document.body.appendChild(notification);

        setTimeout(function() {
            notification.remove();
        }, 3000);
    });
}

// Auto-scroll al resultado cuando se carga
@if(isset($output))
    document.addEventListener('DOMContentLoaded', function() {
        const output = document.getElementById('output');
        if (output) {
            output.scrollIntoView({ behavior: 'smooth' });
        }
    });
@endif
</script>
@endsection
