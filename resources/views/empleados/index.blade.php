@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <span>Lista de Empleados</span>
                    <div>
                        <a href="{{ route('empleados.create') }}" class="btn btn-success btn-sm">Registrar Empleado</a>
                        <a href="{{ route('empleados.import.form') }}" class="btn btn-info btn-sm">Importar Archivo</a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Cargo</th>
                                <th>Teléfono</th>
                                <th>Correo electrónico</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($empleados as $empleado)
                                <tr>
                                    <td>{{ $empleado->id }}</td>
                                    <td>{{ $empleado->nombre }}</td>
                                    <td>{{ $empleado->cargo }}</td>
                                    <td>{{ $empleado->telefono }}</td>
                                    <td>{{ $empleado->correo_electronico }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay empleados registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
