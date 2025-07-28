@extends('layouts.app')

@section('title', 'Renovar Enlace - ' . $encuesta->titulo)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-link"></i> Renovar Enlace de Encuesta
                    </h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ session('warning') }}
                        </div>
                    @endif

                    @if(session('nuevo_enlace'))
                        <div class="alert alert-info">
                            <h5><i class="fas fa-link"></i> Nuevo Enlace Generado</h5>
                            <p>Tu enlace ha sido renovado exitosamente. Copia el siguiente enlace:</p>
                            <div class="input-group">
                                <input type="text" class="form-control" value="{{ session('nuevo_enlace') }}" readonly>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="copiarEnlace()">
                                        <i class="fas fa-copy"></i> Copiar
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Este enlace expira en 24 horas.</small>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <h4>{{ $encuesta->titulo }}</h4>
                            <p class="text-muted">{{ $encuesta->empresa->nombre ?? 'Empresa' }}</p>

                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle"></i> Información del Enlace</h5>
                                <ul class="mb-0">
                                    <li><strong>Email:</strong> {{ $tokenEncuesta->email_destinatario }}</li>
                                    <li><strong>Estado:</strong>
                                        @if($tokenEncuesta->usado)
                                            <span class="badge badge-success">Usado</span>
                                        @elseif($tokenEncuesta->haExpirado())
                                            <span class="badge badge-danger">Expirado</span>
                                        @else
                                            <span class="badge badge-warning">Válido</span>
                                        @endif
                                    </li>
                                    <li><strong>Tiempo restante:</strong> {{ $tokenEncuesta->tiempoRestante() }}</li>
                                    @if($tokenEncuesta->fecha_uso)
                                        <li><strong>Usado el:</strong> {{ $tokenEncuesta->fecha_uso->format('d/m/Y H:i') }}</li>
                                    @endif
                                </ul>
                            </div>

                            @if($tokenEncuesta->haExpirado())
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-clock"></i> Enlace Expirado</h5>
                                    <p>Tu enlace ha expirado. Para renovarlo, ingresa tu email y haz clic en "Renovar Enlace".</p>
                                </div>

                                <form method="POST" action="{{ route('encuestas.renovar.enlace', $encuesta->slug) }}">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $tokenEncuesta->token_acceso }}">

                                    <div class="form-group">
                                        <label for="email">
                                            <i class="fas fa-envelope"></i> Email
                                        </label>
                                        <input type="email" name="email" id="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               value="{{ $tokenEncuesta->email_destinatario }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-sync-alt"></i> Renovar Enlace
                                    </button>
                                </form>
                            @elseif($tokenEncuesta->usado)
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-check-circle"></i> Enlace Ya Utilizado</h5>
                                    <p>Este enlace ya ha sido utilizado. Si necesitas acceder nuevamente, contacta al administrador.</p>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle"></i> Enlace Válido</h5>
                                    <p>Tu enlace aún es válido. Puedes acceder a la encuesta usando el enlace original.</p>
                                    <a href="{{ $tokenEncuesta->obtenerEnlace() }}" class="btn btn-success">
                                        <i class="fas fa-external-link-alt"></i> Ir a la Encuesta
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-question-circle"></i> Ayuda
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <h6><i class="fas fa-info-circle"></i> ¿Por qué renovar?</h6>
                                    <ul class="list-unstyled">
                                        <li>• Los enlaces expiran por seguridad</li>
                                        <li>• Cada enlace es único por destinatario</li>
                                        <li>• Solo puedes renovar tu propio enlace</li>
                                        <li>• El nuevo enlace tendrá 24 horas de validez</li>
                                    </ul>

                                    <h6><i class="fas fa-shield-alt"></i> Seguridad</h6>
                                    <ul class="list-unstyled">
                                        <li>• Los enlaces no se pueden compartir</li>
                                        <li>• Cada acceso es registrado</li>
                                        <li>• Los enlaces expirados no funcionan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function copiarEnlace() {
    const enlaceInput = document.querySelector('input[readonly]');
    enlaceInput.select();
    document.execCommand('copy');

    // Mostrar notificación
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copiado';
    button.classList.add('btn-success');
    button.classList.remove('btn-outline-secondary');

    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-secondary');
    }, 2000);
}

// Auto-hide alerts
setTimeout(function() {
    $('.alert').fadeOut('slow');
}, 10000);
</script>
@endsection
