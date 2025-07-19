@extends('adminlte::page')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 mb-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">Plantilla Excel</div>
                <div class="card-body text-center">
                    <p>Descarga una plantilla en formato Excel para la carga masiva de empleados.</p>
                    <a href="{{ route('empleados.plantilla.excel') }}" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> Descargar plantilla Excel
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">Plantilla CSV</div>
                <div class="card-body text-center">
                    <p>Descarga una plantilla en formato CSV para la carga masiva de empleados.</p>
                    <a href="{{ route('empleados.plantilla.csv') }}" class="btn btn-info">
                        <i class="fas fa-file-csv"></i> Descargar plantilla CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Volver al listado de empleados</a>
        </div>
    </div>
</div>
@endsection
