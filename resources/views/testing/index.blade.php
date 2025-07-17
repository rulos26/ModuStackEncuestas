@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Pruebas Automatizadas</h1>
    <form method="POST" action="{{ route('testing.run') }}">
        @csrf
        <div class="mb-3">
            <label for="file" class="form-label">Archivo o filtro de test (opcional):</label>
            <input type="text" name="file" id="file" class="form-control" placeholder="Ej: UserRoleFlowTest">
        </div>
        <button type="submit" class="btn btn-primary">Ejecutar pruebas</button>
    </form>
    @isset($output)
        <hr>
        <h3>Resultado:</h3>
        <pre style="background:#222;color:#eee;padding:1em;border-radius:8px;">{{ $output }}</pre>
    @endisset
</div>
@endsection
