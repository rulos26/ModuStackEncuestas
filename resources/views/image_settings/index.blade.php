@extends('adminlte::page')

@section('title', 'Configuración de Imágenes')

@section('content_header')
    <h1>Configuración de Imágenes</h1>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
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
                            <img src="{{ asset('storage/'.$settings->logo) }}" alt="Logo" height="60" class="mb-2">
                        @endif
                        <input type="file" name="logo" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label>Logo Login</label><br>
                        @if($settings->login_logo)
                            <img src="{{ asset('storage/'.$settings->login_logo) }}" alt="Logo Login" height="60" class="mb-2">
                        @endif
                        <input type="file" name="login_logo" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label>Logo Dashboard</label><br>
                        @if($settings->dashboard_logo)
                            <img src="{{ asset('storage/'.$settings->dashboard_logo) }}" alt="Logo Dashboard" height="60" class="mb-2">
                        @endif
                        <input type="file" name="dashboard_logo" class="form-control">
                    </div>
                    <div class="form-group mb-3">
                        <label>Spinner (Preloader)</label><br>
                        @if($settings->spinner)
                            <img src="{{ asset('storage/'.$settings->spinner) }}" alt="Spinner" height="60" class="mb-2">
                        @endif
                        <input type="file" name="spinner" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
