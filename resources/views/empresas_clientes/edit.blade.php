@extends('adminlte::page')

@section('title', 'Editar Empresa Cliente')

@section('content_header')
    <h1>Editar Empresa Cliente</h1>
@endsection

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="card">
    <div class="card-body">
        <form action="{{ route('empresas_clientes.update', $empresas_cliente) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $empresas_cliente->nombre) }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="nit">NIT</label>
                <input type="text" name="nit" id="nit" class="form-control" value="{{ old('nit', $empresas_cliente->nit) }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono', $empresas_cliente->telefono) }}" maxlength="30">
            </div>
            <div class="form-group">
                <label for="correo_electronico">Correo electrónico</label>
                <input type="email" name="correo_electronico" id="correo_electronico" class="form-control" value="{{ old('correo_electronico', $empresas_cliente->correo_electronico) }}" maxlength="255">
            </div>
            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" name="direccion" id="direccion" class="form-control" value="{{ old('direccion', $empresas_cliente->direccion) }}" maxlength="255">
            </div>
            <div class="form-group">
                <label for="contacto">Nombre del Contacto</label>
                <input type="text" name="contacto" id="contacto" class="form-control" value="{{ old('contacto', $empresas_cliente->contacto) }}" maxlength="255">
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar Cambios</button>
            <a href="{{ route('empresas_clientes.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
