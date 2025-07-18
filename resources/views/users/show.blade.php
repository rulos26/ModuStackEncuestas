@extends('adminlte::page')

@section('title', 'Detalle de Usuario')

@section('content_header')
    <h1>Detalle de Usuario</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-3 text-center">
            @if($user->roles->count())
                <span class="badge bg-info" style="font-size:1.1em;">{{ $user->roles->first()->name }}</span>
            @endif
        </div>
        <dl class="row">
            <dt class="col-sm-3">Nombre</dt>
            <dd class="col-sm-9">{{ $user->name }}</dd>
            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9">{{ $user->email }}</dd>
            <dt class="col-sm-3">Email verificado</dt>
            <dd class="col-sm-9">{{ $user->email_verified_at ? $user->email_verified_at->format('d/m/Y H:i') : 'No' }}</dd>
            <dt class="col-sm-3">Creado</dt>
            <dd class="col-sm-9">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
            <dt class="col-sm-3">Actualizado</dt>
            <dd class="col-sm-9">{{ $user->updated_at->format('d/m/Y H:i') }}</dd>
        </dl>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Volver</a>
    </div>
</div>
@endsection
