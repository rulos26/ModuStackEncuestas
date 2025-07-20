@extends('adminlte::page')

@section('title', 'Monitoreo de Sesiones Activas')

@section('content_header')
    <h1><i class="fas fa-users"></i> Monitoreo de Sesiones Activas</h1>
@stop

@section('content')
<div class="row">
    <!-- Estadísticas -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_active'] }}</h3>
                <p>Sesiones Activas</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['unique_users_today'] }}</h3>
                <p>Usuarios Únicos Hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['sessions_expired'] }}</h3>
                <p>Sesiones Expiradas</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $stats['total_today'] }}</h3>
                <p>Inicios de Sesión Hoy</p>
            </div>
            <div class="icon">
                <i class="fas fa-sign-in-alt"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Sesiones Activas
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="closeExpiredSessions()">
                        <i class="fas fa-times"></i> Cerrar Expiradas
                    </button>
                    <div class="btn-group ml-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('session.monitor.export', ['format' => 'csv', 'type' => 'active']) }}">
                                <i class="fas fa-file-csv"></i> CSV - Sesiones Activas
                            </a>
                            <a class="dropdown-item" href="{{ route('session.monitor.export', ['format' => 'json', 'type' => 'active']) }}">
                                <i class="fas fa-file-code"></i> JSON - Sesiones Activas
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('session.monitor.export', ['format' => 'csv', 'type' => 'all']) }}">
                                <i class="fas fa-file-csv"></i> CSV - Todas las Sesiones
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="roleFilter">Filtrar por Rol:</label>
                        <select id="roleFilter" class="form-control form-control-sm">
                            <option value="">Todos los roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" {{ $roleFilter == $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="statusFilter">Estado:</label>
                        <select id="statusFilter" class="form-control form-control-sm">
                            <option value="active" {{ $statusFilter == 'active' ? 'selected' : '' }}>Activas</option>
                            <option value="inactive" {{ $statusFilter == 'inactive' ? 'selected' : '' }}>Inactivas</option>
                            <option value="all">Todas</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="routeFilter">Filtrar por Ruta:</label>
                        <select id="routeFilter" class="form-control form-control-sm">
                            <option value="">Todas las rutas</option>
                            @foreach($routes as $route)
                                <option value="{{ $route }}" {{ $routeFilter == $route ? 'selected' : '' }}>{{ $route }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-primary btn-sm btn-block" onclick="applyFilters()">
                            <i class="fas fa-filter"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla de sesiones -->
                <div class="table-responsive">
                    <table id="sessionsTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>IP</th>
                                <th>Navegador/OS</th>
                                <th>Página Actual</th>
                                <th>Última Actividad</th>
                                <th>Tiempo Inactividad</th>
                                <th>Duración Sesión</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                            <tr data-session-id="{{ $session->id }}">
                                <td>
                                    <strong>{{ $session->user->name }}</strong><br>
                                    <small class="text-muted">{{ $session->user->email }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $session->user->roles->first()?->name ?? 'Sin rol' }}
                                    </span>
                                </td>
                                <td>
                                    <code>{{ $session->ip_address }}</code>
                                </td>
                                <td>
                                    <i class="fas fa-globe"></i> {{ $session->browser_info['browser'] }}<br>
                                    <small class="text-muted">{{ $session->browser_info['os'] }}</small>
                                </td>
                                <td>
                                    <span class="text-primary">{{ $session->current_page ?? 'N/A' }}</span><br>
                                    <small class="text-muted">{{ $session->current_route ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="last-activity">{{ $session->last_activity->format('H:i:s') }}</span><br>
                                    <small class="text-muted">{{ $session->last_activity->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <span class="inactivity-time">{{ $session->inactivity_time }}</span>
                                </td>
                                <td>
                                    <span class="session-duration">{{ $session->session_duration }}</span>
                                </td>
                                <td>
                                    @if($session->is_active)
                                        <span class="badge badge-success">
                                            <i class="fas fa-circle"></i> Activa
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-circle"></i> Inactiva
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->is_active)
                                        <button type="button" class="btn btn-sm btn-danger"
                                                onclick="closeSession({{ $session->id }})"
                                                title="Cerrar sesión">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="d-flex justify-content-center">
                    {{ $sessions->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        transition: transform 0.2s;
    }
    .small-box:hover {
        transform: translateY(-2px);
    }
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.8em;
    }
    .last-activity, .inactivity-time, .session-duration {
        font-weight: bold;
    }
    .text-muted {
        font-size: 0.85em;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#sessionsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "pageLength": 25,
        "order": [[5, "desc"]], // Ordenar por última actividad
        "responsive": true,
        "autoWidth": false
    });

    // Actualizar datos cada 30 segundos
    setInterval(refreshData, 30000);
});

