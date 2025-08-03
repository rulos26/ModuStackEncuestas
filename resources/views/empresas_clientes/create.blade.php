@extends('adminlte::page')

@section('title', 'Nueva Empresa Cliente')

@section('content_header')
    <h1>Nueva Empresa Cliente</h1>
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
        <form action="{{ route('empresas_clientes.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="nombre">Nombre del Cliente</label>
                <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre') }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="nit">NIT</label>
                <input type="text" name="nit" id="nit" class="form-control" value="{{ old('nit') }}" required maxlength="255">
            </div>
            <div class="form-group">
                <label for="telefono">Teléfono de la Empresa</label>
                <input type="text" name="telefono" id="telefono" class="form-control" value="{{ old('telefono') }}" maxlength="30">
            </div>
            <div class="form-group">
                <label for="correo_electronico">Correo Institucional</label>
                <input type="email" name="correo_electronico" id="correo_electronico" class="form-control" value="{{ old('correo_electronico') }}" maxlength="255">
            </div>
            <div class="form-group">
                <label for="direccion">Dirección Local</label>
                <input type="text" name="direccion" id="direccion" class="form-control" value="{{ old('direccion') }}" maxlength="255">
            </div>
            <div class="form-group">
                <label for="contacto">Representante Legal</label>
                <input type="text" name="contacto" id="contacto" class="form-control" value="{{ old('contacto') }}" maxlength="255">
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar</button>
            <a href="{{ route('empresas_clientes.index') }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
</div>
@endsection
