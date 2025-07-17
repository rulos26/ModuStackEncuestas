@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1>Prueba Interna de Usuario</h1>
    <form method="POST" action="{{ route('test.ejecutar') }}">
        @csrf
        <button type="submit" class="btn btn-primary btn-lg mt-3">Ejecutar prueba</button>
    </form>
</div>
@endsection