function refreshData() {
    $.ajax({
        url: '{{ route("session.monitor.active") }}',
        method: 'GET',
        success: function(data) {
            updateTable(data);
        },
        error: function(xhr) {
            console.error('Error al actualizar datos:', xhr);
        }
    });
}

function updateTable(sessions) {
    const tbody = $('#sessionsTable tbody');
    tbody.empty();

    sessions.forEach(function(session) {
        const row = `
            <tr data-session-id="${session.id}">
                <td>
                    <strong>${session.user_name}</strong><br>
                    <small class="text-muted">${session.user_email}</small>
                </td>
                <td>
                    <span class="badge badge-info">${session.role}</span>
                </td>
                <td>
                    <code>${session.ip_address}</code>
                </td>
                <td>
                    <i class="fas fa-globe"></i> ${session.browser_info.browser}<br>
                    <small class="text-muted">${session.browser_info.os}</small>
                </td>
                <td>
                    <span class="text-primary">${session.current_page || 'N/A'}</span><br>
                    <small class="text-muted">${session.current_route || 'N/A'}</small>
                </td>
                <td>
                    <span class="last-activity">${session.last_activity}</span><br>
                    <small class="text-muted">${session.login_time}</small>
                </td>
                <td>
                    <span class="inactivity-time">${session.inactivity_time}</span>
                </td>
                <td>
                    <span class="session-duration">${session.session_duration}</span>
                </td>
                <td>
                    <span class="badge badge-success">
                        <i class="fas fa-circle"></i> Activa
                    </span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="closeSession(${session.id})"
                            title="Cerrar sesión">
                        <i class="fas fa-times"></i>
                    </button>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

function closeSession(sessionId) {
    if (!confirm('¿Estás seguro de que quieres cerrar esta sesión?\n\n⚠️ ADVERTENCIA: El usuario será FORZADO a hacer logout y deberá iniciar sesión nuevamente.')) {
        return;
    }

    $.ajax({
        url: `/session-monitor/close/${sessionId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $(`tr[data-session-id="${sessionId}"]`).fadeOut();
                toastr.success(response.message);

                // Mostrar notificación adicional
                setTimeout(function() {
                    toastr.info('El usuario será redirigido automáticamente al login en la próxima acción.');
                }, 2000);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            toastr.error('Error al cerrar la sesión');
        }
    });
}

function closeExpiredSessions() {
    if (!confirm('¿Estás seguro de que quieres cerrar todas las sesiones expiradas?\n\n⚠️ ADVERTENCIA: Todos los usuarios con sesiones expiradas serán FORZADOS a hacer logout y deberán iniciar sesión nuevamente.')) {
        return;
    }

    $.ajax({
        url: '{{ route("session.monitor.close-expired") }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);

                // Mostrar notificación adicional
                setTimeout(function() {
                    toastr.info('Los usuarios serán redirigidos automáticamente al login en la próxima acción.');
                }, 2000);

                setTimeout(refreshData, 1000);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            toastr.error('Error al cerrar sesiones expiradas');
        }
    });
}

function applyFilters() {
    const roleFilter = $('#roleFilter').val();
    const statusFilter = $('#statusFilter').val();
    const routeFilter = $('#routeFilter').val();

    const params = new URLSearchParams();
    if (roleFilter) params.append('role', roleFilter);
    if (statusFilter) params.append('status', statusFilter);
    if (routeFilter) params.append('route', routeFilter);

    window.location.href = '{{ route("session.monitor.index") }}?' + params.toString();
}
</script>
@stop
