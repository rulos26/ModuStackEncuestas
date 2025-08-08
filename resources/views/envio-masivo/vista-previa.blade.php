@extends('adminlte::page')

@section('title', 'Vista Previa del Correo')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-eye mr-2"></i>
                        Vista Previa del Correo
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('envio-masivo.index') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane mr-1"></i>
                            Volver al Envío
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Información de la encuesta -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-poll mr-1"></i>Información de la Encuesta</h6>
                        <strong>Título:</strong> {{ $encuesta->titulo }}<br>
                        <strong>Empresa:</strong> {{ $empresa ? $empresa->nombre : 'Sin empresa' }}<br>
                        <strong>Estado:</strong> {{ ucfirst($encuesta->estado) }}<br>
                        <strong>Preguntas:</strong> {{ $encuesta->preguntas->count() }}<br>
                        <strong>Link público:</strong>
                        <a href="{{ $linkEncuesta }}" target="_blank">{{ $linkEncuesta }}</a>
                    </div>

                    <!-- Información de empleados -->
                    <div class="alert alert-success">
                        <h6><i class="fas fa-users mr-1"></i>Empleados Destinatarios</h6>
                        <strong>Total empleados:</strong> {{ $empleados->count() }}<br>
                        <strong>Con email válido:</strong> {{ $empleados->whereNotNull('correo_electronico')->where('correo_electronico', '!=', '')->count() }}<br>
                        <strong>Destinatarios:</strong> {{ $empleados->whereNotNull('correo_electronico')->where('correo_electronico', '!=', '')->count() }} recibirán el correo
                    </div>

                    <!-- Vista previa del correo -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-envelope mr-1"></i>
                                Vista Previa del Correo
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="email-preview">
                                <div class="email-header">
                                    <strong>De:</strong> {{ config('mail.from.name') }} &lt;{{ config('mail.from.address') }}&gt;<br>
                                    <strong>Para:</strong> {{ $empleados->first() ? $empleados->first()->nombre : 'Empleado' }} &lt;{{ $empleados->first() ? $empleados->first()->correo_electronico : 'email@ejemplo.com' }}&gt;<br>
                                    <strong>Asunto:</strong> Invitación a participar en: {{ $encuesta->titulo }}
                                </div>
                                <hr>
                                <div class="email-body">
                                    <pre style="white-space: pre-wrap; font-family: inherit;">{{ $cuerpoCorreo }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de empleados -->
                    @if($empleados->count() > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-list mr-1"></i>
                                    Lista de Empleados Destinatarios ({{ $empleados->count() }})
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($empleados as $index => $empleado)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $empleado->nombre }}</td>
                                                    <td>{{ $empleado->correo_electronico }}</td>
                                                    <td>
                                                        @if($empleado->correo_electronico && filter_var($empleado->correo_electronico, FILTER_VALIDATE_EMAIL))
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check mr-1"></i>
                                                                Válido
                                                            </span>
                                                        @else
                                                            <span class="badge badge-danger">
                                                                <i class="fas fa-times mr-1"></i>
                                                                Inválido
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle mr-1"></i>No hay empleados</h6>
                            <p class="mb-0">No hay empleados registrados en esta empresa con emails válidos.</p>
                        </div>
                    @endif

                    <!-- Botones de acción -->
                    <div class="form-group mt-4">
                        <a href="{{ route('envio-masivo.index') }}?encuesta_id={{ $encuesta->id }}" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i>
                            Proceder con el Envío
                        </a>

                        <a href="{{ route('envio-masivo.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.email-preview {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    background-color: #f8f9fa;
}

.email-header {
    background-color: #e9ecef;
    padding: 10px;
    border-radius: 3px;
    margin-bottom: 15px;
    font-size: 14px;
}

.email-body {
    background-color: white;
    padding: 15px;
    border-radius: 3px;
    border: 1px solid #dee2e6;
}

.email-body pre {
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
}
</style>
@endpush
