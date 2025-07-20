@extends('adminlte::page')

@section('title', 'Detalle de Departamento')

@section('content_header')
    <h1><i class="fas fa-map"></i> Detalle de Departamento</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Información del Departamento</h3>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">ID:</dt>
                    <dd class="col-sm-8">{{ $departamento->id }}</dd>
                    <dt class="col-sm-4">Nombre:</dt>
                    <dd class="col-sm-8">{{ $departamento->nombre }}</dd>
                    <dt class="col-sm-4">País:</dt>
                    <dd class="col-sm-8">{{ $departamento->pais->name ?? '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('departamentos.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ route('departamentos.edit', $departamento) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>
            </div>
        </div>
    </div>
</div>
@stop
