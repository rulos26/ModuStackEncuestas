@extends('adminlte::page')

@section('title', 'Configuración de Imágenes')

@section('content_header')
    <h1>Configuración de Imágenes</h1>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="alert alert-info">
            <h5 class="mb-2"><i class="fas fa-info-circle"></i> ¿Qué es el Módulo de Imágenes?</h5>
            <ul class="mb-1">
                <li><b>Logo Principal:</b> Imagen que aparece en la cabecera y menú lateral del sistema.</li>
                <li><b>Logo Login:</b> Imagen mostrada en la pantalla de inicio de sesión.</li>
                <li><b>Logo Dashboard:</b> Imagen destacada en la página principal del dashboard.</li>
                <li><b>Spinner (Preloader):</b> Imagen animada que aparece mientras carga el sistema.</li>
                <li><b>Favicon:</b> Icono pequeño que aparece en la pestaña del navegador.</li>
            </ul>
            <p class="mb-0">Puedes personalizar cada imagen para adaptar el sistema a la identidad visual de tu organización. <br>Solo los administradores pueden realizar estos cambios.</p>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <div class="card">
            <div class="card-body">
                <form action="{{ route('settings.images.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-3">
                        <label>Logo Principal</label><br>
                        @if($settings->logo)
                            <img src="{{ asset('public/storage/images/logo/logo.png') }}" alt="Logo" height="60" class="mb-2">
                        @endif
                        <input type="file" name="logo" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label>Logo Login</label><br>
                        @if($settings->login_logo)
                            <img src="{{ asset('public/storage/images/login/login.png') }}" alt="Logo Login" height="60" class="mb-2">
                        @endif
                        <input type="file" name="login_logo" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label>Logo Dashboard</label><br>
                        @if($settings->dashboard_logo)
                            <img src="{{ asset('public/storage/images/dashboard/dashboard.png') }}" alt="Logo Dashboard" height="60" class="mb-2">
                        @endif
                        <input type="file" name="dashboard_logo" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label>Spinner (Preloader)</label><br>
                        @if($settings->spinner)
                            <img src="{{ asset('public/storage/images/spinner/spinner.png') }}" alt="Spinner" height="60" class="mb-2">
                        @endif
                        <input type="file" name="spinner" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label>Favicon</label><br>
                        @if($settings->favicon)
                            <img src="{{ asset('public/storage/images/favicon/favicon.png') }}" alt="Favicon" height="32" class="mb-2">
                        @endif
                        <input type="file" name="favicon" class="form-control" accept="image/x-icon,image/png">
                        <small class="form-text text-muted">Tamaño recomendado: 32x32px o 64x64px (.ico o .png)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
