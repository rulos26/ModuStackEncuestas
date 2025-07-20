@extends('adminlte::page')

@section('title', 'Historial de Sesiones')

@section('content_header')
    <h1><i class="fas fa-history"></i> Historial de Sesiones</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list"></i> Historial de Sesiones Cerradas
                </h3>
                <div class="card-tools">
                    <a href="{{ route('session.monitor.index') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Volver al Monitoreo
                    </a>
                    <div class="btn-group ml-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('session.monitor.export', ['format' => 'csv', 'type' => 'inactive']) }}">
                                <i class="fas fa-file-csv"></i> CSV - Historial
                            </a>
                            <a class="dropdown-item" href="{{ route('session.monitor.export', ['format' => 'json', 'type' => 'inactive']) }}">
                                <i class="fas fa-file-code"></i> JSON - Historial
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Filtros de fecha -->
                <form method="GET" action="{{ route('session.monitor.history') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="start_date">Fecha de Inicio:</label>
                            <input type="date" id="start_date" name="start_date"
                                   class="form-control" value="{{ $startDate ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date">Fecha de Fin:</label>
                            <input type="date" id="end_date" name="end_date"
                                   class="form-control" value="{{ $endDate ?? '' }}">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <a href="{{ route('session.monitor.history') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-times"></i> Limpiar Filtros
                            </a>
                        </div>
                    </div>
                </form>

                <!-- Tabla de historial -->
                <div class="table-responsive">
                    <table id="historyTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>IP</th>
                                <th>Navegador/OS</th>
                                <th>Última Página</th>
                                <th>Hora de Login</th>
                                <th>Hora de Logout</th>
                                <th>Duración</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $session)
                            <tr>
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
                                    <span class="text-success">{{ $session->login_time->format('H:i:s') }}</span><br>
                                    <small class="text-muted">{{ $session->login_time->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <span class="text-danger">{{ $session->logout_time?->format('H:i:s') ?? 'N/A' }}</span><br>
                                    <small class="text-muted">{{ $session->logout_time?->format('d/m/Y') ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <span class="session-duration">{{ $session->session_duration }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-circle"></i> Cerrada
                                    </span>
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
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.8em;
    }
    .session-duration {
        font-weight: bold;
        color: #007bff;
    }
    .text-muted {
        font-size: 0.85em;
    }
    .card-tools .btn-group {
        margin-left: 10px;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#historyTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "pageLength": 50,
        "order": [[5, "desc"]], // Ordenar por hora de login
        "responsive": true,
        "autoWidth": false,
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copiar',
                className: 'btn btn-sm btn-outline-secondary'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-sm btn-outline-success'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-sm btn-outline-danger'
            }
        ]
    });
});
</script>
@stop
