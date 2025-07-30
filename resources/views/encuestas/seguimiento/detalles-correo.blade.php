<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label><strong>Nombre:</strong></label>
            <p class="form-control-static">{{ $correo['nombre'] }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label><strong>Email:</strong></label>
            <p class="form-control-static">{{ $correo['email'] }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label><strong>Tipo:</strong></label>
            <p class="form-control-static">
                @if($correo['tipo'] === 'empleado')
                    <span class="badge badge-info">Empleado</span>
                @else
                    <span class="badge badge-primary">Usuario</span>
                @endif
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label><strong>Cargo:</strong></label>
            <p class="form-control-static">{{ $correo['cargo'] ?? 'N/A' }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            <label><strong>Encuesta:</strong></label>
            <p class="form-control-static">{{ $encuesta->titulo }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label><strong>Fecha de Creación:</strong></label>
            <p class="form-control-static">{{ $encuesta->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label><strong>Fecha Límite:</strong></label>
            <p class="form-control-static">
                @if($encuesta->fecha_fin)
                    {{ $encuesta->fecha_fin->format('d/m/Y H:i:s') }}
                @else
                    Sin fecha límite
                @endif
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="form-group">
            <label><strong>Descripción:</strong></label>
            <p class="form-control-static">{{ $encuesta->descripcion ?? 'Sin descripción' }}</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle"></i> Información del Envío</h6>
            <ul class="mb-0">
                <li><strong>Estado:</strong> Pendiente de envío</li>
                <li><strong>Token:</strong> Se generará automáticamente al enviar</li>
                <li><strong>Enlace:</strong> Se creará dinámicamente</li>
                <li><strong>Plantilla:</strong> Correo estándar de encuesta</li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="alert alert-warning">
            <h6><i class="fas fa-exclamation-triangle"></i> Nota Importante</h6>
            <p class="mb-0">
                Al enviar este correo, se generará un token único para este destinatario
                que le permitirá acceder a la encuesta de forma segura.
            </p>
        </div>
    </div>
</div>
