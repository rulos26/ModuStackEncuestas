@extends('adminlte::page')

@section('title', 'Errores del Módulo')

@section('content_header')
    <h1>Errores del Módulo</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <strong>Últimos errores registrados del módulo (module_error.log)</strong>
    </div>
    <div class="card-body" style="background:#222;color:#eee;font-family:monospace;font-size:13px;max-height:600px;overflow:auto;">
        @if($logContent)
            <pre style="white-space: pre-wrap; word-break: break-all;">{{ $logContent }}</pre>
        @else
            <span class="text-danger">No se encontró el archivo de log de errores del módulo.</span>
        @endif
    </div>
</div>
@endsection
