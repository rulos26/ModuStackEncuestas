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
                                                            <option value="estado_encuesta" {{ $tipo === 'estado_encuesta' ? 'selected' : '' }}>
                            Diagnosticar Estado de Encuesta
                        </option>
                        <option value="probar_envio" {{ $tipo === 'probar_envio' ? 'selected' : '' }}>
                            Probar Configuración de Envío
                        </option>
                        <option value="diagnosticar_tipos" {{ $tipo === 'diagnosticar_tipos' ? 'selected' : '' }}>
                            Diagnosticar Tipos de Preguntas
                        </option>
                        <option value="diagnosticar_progreso" {{ $tipo === 'diagnosticar_progreso' ? 'selected' : '' }}>
                            Diagnosticar Progreso de Encuesta
                        </option>
                        <option value="forzar_validaciones" {{ $tipo === 'forzar_validaciones' ? 'selected' : '' }}>
                            Forzar Validaciones de Encuesta
                        </option>
                        <option value="probar_dashboard" {{ $tipo === 'probar_dashboard' ? 'selected' : '' }}>
                            Probar Dashboard de Seguimiento
                        </option>
                        <option value="diagnosticar_dashboard" {{ $tipo === 'diagnosticar_dashboard' ? 'selected' : '' }}>
                            Diagnosticar Dashboard de Seguimiento
                        </option>
                        <option value="migracion_sent_mails" {{ $tipo === 'migracion_sent_mails' ? 'selected' : '' }}>
                            Migración Sent Mails (Status)
                        </option>
                        <option value="migracion_configuracion_envio" {{ $tipo === 'migracion_configuracion_envio' ? 'selected' : '' }}>
                            🗄️ Probar Migración Configuración Envío
                        </option>
                        <option value="diagnosticar_tablas_migracion" {{ $tipo === 'diagnosticar_tablas_migracion' ? 'selected' : '' }}>
                            🔍 Diagnosticar Tablas para Migración
                        </option>
                        <option value="probar_migracion_simple" {{ $tipo === 'probar_migracion_simple' ? 'selected' : '' }}>
                            🧪 Probar Migración Simple
                        </option>
                        <option value="verificar_tabla_empresas" {{ $tipo === 'verificar_tabla_empresas' ? 'selected' : '' }}>
                            🔍 Verificar Tabla Empresas
                        </option>
                        <option value="corregir_user_id" {{ $tipo === 'corregir_user_id' ? 'selected' : '' }}>
                            Corregir User ID de Encuesta
                        </option>
                        <option value="debug_dashboard" {{ $tipo === 'debug_dashboard' ? 'selected' : '' }}>
                            Debug Dashboard de Encuesta
                        </option>
                        <option value="verificar_enum" {{ $tipo === 'verificar_enum' ? 'selected' : '' }}>
                            Verificar ENUM de Estado
                        </option>
                        <option value="tester_flujo_completo" {{ $tipo === 'tester_flujo_completo' ? 'selected' : '' }}>
                            Tester Flujo Completo de Encuestas
                        </option>
                        <option value="publicar_encuesta" {{ $tipo === 'publicar_encuesta' ? 'selected' : '' }}>
                            Publicar Encuesta y Generar Enlace
                        </option>
                        <option value="verificar_respuestas" {{ $tipo === 'verificar_respuestas' ? 'selected' : '' }}>
                            Verificar Respuestas de Encuesta
                        </option>
                        <option value="configurar_sesiones" {{ $tipo === 'configurar_sesiones' ? 'selected' : '' }}>
                            Configurar Sesiones para Hosting
                        </option>
                        <option value="verificar_escala" {{ $tipo === 'verificar_escala' ? 'selected' : '' }}>
                            Verificar Preguntas de Escala
                        </option>
                        <option value="diagnosticar_error_publica" {{ $tipo === 'diagnosticar_error_publica' ? 'selected' : '' }}>
                            Diagnosticar Error Encuesta Pública
                        </option>
                        <option value="solucionar_hosting_completa" {{ $tipo === 'solucionar_hosting_completa' ? 'selected' : '' }}>
                            Solución Completa para Hosting
                        </option>
                        <option value="diagnosticar_envio_correos" {{ $tipo === 'diagnosticar_envio_correos' ? 'selected' : '' }}>
                            📧 Diagnosticar Envío de Correos
                        </option>
                        </option>
                        <option value="solucionar_csrf_hosting" {{ $tipo === 'solucionar_csrf_hosting' ? 'selected' : '' }}>
                            Solucionar Error CSRF en Hosting
                        </option>
                        <option value="solucion_definitiva_hosting" {{ $tipo === 'solucion_definitiva_hosting' ? 'selected' : '' }}>
                            🚀 Solución Definitiva para Hosting
                        </option>
                        <option value="emergency_hosting_fix" {{ $tipo === 'emergency_hosting_fix' ? 'selected' : '' }}>
                            🚨 SOLUCIÓN DE EMERGENCIA
                        </option>
                        <option value="diagnosticar_flujo_publica" {{ $tipo === 'diagnosticar_flujo_publica' ? 'selected' : '' }}>
                            🔍 Diagnosticar Flujo Encuesta Pública
                        </option>
                        <option value="revisar_logs_prueba" {{ $tipo === 'revisar_logs_prueba' ? 'selected' : '' }}>
                            📝 Revisar Logs de Prueba
                        </option>
                        <option value="fix_session_419" {{ $tipo === 'fix_session_419' ? 'selected' : '' }}>
                            🔧 Solucionar Error 419 - Sesiones
                        </option>
                        <option value="diagnosticar_redireccion_fin" {{ $tipo === 'diagnosticar_redireccion_fin' ? 'selected' : '' }}>
                            🔍 Diagnosticar Redirección a Fin de Encuesta
                        </option>
                        <option value="probar_numero_encuestas" {{ $tipo === 'probar_numero_encuestas' ? 'selected' : '' }}>
                            🧪 Probar Campo Numero Encuestas
                        </option>
                        <option value="probar_contadores_encuesta" {{ $tipo === 'probar_contadores_encuesta' ? 'selected' : '' }}>
                            📊 Probar Contadores de Encuesta
                        </option>
                                    <option value="limpiar_cache" {{ $tipo === 'limpiar_cache' ? 'selected' : '' }}>
                                        Limpiar Caché del Sistema
                                    </option>
                                    <option value="limpiar_todo" {{ $tipo === 'limpiar_todo' ? 'selected' : '' }}>
                                        Optimizar Todo el Sistema
                                    </option>
                                </select>
                                <small class="form-text text-muted">
                                    Selecciona el tipo de prueba que deseas ejecutar.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="encuesta_id">ID de Encuesta:</label>
                                <input type="number" class="form-control" id="encuesta_id" name="encuesta_id"
                                       placeholder="Opcional para pruebas específicas">
                                <small class="form-text text-muted">
                                    Solo necesario para pruebas de preguntas específicas.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3" id="email_group" style="display: none;">
                            <div class="form-group">
                                <label for="email">Email de Prueba:</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="rulos26@gmail.com" value="rulos26@gmail.com">
                                <small class="form-text text-muted">
                                    Email donde llegarán todos los correos de prueba.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3" id="cantidad_group" style="display: none;">
                            <div class="form-group">
                                <label for="cantidad">Cantidad de Usuarios:</label>
                                <input type="number" class="form-control" id="cantidad" name="cantidad"
                                       placeholder="20" value="20" min="1" max="100">
                                <small class="form-text text-muted">
                                    Número de usuarios para el envío masivo.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3" id="horas_group" style="display: none;">
                            <div class="form-group">
                                <label for="horas">Horas de Validez:</label>
                                <input type="number" class="form-control" id="horas" name="horas"
                                       placeholder="24" value="24" min="1" max="168">
                                <small class="form-text text-muted">
                                    Horas de validez del token de acceso.
                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="debug" name="debug" value="1">
                                    <label class="custom-control-label" for="debug">
                                        <i class="fas fa-bug"></i> Modo Debug
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    Activa información adicional de debug.
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

                    <div class="col-md-3 mb-3">
                        <div class="card bg-info">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                                <h6>Estado Encuesta</h6>
                                <p class="card-text">Diagnostica estado</p>
                                <ul class="text-left small">
                                    <li>Verifica condiciones</li>
                                    <li>Revisa preguntas/respuestas</li>
                                    <li>Identifica problemas</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-broom fa-2x mb-2"></i>
                                <h6>Limpiar Caché</h6>
                                <p class="card-text">Limpia caché del sistema</p>
                                <ul class="text-left small">
                                    <li>config:clear</li>
                                    <li>route:clear</li>
                                    <li>view:clear</li>
                                    <li>cache:clear</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-success">
                            <div class="card-body text-center">
                                <i class="fas fa-rocket fa-2x mb-2"></i>
                                <h6>Optimizar Todo</h6>
                                <p class="card-text">Optimización completa</p>
                                <ul class="text-left small">
                                    <li>optimize:clear</li>
                                    <li>Limpia todo el sistema</li>
                                    <li>Mejora rendimiento</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-indigo">
                            <div class="card-body text-center">
                                <i class="fas fa-list-check fa-2x mb-2"></i>
                                <h6>Diagnosticar Tipos</h6>
                                <p class="card-text">Analiza tipos de preguntas</p>
                                <ul class="text-left small">
                                    <li>Verifica configuración</li>
                                    <li>Analiza necesidades</li>
                                    <li>Identifica problemas</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-purple">
                            <div class="card-body text-center">
                                <i class="fas fa-tasks fa-2x mb-2"></i>
                                <h6>Diagnosticar Progreso</h6>
                                <p class="card-text">Analiza progreso de encuesta</p>
                                <ul class="text-left small">
                                    <li>Verifica pasos completados</li>
                                    <li>Analiza lógica de flujo</li>
                                    <li>Identifica inconsistencias</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-danger">
                            <div class="card-body text-center">
                                <i class="fas fa-wrench fa-2x mb-2"></i>
                                <h6>Forzar Validaciones</h6>
                                <p class="card-text">Fuerza validaciones para desarrollo</p>
                                <ul class="text-left small">
                                    <li>Habilita envío masivo</li>
                                    <li>Completa validaciones</li>
                                    <li>Modo desarrollo</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-teal">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                <h6>Probar Dashboard</h6>
                                <p class="card-text">Prueba dashboard de seguimiento</p>
                                <ul class="text-left small">
                                    <li>Verifica bloques de envío</li>
                                    <li>Simula correos enviados</li>
                                    <li>Analiza estadísticas</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-orange">
                            <div class="card-body text-center">
                                <i class="fas fa-search fa-2x mb-2"></i>
                                <h6>Diagnosticar Dashboard</h6>
                                <p class="card-text">Diagnostica problemas del dashboard</p>
                                <ul class="text-left small">
                                    <li>Verifica rutas y modelos</li>
                                    <li>Analiza tablas y vistas</li>
                                    <li>Identifica errores</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card bg-maroon">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-2x mb-2"></i>
                                <h6>Migración Sent Mails</h6>
                                <p class="card-text">Agrega columnas status a sent_mails</p>
                                <ul class="text-left small">
                                    <li>Agrega columna status</li>
                                    <li>Agrega error_message</li>
                                    <li>Actualiza registros</li>
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
                        <li><strong>Diagnóstico de Estado de Encuesta:</strong> Verifica por qué una encuesta no puede enviarse masivamente, revisando todas las condiciones requeridas.</li>
                        <li><strong>Limpiar Caché del Sistema:</strong> Ejecuta todos los comandos de limpieza de caché (config, route, view, cache).</li>
                        <li><strong>Optimizar Todo el Sistema:</strong> Ejecuta optimize:clear para limpiar y optimizar todo el sistema.</li>
                        <li><strong>Diagnosticar Tipos de Preguntas:</strong> Analiza la configuración de tipos de preguntas, verifica qué tipos necesitan respuestas y cuáles permiten lógica condicional.</li>
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
        const selectedValue = $(this).val();
        const needsEncuestaId = ['preguntas', 'creacion_preguntas', 'simular_pregunta', 'estado_encuesta', 'probar_envio', 'diagnosticar_tipos', 'diagnosticar_progreso', 'forzar_validaciones', 'probar_dashboard', 'diagnosticar_dashboard', 'publicar_encuesta', 'verificar_respuestas', 'diagnosticar_envio_correos'].includes(selectedValue);
        const needsDebug = ['diagnosticar_tipos', 'diagnosticar_progreso'].includes(selectedValue);

        if (needsEncuestaId) {
            $('#encuesta_id').closest('.form-group').show();
            $('#encuesta_id').prop('required', true);

            // Actualizar placeholder según la opción
            if (selectedValue === 'estado_encuesta') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para diagnosticar');
            } else if (selectedValue === 'diagnosticar_tipos') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para analizar tipos');
            } else if (selectedValue === 'diagnosticar_progreso') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para analizar progreso');
            } else if (selectedValue === 'forzar_validaciones') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para forzar validaciones');
            } else if (selectedValue === 'probar_dashboard') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para probar dashboard');
            } else if (selectedValue === 'diagnosticar_dashboard') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para diagnosticar dashboard');
            } else if (selectedValue === 'migracion_sent_mails') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Ejecuta migración');
            } else if (selectedValue === 'migracion_configuracion_envio') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Ejecuta migración de configuración de envío');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'diagnosticar_tablas_migracion') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Diagnostica tablas necesarias');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'probar_migracion_simple') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Prueba migración simple');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'tester_flujo_completo') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Crea encuesta automáticamente');
                $('#email_group').show();
                $('#cantidad_group').show();
            } else if (selectedValue === 'publicar_encuesta') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta a publicar');
                $('#email_group').show();
                $('#email').attr('placeholder', 'Email para el token de acceso');
                $('#cantidad_group').hide();
                $('#horas_group').show();
            } else if (selectedValue === 'verificar_respuestas') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para verificar respuestas');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'configurar_sesiones') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Configuración automática');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'verificar_escala') {
                $('#encuesta_id').attr('placeholder', 'ID de encuesta (opcional) - Verifica todas si no se especifica');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'diagnosticar_error_publica') {
                $('#encuesta_id').attr('placeholder', 'ID de encuesta (opcional) - Diagnostica todas si no se especifica');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'solucionar_hosting_completa') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Solución automática completa');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'solucionar_csrf_hosting') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Solución específica para CSRF');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'solucion_definitiva_hosting') {
                $('#encuesta_id').attr('placeholder', '⚠️ SOLUCIÓN DEFINITIVA - Deshabilita CSRF completamente');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'emergency_hosting_fix') {
                $('#encuesta_id').attr('placeholder', '🚨 SOLUCIÓN DE EMERGENCIA - La más agresiva disponible');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'diagnosticar_flujo_publica') {
                $('#encuesta_id').attr('placeholder', 'ID de encuesta (opcional) - Diagnostica el flujo completo');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'revisar_logs_prueba') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Revisa logs de prueba generados');
                $('#email_group').hide();
                $('#cantidad_group').hide();
            } else if (selectedValue === 'diagnosticar_envio_correos') {
                $('#encuesta_id').attr('placeholder', 'ID de encuesta (opcional) - Diagnostica todas si no se especifica');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'fix_session_419') {
                $('#encuesta_id').attr('placeholder', 'No requiere ID - Soluciona error 419 de sesiones');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'diagnosticar_redireccion_fin') {
                $('#encuesta_id').attr('placeholder', 'Slug de encuesta (opcional) - Diagnostica todas si no se especifica');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'probar_numero_encuestas') {
                $('#encuesta_id').attr('placeholder', 'Valor por defecto (opcional) - Usa 100 si no se especifica');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'probar_contadores_encuesta') {
                $('#encuesta_id').attr('placeholder', 'ID de encuesta (opcional) - Prueba todas si no se especifica');
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            } else if (selectedValue === 'probar_envio') {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para probar envío');
            } else {
                $('#encuesta_id').attr('placeholder', 'ID de la encuesta para pruebas');
            }
                    } else {
                $('#encuesta_id').closest('.form-group').hide();
                $('#encuesta_id').prop('required', false);
                $('#email_group').hide();
                $('#cantidad_group').hide();
                $('#horas_group').hide();
            }

        // Mostrar/ocultar debug
        if (needsDebug) {
            $('#debug').closest('.form-group').show();
        } else {
            $('#debug').closest('.form-group').hide();
        }
    });

    // Ejecutar al cargar la página
    $('#tipo').trigger('change');
});
</script>
@endsection
