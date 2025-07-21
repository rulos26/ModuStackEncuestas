@extends('adminlte::page')

@section('title', $politica ? $politica->titulo : 'Política de Privacidad')

@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Política de Privacidad</h1>
@stop

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">{{ $politica ? $politica->titulo : 'Política de Privacidad' }}</h3>
                @if($politica)
                <div class="float-right">
                    <span class="badge badge-success">Versión {{ $politica->version }}</span>
                    <span class="ml-2 text-muted">Publicada: {{ $politica->fecha_publicacion ? $politica->fecha_publicacion->format('d/m/Y') : '-' }}</span>
                </div>
                @endif
            </div>
            <div class="card-body">
                @if($politica)
                    {!! $politica->contenido !!}
                @else
                    <div class="alert alert-warning text-center">No hay una política de privacidad activa en este momento.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@stop
