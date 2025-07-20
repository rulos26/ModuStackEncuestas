@extends('adminlte::page')

@section('title', 'Información de la Empresa')

@section('content_header')
    <h1><i class="fas fa-building"></i> Información de la Empresa</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-info">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Datos Institucionales</h3>
                <a href="{{ route('empresa.edit') }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Editar</a>
                <a href="{{ route('empresa.export.pdf') }}" class="btn btn-outline-danger btn-sm ml-2"><i class="fas fa-file-pdf"></i> Exportar PDF</a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($empresa)
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Nombre Legal:</dt>
                            <dd class="col-sm-7">{{ $empresa->nombre_legal }}</dd>
                            <dt class="col-sm-5">NIT:</dt>
                            <dd class="col-sm-7">{{ $empresa->nit }}</dd>
                            <dt class="col-sm-5">Representante Legal:</dt>
                            <dd class="col-sm-7">{{ $empresa->representante_legal }}</dd>
                            <dt class="col-sm-5">Teléfono:</dt>
                            <dd class="col-sm-7">{{ $empresa->telefono }}</dd>
                            <dt class="col-sm-5">Correo Electrónico:</dt>
                            <dd class="col-sm-7">{{ $empresa->email }}</dd>
                            <dt class="col-sm-5">Dirección:</dt>
                            <dd class="col-sm-7">{{ $empresa->direccion }}</dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">País:</dt>
                            <dd class="col-sm-7">{{ $empresa->pais->name ?? '-' }}</dd>
                            <dt class="col-sm-5">Departamento:</dt>
                            <dd class="col-sm-7">{{ $empresa->departamento->nombre ?? '-' }}</dd>
                            <dt class="col-sm-5">Municipio:</dt>
                            <dd class="col-sm-7">{{ $empresa->municipio->nombre ?? '-' }}</dd>
                            <dt class="col-sm-5">Fecha de Creación:</dt>
                            <dd class="col-sm-7">{{ $empresa->fecha_creacion ? \Carbon\Carbon::parse($empresa->fecha_creacion)->format('d/m/Y') : '-' }}</dd>
                        </dl>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Misión:</strong>
                        <p>{{ $empresa->mision }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong>Visión:</strong>
                        <p>{{ $empresa->vision }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong>Descripción:</strong>
                        <p>{{ $empresa->descripcion }}</p>
                    </div>
                </div>
                @else
                <div class="alert alert-warning text-center">
                    No hay información registrada. <a href="{{ route('empresa.create') }}" class="btn btn-success btn-sm ml-2"><i class="fas fa-plus"></i> Registrar Empresa</a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
