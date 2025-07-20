@extends('adminlte::page')

@section('title', 'Detalle de Municipio')

@section('content_header')
    <h1><i class="fas fa-map-marker-alt"></i> Detalle de Municipio</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Información del Municipio</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">ID:</dt>
                    <dd class="col-sm-8">{{ $municipio->id }}</dd>
                    <dt class="col-sm-4">Nombre:</dt>
                    <dd class="col-sm-8">{{ $municipio->nombre }}</dd>
                    <dt class="col-sm-4">Departamento:</dt>
                    <dd class="col-sm-8">{{ $municipio->departamento->nombre ?? '-' }}</dd>
                    <dt class="col-sm-4">Estado:</dt>
                    <dd class="col-sm-8">
                        @if($municipio->estado)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Inactivo</span>
                        @endif
                    </dd>
                </dl>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('municipios.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ route('municipios.edit', $municipio) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>
            </div>
        </div>
    </div>
</div>
@stop
