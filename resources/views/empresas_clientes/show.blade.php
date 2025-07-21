@extends('adminlte::page')

@section('title', 'Detalle Empresa Cliente')

@section('content_header')
    <h1>Detalle Empresa Cliente</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Nombre</dt>
            <dd class="col-sm-9">{{ $empresas_cliente->nombre }}</dd>
            <dt class="col-sm-3">NIT</dt>
            <dd class="col-sm-9">{{ $empresas_cliente->nit }}</dd>
            <dt class="col-sm-3">Teléfono</dt>
            <dd class="col-sm-9">{{ $empresas_cliente->telefono }}</dd>
            <dt class="col-sm-3">Correo electrónico</dt>
            <dd class="col-sm-9">{{ $empresas_cliente->correo_electronico }}</dd>
            <dt class="col-sm-3">Dirección</dt>
            <dd class="col-sm-9">{{ $empresas_cliente->direccion }}</dd>
            <dt class="col-sm-3">Nombre del Contacto</dt>
            <dd class="col-sm-9">{{ $empresas_cliente->contacto }}</dd>
            <dt class="col-sm-3">Cargo del Contacto</dt>
            <dd class="col-sm-9">{{ $empresas_cliente->cargo_contacto }}</dd>
        </dl>
        <a href="{{ route('empresas_clientes.index') }}" class="btn btn-secondary">Volver</a>
        <a href="{{ route('empresas_clientes.edit', $empresas_cliente) }}" class="btn btn-warning">Editar</a>
        <a href="{{ route('empresas_clientes.exportPdf', $empresas_cliente) }}" class="btn btn-secondary">Exportar PDF</a>
    </div>
</div>
@endsection
