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
                <p class="mb-0">El menú está configurado en <code>config/adminlte.php</code> y se renderiza automáticamente en todas las vistas que extiendan de <code>adminlte::page</code>.</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>0</h3>
                    <p>Encuestas Creadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Ver todas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>0</h3>
                    <p>Respuestas Recibidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-reply"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Ver respuestas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>1</h3>
                    <p>Usuarios Registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Gestionar usuarios <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>0</h3>
                    <p>Reportes Generados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <a href="#" class="small-box-footer">
                    Ver reportes <i class="fas fa-arrow-circle-right"></i>
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
                    <h3 class="card-title"><i class="fas fa-list"></i> Menú Implementado</h3>
                </div>
                <div class="card-body">
                    <p>El menú lateral incluye las siguientes secciones:</p>
                    <ul>
                        <li><strong>Dashboard:</strong> Página principal (actual)</li>
                        <li><strong>Gestión de Encuestas:</strong> Crear y gestionar encuestas</li>
                        <li><strong>Respuestas:</strong> Ver y exportar respuestas</li>
                        <li><strong>Administración:</strong> Usuarios y configuración</li>
                        <li><strong>Sistema:</strong> Logs y ayuda</li>
                    </ul>
                    <small class="text-muted">Las rutas marcadas con # están pendientes de implementación.</small>
                </div>
            </div>
        </div>
    </div>
@endsection
