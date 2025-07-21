@extends('adminlte::page')

@section('title', 'Detalle de Política de Privacidad')

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Detalle de Política de Privacidad</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">{{ $politica->titulo }}</h3>
                <div class="float-right">
                    <span class="badge badge-{{ $politica->estado ? 'success' : 'secondary' }}">
                        {{ $politica->estado ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Versión:</dt>
                    <dd class="col-sm-9">{{ $politica->version }}</dd>
                    <dt class="col-sm-3">Fecha de Publicación:</dt>
                    <dd class="col-sm-9">{{ $politica->fecha_publicacion ? $politica->fecha_publicacion->format('d/m/Y') : '-' }}</dd>
                </dl>
                <hr>
                <div>
                    {!! $politica->contenido !!}
                </div>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('politicas-privacidad.index') }}" class="btn btn-secondary">Volver</a>
                <a href="{{ route('politicas-privacidad.edit', $politica) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>
            </div>
        </div>
    </div>
</div>
@stop
