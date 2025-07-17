@extends('adminlte::page')

@section('content')
<div class="container mt-5">
    <h1>Resultado de la Prueba</h1>
    <div class="alert {{ $success ? 'alert-success' : 'alert-danger' }} mt-4">
        <strong>{{ $mensaje }}</strong>
        @if(!$success && isset($error))
            <hr>
            <div><b>Error:</b> {{ $error['mensaje'] }}</div>
            <div><b>Línea:</b> {{ $error['linea'] }}</div>
            <div><b>Archivo:</b> {{ $error['archivo'] }}</div>
        @endif
    </div>
    <a href="{{ route('test.index') }}" class="btn btn-secondary">Volver</a>
</div>
@endsection
