@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">Registrar Empleado Cliente</div>
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
                    <form method="POST" action="{{ route('empleados.store') }}">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="nombre">Empleado Cliente</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="255" pattern="^(?:\b\w+\b\s?){1,10}$" title="Máximo 10 palabras">
                            <small class="form-text text-muted">Máximo 10 palabras y 255 caracteres.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="empresa_id">Empresa Cliente</label>
                            <select class="form-control" id="empresa_id" name="empresa_id">
                                <option value="">Seleccione una empresa</option>
                                @foreach($empresas as $empresa)
                                    <option value="{{ $empresa->id }}" {{ old('empresa_id') == $empresa->id ? 'selected' : '' }}>
                                        {{ $empresa->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Seleccione la empresa a la que pertenece el empleado.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="telefono">Teléfono</label>
                            <input type="number" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" required pattern="^[0-9]{10}$" maxlength="10" minlength="10" title="Debe ser un número de 10 dígitos">
                            <small class="form-text text-muted">Debe ser un número de 10 dígitos.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="correo_electronico">Correo electrónico</label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico') }}" required maxlength="255">
                            <small class="form-text text-muted">Debe ser un correo válido, único y máximo 255 caracteres.</small>
                        </div>
                        <button type="submit" class="btn btn-success">Registrar</button>
                        <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Volver</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
