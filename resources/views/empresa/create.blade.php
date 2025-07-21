@extends('adminlte::page')

@section('title', 'Registrar Información de la Empresa')

@section('content_header')
    <h1><i class="fas fa-building"></i> Registrar Información de la Empresa</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Datos Institucionales</h3>
            </div>
            <form method="POST" action="{{ route('empresa.store') }}" id="empresaForm">
                @csrf
                <div class="card-body">
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
                                <input type="text" name="nombre_legal" class="form-control" value="{{ old('nombre_legal') }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="nit">NIT</label>
                                <input type="text" name="nit" class="form-control" value="{{ old('nit') }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="representante_legal">Representante Legal</label>
                                <input type="text" name="representante_legal" class="form-control" value="{{ old('representante_legal') }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" name="telefono" class="form-control" value="{{ old('telefono') }}" required maxlength="30">
                            </div>
                            <div class="form-group">
                                <label for="email">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required maxlength="255">
                            </div>
                            <div class="form-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" name="direccion" class="form-control" value="{{ old('direccion') }}" required maxlength="255">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pais_id">País</label>
                                <select name="pais_id" id="pais_id" class="form-control" required>
                                    <option value="">Seleccione un país</option>
                                    @foreach($paises as $pais)
                                        <option value="{{ $pais->id }}" {{ old('pais_id') == $pais->id ? 'selected' : '' }}>{{ $pais->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="departamento_id">Departamento</label>
                                <select name="departamento_id" id="departamento_id" class="form-control" required disabled>
                                    <option value="">Seleccione un departamento</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="municipio_id">Municipio</label>
                                <select name="municipio_id" id="municipio_id" class="form-control" required disabled>
                                    <option value="">Seleccione un municipio</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="fecha_creacion">Fecha de Creación</label>
                                <input type="date" name="fecha_creacion" class="form-control" value="{{ old('fecha_creacion') }}">
                            </div>
                            <div class="form-group">
                                <label for="mision">Misión</label>
                                <textarea name="mision" class="form-control" rows="2">{{ old('mision') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="vision">Visión</label>
                                <textarea name="vision" class="form-control" rows="2">{{ old('vision') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="descripcion">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
    window.baseUrl = "{{ url('/') }}";
</script>
<script>
$(document).ready(function() {
    // Cargar departamentos al seleccionar país
    $('#pais_id').change(function() {
        var paisId = $(this).val();
        var urlDep = window.baseUrl + '/empresa/departamentos/' + paisId;
        $('#departamento_id').prop('disabled', true).html('<option value="">Cargando...</option>');
        $('#municipio_id').prop('disabled', true).html('<option value="">Seleccione un municipio</option>');
        if (paisId) {
            console.log('Intentando acceder a la URL de departamentos:', urlDep);
            $.ajax({
                url: urlDep,
                method: 'GET',
                success: function(data) {
                    var options = '<option value="">Seleccione un departamento</option>';
                    data.forEach(function(dep) {
                        options += '<option value="' + dep.id + '">' + dep.nombre + '</option>';
                    });
                    $('#departamento_id').html(options).prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX departamentos:', {
                        url: urlDep,
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    alert('Error al cargar departamentos. Revisa la consola para más detalles.');
                }
            });
        } else {
            $('#departamento_id').html('<option value="">Seleccione un departamento</option>').prop('disabled', true);
        }
    });
    // Cargar municipios al seleccionar departamento
    $('#departamento_id').change(function() {
        var depId = $(this).val();
        var urlMun = window.baseUrl + '/empresa/municipios/' + depId;
        $('#municipio_id').prop('disabled', true).html('<option value="">Cargando...</option>');
        if (depId) {
            console.log('Intentando acceder a la URL de municipios:', urlMun);
            $.ajax({
                url: urlMun,
                method: 'GET',
                success: function(data) {
                    var options = '<option value="">Seleccione un municipio</option>';
                    data.forEach(function(mun) {
                        options += '<option value="' + mun.id + '">' + mun.nombre + '</option>';
                    });
                    $('#municipio_id').html(options).prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX municipios:', {
                        url: urlMun,
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    alert('Error al cargar municipios. Revisa la consola para más detalles.');
                }
            });
        } else {
            $('#municipio_id').html('<option value="">Seleccione un municipio</option>').prop('disabled', true);
        }
    });
});
</script>
@stop
