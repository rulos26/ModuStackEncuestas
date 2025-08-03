@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">Detalles del Empleado</div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8">{{ $empleado->nombre }}</dd>

                        <dt class="col-sm-4">Teléfono</dt>
                        <dd class="col-sm-8">{{ $empleado->telefono }}</dd>
                        <dt class="col-sm-4">Correo electrónico</dt>
                        <dd class="col-sm-8">{{ $empleado->correo_electronico }}</dd>
                    </dl>
                    <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Volver</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
