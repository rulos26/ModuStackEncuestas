@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">Editar Empleado</div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('empleados.update', $empleado->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="form-group mb-3">
                            <label for="nombre">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $empleado->nombre) }}" required maxlength="255" pattern="^(?:\b\w+\b\s?){1,10}$" title="Máximo 10 palabras">
                            <small class="form-text text-muted">Máximo 10 palabras y 255 caracteres.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="telefono">Teléfono</label>
                            <input type="number" class="form-control" id="telefono" name="telefono" value="{{ old('telefono', $empleado->telefono) }}" required pattern="^[0-9]{10}$" maxlength="10" minlength="10" title="Debe ser un número de 10 dígitos">
                            <small class="form-text text-muted">Debe ser un número de 10 dígitos.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="correo_electronico">Correo electrónico</label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico', $empleado->correo_electronico) }}" required maxlength="255">
                            <small class="form-text text-muted">Debe ser un correo válido, único y máximo 255 caracteres.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
