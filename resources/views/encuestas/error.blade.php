@extends('adminlte::page')

@section('title', $titulo ?? 'Error')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-exclamation-triangle text-danger"></i> {{ $titulo ?? 'Error' }}
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-link-broken fa-4x text-muted"></i>
                    </div>

                    <h4 class="text-danger">{{ $mensaje ?? 'Ha ocurrido un error' }}</h4>

                    <div class="alert alert-info mt-4">
                        <h5><i class="fas fa-info-circle"></i> Posibles causas:</h5>
                        <ul class="list-unstyled text-left">
                            <li><i class="fas fa-clock text-warning"></i> El enlace ha expirado (24 horas)</li>
                            <li><i class="fas fa-times text-danger"></i> El enlace ya fue utilizado</li>
                            <li><i class="fas fa-ban text-danger"></i> El enlace es inválido o incorrecto</li>
                            <li><i class="fas fa-pause text-warning"></i> La encuesta está temporalmente deshabilitada</li>
                        </ul>
                    </div>

                    <div class="mt-4">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Ir al Inicio
                        </a>

                        <button class="btn btn-outline-secondary ml-2" onclick="window.history.back()">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                    </div>

                    <div class="mt-4">
                        <small class="text-muted">
                            Si crees que esto es un error, contacta al administrador del sistema.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
