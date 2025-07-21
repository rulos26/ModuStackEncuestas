@extends('adminlte::page')

@section('title', 'Nueva Política de Privacidad')

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Nueva Política de Privacidad</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Registrar Política</h3>
            </div>
            <form method="POST" action="{{ route('politicas-privacidad.store') }}" id="formPolitica">
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
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo') }}" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="version">Versión</label>
                        <input type="text" name="version" class="form-control" value="{{ old('version') }}" required maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="fecha_publicacion">Fecha de Publicación</label>
                        <input type="date" name="fecha_publicacion" class="form-control" value="{{ old('fecha_publicacion') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select name="estado" class="form-control" required>
                            <option value="1" {{ old('estado', 1) == 1 ? 'selected' : '' }}>Activa</option>
                            <option value="0" {{ old('estado') === '0' ? 'selected' : '' }}>Inactiva</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="contenido">Contenido</label>
                        <textarea name="contenido" id="contenido" class="form-control summernote" rows="8" required>{{ old('contenido') }}</textarea>
                    </div>
                    <div class="form-group text-right">
                        <button type="button" class="btn btn-info" id="btnPreview"><i class="fas fa-eye"></i> Previsualizar</button>
                    </div>
                    <div id="previewContainer" style="display:none;">
                        <div class="alert alert-secondary"><strong>Previsualización:</strong></div>
                        <div class="card">
                            <div class="card-body" id="previewContent"></div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('politicas-privacidad.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<!-- Summernote CDN -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script>
$(document).ready(function() {
    $('.summernote').summernote({
        height: 250,
        lang: 'es-ES',
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    $('#btnPreview').click(function() {
        var html = $('#contenido').summernote('code');
        $('#previewContent').html(html);
        $('#previewContainer').show();
        $('html, body').animate({ scrollTop: $('#previewContainer').offset().top - 100 }, 400);
    });
});
</script>
@stop
