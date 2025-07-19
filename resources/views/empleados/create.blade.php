@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">Registrar Empleado</div>
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
                            <label for="nombre">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="cargo">Cargo</label>
                            <input type="text" class="form-control" id="cargo" name="cargo" value="{{ old('cargo') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="telefono">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono" value="{{ old('telefono') }}" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="correo_electronico">Correo electrónico</label>
                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" value="{{ old('correo_electronico') }}" required>
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
