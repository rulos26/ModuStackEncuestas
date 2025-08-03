@extends('adminlte::page')

@section('title', 'Lista de Empleados')

@section('content_header')
    <h1>Lista de Empleados</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Empleados</h3>
                    <div class="card-tools">
                        <a href="{{ route('empleados.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Empleado
                        </a>
                        <a href="{{ route('empleados.import.form') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-upload"></i> Importar
                        </a>
                        <a href="{{ route('empleados.plantillas') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download"></i> Plantillas
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table id="empleados-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="10%">ID</th>
                                    <th width="30%">Nombre</th>
                                    <th width="20%">Teléfono</th>
                                    <th width="25%">Correo Electrónico</th>
                                    <th width="15%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($empleados as $empleado)
                                    <tr>
                                        <td>{{ $empleado->id }}</td>
                                        <td>{{ $empleado->nombre }}</td>
                                        <td>{{ $empleado->telefono }}</td>
                                        <td>{{ $empleado->correo_electronico }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('empleados.show', $empleado->id) }}"
                                                   class="btn btn-info btn-sm"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('empleados.edit', $empleado->id) }}"
                                                   class="btn btn-warning btn-sm"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('empleados.destroy', $empleado->id) }}"
                                                      method="POST"
                                                      style="display: inline;"
                                                      onsubmit="return confirm('¿Está seguro de eliminar este empleado?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap4.min.css">
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#empleados-table').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        order: [[0, 'asc']],
        columnDefs: [
            {
                targets: 4, // Columna de acciones
                orderable: false,
                searchable: false,
                responsivePriority: 1
            },
            {
                targets: [0, 2], // ID y Teléfono
                responsivePriority: 2
            }
        ]
    });
});
</script>
@stop
