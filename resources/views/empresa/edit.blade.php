@extends('adminlte::page')

@section('title', 'Editar Información de la Empresa')

@section('content_header')
    <h1><i class="fas fa-building"></i> Editar Información de la Empresa</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Datos Institucionales</h3>
            </div>
            <form method="POST" action="{{ route('empresa.update') }}" id="empresaForm">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_legal">Nombre Legal</label>
                                <input type="text" name="nombre_legal" class="form-control" value="{{ old('nombre_legal', $empresa->nombre_legal) }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="nit">NIT</label>
                                <input type="text" name="nit" class="form-control" value="{{ old('nit', $empresa->nit) }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="representante_legal">Representante Legal</label>
                                <input type="text" name="representante_legal" class="form-control" value="{{ old('representante_legal', $empresa->representante_legal) }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono', $empresa->telefono) }}" required maxlength="30">
                            </div>
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $empresa->email) }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{ old('direccion', $empresa->direccion) }}" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pais_id">País</label>
                                <select name="pais_id" id="pais_id" class="form-control" required>
                                    <option value="">Seleccione un país</option>
                                    @foreach($paises as $pais)
                                        <option value="{{ $pais->id }}" {{ old('pais_id', $empresa->pais_id) == $pais->id ? 'selected' : '' }}>{{ $pais->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="departamento_id">Departamento</label>
                                <select name="departamento_id" id="departamento_id" class="form-control" required>
                                    <option value="">Seleccione un departamento</option>
                                    @foreach($departamentos as $dep)
                                        <option value="{{ $dep->id }}" {{ old('departamento_id', $empresa->departamento_id) == $dep->id ? 'selected' : '' }}>{{ $dep->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="municipio_id">Municipio</label>
                                <select name="municipio_id" id="municipio_id" class="form-control" required>
                                    <option value="">Seleccione un municipio</option>
                                    @foreach($municipios as $mun)
                                        <option value="{{ $mun->id }}" {{ old('municipio_id', $empresa->municipio_id) == $mun->id ? 'selected' : '' }}>{{ $mun->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="fecha_creacion">Fecha de Creación</label>
                                <input type="date" name="fecha_creacion" class="form-control" value="{{ old('fecha_creacion', $empresa->fecha_creacion) }}">
                            </div>
                            <div class="form-group">
                                <label for="mision">Misión</label>
                                <textarea name="mision" class="form-control" rows="2">{{ old('mision', $empresa->mision) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="vision">Visión</label>
                                <textarea name="vision" class="form-control" rows="2">{{ old('vision', $empresa->vision) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $empresa->descripcion) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Cargar departamentos al seleccionar país
    $('#pais_id').change(function() {
        var paisId = $(this).val();
        $('#departamento_id').prop('disabled', true).html('<option value="">Cargando...</option>');
        $('#municipio_id').prop('disabled', true).html('<option value="">Seleccione un municipio</option>');
        if (paisId) {
            $.get('/empresa/departamentos/' + paisId, function(data) {
                var options = '<option value="">Seleccione un departamento</option>';
                data.forEach(function(dep) {
                    options += '<option value="' + dep.id + '">' + dep.nombre + '</option>';
                });
                $('#departamento_id').html(options).prop('disabled', false);
            });
        } else {
            $('#departamento_id').html('<option value="">Seleccione un departamento</option>').prop('disabled', true);
        }
    });
    // Cargar municipios al seleccionar departamento
    $('#departamento_id').change(function() {
        var depId = $(this).val();
        $('#municipio_id').prop('disabled', true).html('<option value="">Cargando...</option>');
        if (depId) {
            $.get('/empresa/municipios/' + depId, function(data) {
                var options = '<option value="">Seleccione un municipio</option>';
                data.forEach(function(mun) {
                    options += '<option value="' + mun.id + '">' + mun.nombre + '</option>';
                });
                $('#municipio_id').html(options).prop('disabled', false);
            });
        } else {
            $('#municipio_id').html('<option value="">Seleccione un municipio</option>').prop('disabled', true);
        }
    });
});
</script>
@stop
