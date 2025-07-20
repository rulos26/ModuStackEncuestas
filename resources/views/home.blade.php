@extends('adminlte::page')

@section('title', 'Dashboard - Sistema de Encuestas')

@section('content_header')
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading"><i class="fas fa-info-circle"></i> ¡Bienvenido al Sistema de Encuestas!</h4>
                <p>Este es el panel de administración de tu sistema de encuestas. Utiliza el menú lateral para navegar por las diferentes secciones.</p>
                <hr>
                <p class="mb-0">El sistema incluye gestión de usuarios, roles, empleados, configuración dinámica y herramientas de optimización.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ App\Models\User::count() }}</h3>
                    <p>Usuarios Registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('users.index') }}" class="small-box-footer">
                    Gestionar usuarios <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ App\Models\Empleado::count() }}</h3>
                    <p>Empleados Registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-id-badge"></i>
                </div>
                <a href="{{ route('empleados.index') }}" class="small-box-footer">
                    Gestionar empleados <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ Spatie\Permission\Models\Role::count() }}</h3>
                    <p>Roles del Sistema</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <a href="{{ route('roles.index') }}" class="small-box-footer">
                    Gestionar roles <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ App\Models\SentMail::count() }}</h3>
                    <p>Emails Enviados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <a href="{{ route('admin.correos.index') }}" class="small-box-footer">
                    Panel de correos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cogs"></i> Configuración del Sistema</h3>
                </div>
                <div class="card-body">
                    <p>El sistema está configurado con:</p>
                    <ul>
                        <li><strong>AdminLTE 3:</strong> Plantilla administrativa moderna</li>
                        <li><strong>Laravel 12+:</strong> Framework PHP robusto</li>
                        <li><strong>CDN:</strong> Assets cargados desde CDN para mejor rendimiento</li>
                        <li><strong>Módulo de Imágenes:</strong> Configuración dinámica de logos y favicon</li>
                        <li><strong>Módulo de Empleados:</strong> Gestión completa con importación masiva</li>
                        <li><strong>Sistema de Roles:</strong> Permisos granulares con Spatie</li>
                    </ul>
                    <a href="{{ route('settings.images') }}" class="btn btn-primary">
                        <i class="fas fa-images"></i> Configurar Imágenes
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list"></i> Módulos Implementados</h3>
                </div>
                <div class="card-body">
                    <p>Los siguientes módulos están completamente funcionales:</p>
                    <ul>
                        <li><strong>Dashboard:</strong> Página principal con estadísticas</li>
                        <li><strong>Gestión de Usuarios:</strong> CRUD completo con DataTables</li>
                        <li><strong>Gestión de Roles:</strong> Sistema de permisos avanzado</li>
                        <li><strong>Gestión de Empleados:</strong> CRUD + Importación masiva (CSV/Excel)</li>
                        <li><strong>Panel de Correos:</strong> Envío y registro de emails</li>
                        <li><strong>Configuración:</strong> Imágenes del sistema</li>
                        <li><strong>Logs:</strong> Monitoreo del sistema</li>
                        <li><strong>Testing:</strong> Pruebas automatizadas</li>
                        <li><strong>Optimización:</strong> Herramientas de sistema</li>
                    </ul>
                    <small class="text-muted">Todos los módulos están operativos y listos para uso.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-rocket"></i> Acciones Rápidas</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('empleados.create') }}" class="btn btn-success btn-block">
                                <i class="fas fa-user-plus"></i> Registrar Empleado
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('empleados.import.form') }}" class="btn btn-info btn-block">
                                <i class="fas fa-upload"></i> Importar Empleados
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('users.create') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-user-plus"></i> Crear Usuario
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('logs.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-file-alt"></i> Ver Logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
