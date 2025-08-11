@extends('adminlte::page')

@section('title', 'Lista de Encuestas')

@section('content_header')
    <h1>Lista de Encuestas</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gestión de Encuestas</h3>
                    <div class="card-tools">
                        <a href="{{ route('eliminacion-masiva') }}" class="btn btn-warning btn-sm mr-2">
                            <i class="fas fa-trash-alt"></i> Eliminación Masiva
                        </a>
                        <a href="{{ route('encuestas.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Nueva Encuesta
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
                        <table id="encuestas-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="25%">Título</th>
                                    <th width="15%">Empresa</th>
                                    <th width="15%">Usuario</th>
                                    <th width="10%">Estado</th>
                                    <th width="30%">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($encuestas as $index => $encuesta)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $encuesta->titulo }}</strong>
                                            @if($encuesta->fecha_fin)
                                                <br><small class="text-muted">Válida hasta: {{ $encuesta->fecha_fin->format('d/m/Y') }}</small>
                                            @endif
                                            @if($encuesta->preguntas->count() > 0)
                                                <br><small class="text-info">{{ $encuesta->preguntas->count() }} pregunta(s)</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $encuesta->empresa->nombre ?? 'Sin empresa' }}
                                            @if($encuesta->empresa)
                                                <br><small class="text-muted">{{ $encuesta->empresa->nit ?? '' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $encuesta->user->name ?? 'Sin usuario' }}
                                            @if($encuesta->user)
                                                <br><small class="text-muted">{{ $encuesta->user->email ?? '' }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            {!! App\Helpers\EstadoHelper::getBadgeHtml($encuesta->estado) !!}
                                            @if($encuesta->created_at)
                                                <br><small class="text-muted">Creada: {{ $encuesta->created_at->format('d/m/Y') }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('encuestas.show', $encuesta->id) }}"
                                                   class="btn btn-info btn-sm"
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('encuestas.edit', $encuesta->id) }}"
                                                   class="btn btn-warning btn-sm"
                                                   title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('encuestas.preview', $encuesta->id) }}"
                                                   class="btn btn-secondary btn-sm"
                                                   title="Vista previa">
                                                    <i class="fas fa-search"></i>
                                                </a>
                                                <a href="{{ route('encuestas.publica', $encuesta->slug) }}"
                                                   class="btn btn-success btn-sm"
                                                   title="Vista pública"
                                                   target="_blank">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                                <form action="{{ route('encuestas.clone', $encuesta->id) }}"
                                                      method="POST"
                                                      style="display: inline;"
                                                      onsubmit="return confirm('¿Deseas clonar esta encuesta?');">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-dark btn-sm"
                                                            title="Clonar encuesta">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>
                                                <a href="{{ route('encuestas.confirmar-eliminacion', $encuesta->id) }}"
                                                   class="btn btn-danger btn-sm"
                                                   title="Eliminar encuesta">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
<style>
    .badge {
        font-size: 0.8em;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-group .btn {
        margin-right: 2px;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .text-info {
        color: #17a2b8 !important;
    }
    .text-muted {
        color: #6c757d !important;
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .table-responsive {
        border-radius: 0.25rem;
    }
</style>
@stop

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/responsive.bootstrap4.min.js"></script>

<script>
$(document).ready(function() {
    $('#encuestas-table').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
        order: [[0, 'asc']],
        columnDefs: [
            {
                targets: 5, // Columna de acciones
                orderable: false,
                searchable: false,
                responsivePriority: 1
            },
            {
                targets: [0, 4], // ID y Estado
                responsivePriority: 2
            }
        ],
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function () {
            // Personalizar el buscador
            $('.dataTables_filter input').attr('placeholder', 'Buscar encuestas...');
        }
    });
});
</script>
@stop
