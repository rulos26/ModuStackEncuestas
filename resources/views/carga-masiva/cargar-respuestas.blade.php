@extends('adminlte::page')

@section('title', 'Cargar Respuestas')

@section('content_header')
    <h1>
        <i class="fas fa-reply"></i> Cargar Respuestas
        <small class="text-muted">{{ $encuesta->titulo }}</small>
    </h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Mensaje de Éxito -->
            @if(request('preguntas_guardadas'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h5><i class="icon fas fa-check"></i> ¡Preguntas Guardadas!</h5>
                    <p class="mb-0">
                        Se han guardado <strong>{{ request('preguntas_guardadas') }} preguntas</strong> exitosamente.
                        @if(request('errores'))
                            <br><small class="text-warning">Algunas preguntas tuvieron errores: {{ implode(', ', request('errores')) }}</small>
                        @endif
                    </p>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-upload"></i> Cargar Respuestas desde Archivo
                    </h3>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Error</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('carga-masiva.procesar-respuestas') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="encuesta_id" value="{{ $encuesta->id }}">

                        <!-- Información de la Encuesta -->
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Información de la Encuesta</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Encuesta:</strong> {{ $encuesta->titulo }}<br>
                                    <strong>Preguntas:</strong> {{ $preguntas->count() }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Estado:</strong>
                                    <span class="badge badge-{{ $encuesta->estado === 'activa' ? 'success' : 'warning' }}">
                                        {{ ucfirst($encuesta->estado) }}
                                    </span><br>
                                    <strong>Creada:</strong> {{ $encuesta->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>

                        <!-- Archivo de Respuestas -->
                        <div class="form-group">
                            <label for="archivo_respuestas">
                                <i class="fas fa-file-alt"></i> Archivo de Respuestas (.txt)
                            </label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input @error('archivo_respuestas') is-invalid @enderror"
                                           id="archivo_respuestas" name="archivo_respuestas" accept=".txt" required>
                                    <label class="custom-file-label" for="archivo_respuestas">Elegir archivo...</label>
                                </div>
                            </div>
                            @error('archivo_respuestas')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Formato requerido:</strong> Un archivo .txt con respuestas en formato R_X: contenido
                            </small>
                        </div>

                        <!-- Vista Previa de Preguntas -->
                        <div class="form-group">
                            <label>
                                <i class="fas fa-eye"></i> Vista Previa de Preguntas
                            </label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="10%">#</th>
                                            <th width="60%">Pregunta</th>
                                            <th width="20%">Tipo</th>
                                            <th width="10%">ID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($preguntas as $pregunta)
                                            <tr>
                                                <td class="text-center">
                                                    <span class="badge badge-primary">{{ $loop->iteration }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $pregunta->texto }}</strong>
                                                </td>
                                                <td>
                                                                                    <span class="badge badge-{{ $controller->getBadgeColorForType($pregunta->tipo) }}">
                                    {{ $controller->getTypeName($pregunta->tipo) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <code>{{ $pregunta->id }}</code>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Procesar Respuestas
                            </button>
                            <a href="{{ route('encuestas.show', $encuesta->id) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Ver Encuesta
                            </a>
                            <a href="{{ route('carga-masiva.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de Ayuda -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Formato del Archivo de Respuestas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-file-text text-primary"></i> Formato Requerido</h5>
                            <p>El archivo debe contener respuestas con el formato:</p>
                            <pre class="bg-light p-2 rounded">
R_1: Sí, completamente de acuerdo
R_2: Excelente servicio
R_3: Muy satisfecho, 5 estrellas
R_4: Opción A, Opción B, Opción C
                            </pre>
                            <p><strong>Donde:</strong></p>
                            <ul>
                                <li><code>R_X</code> = Número de la pregunta (1, 2, 3, etc.)</li>
                                <li><code>:</code> = Separador obligatorio</li>
                                <li><code>contenido</code> = La respuesta correspondiente</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-exclamation-triangle text-warning"></i> Reglas Importantes</h5>
                            <ul>
                                <li>El número después de R_ debe corresponder al orden de la pregunta</li>
                                <li>Si una pregunta no tiene respuesta, omítela del archivo</li>
                                <li>Para respuestas múltiples, sepáralas con comas</li>
                                <li>El sistema validará la compatibilidad con el tipo de pregunta</li>
                                <li>Las respuestas se asociarán automáticamente a las preguntas</li>
                            </ul>

                            <h6 class="mt-3"><i class="fas fa-check-circle text-success"></i> Ejemplos Válidos</h6>
                            <ul class="small">
                                <li><code>R_1: Juan Pérez</code> (texto corto)</li>
                                <li><code>R_2: Muy satisfecho con el servicio</code> (párrafo)</li>
                                <li><code>R_3: Opción A, Opción B</code> (múltiple)</li>
                                <li><code>R_4: 8</code> (escala)</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.custom-file-label::after {
    content: "Buscar";
}

.card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.badge {
    font-size: 0.875rem;
}

pre {
    font-size: 0.875rem;
    line-height: 1.4;
}

code {
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}
</style>
@stop

@section('js')
<script>
// Mostrar nombre del archivo seleccionado
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = e.target.files[0].name;
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
});

// Validación del formulario
document.querySelector('form').addEventListener('submit', function(e) {
    var archivo = document.getElementById('archivo_respuestas').files[0];

    if (!archivo) {
        e.preventDefault();
        Swal.fire({
            icon: 'warning',
            title: 'Archivo requerido',
            text: 'Por favor selecciona un archivo de respuestas.'
        });
        return;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Procesando respuestas...',
        html: 'Leyendo archivo y validando respuestas...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
});
</script>
@stop
