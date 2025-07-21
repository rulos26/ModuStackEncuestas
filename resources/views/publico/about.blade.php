@extends('adminlte::page')

@section('title', 'About Quantum Metric')

@section('content_header')
    <h1><i class="fas fa-building"></i> About Quantum Metric</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">{{ $empresa->nombre_legal ?? 'Información de la Empresa' }}</h3>
            </div>
            <div class="card-body">
                @if($empresa)
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5"><i class="fas fa-id-card"></i> NIT:</dt>
                            <dd class="col-sm-7">{{ $empresa->nit }}</dd>
                            <dt class="col-sm-5"><i class="fas fa-user-tie"></i> Representante:</dt>
                            <dd class="col-sm-7">{{ $empresa->representante_legal }}</dd>
                            <dt class="col-sm-5"><i class="fas fa-phone"></i> Teléfono:</dt>
                            <dd class="col-sm-7">{{ $empresa->telefono }}</dd>
                            <dt class="col-sm-5"><i class="fas fa-envelope"></i> Email:</dt>
                            <dd class="col-sm-7">{{ $empresa->email }}</dd>
                            <dt class="col-sm-5"><i class="fas fa-map-marker-alt"></i> Dirección:</dt>
                            <dd class="col-sm-7">{{ $empresa->direccion }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5"><i class="fas fa-flag"></i> País:</dt>
                            <dd class="col-sm-7">{{ $empresa->pais->name ?? '-' }}</dd>
                            <dt class="col-sm-5"><i class="fas fa-map"></i> Departamento:</dt>
                            <dd class="col-sm-7">{{ $empresa->departamento->nombre ?? '-' }}</dd>
                            <dt class="col-sm-5"><i class="fas fa-city"></i> Municipio:</dt>
                            <dd class="col-sm-7">{{ $empresa->municipio->nombre ?? '-' }}</dd>
                            <dt class="col-sm-5"><i class="fas fa-calendar"></i> Creación:</dt>
                            <dd class="col-sm-7">{{ $empresa->fecha_creacion ? \Carbon\Carbon::parse($empresa->fecha_creacion)->format('d/m/Y') : '-' }}</dd>
                        </dl>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <strong><i class="fas fa-bullseye"></i> Misión:</strong>
                        <p>{{ $empresa->mision }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-eye"></i> Visión:</strong>
                        <p>{{ $empresa->vision }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong><i class="fas fa-info-circle"></i> Descripción:</strong>
                        <p>{{ $empresa->descripcion }}</p>
                    </div>
                </div>
                @else
                <div class="alert alert-warning text-center">No hay información pública de la empresa registrada.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
