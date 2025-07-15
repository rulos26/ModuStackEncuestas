@extends('adminlte::page')

@section('title', 'Logs del Sistema')

@section('content_header')
    <h1>Logs del Sistema</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <strong>Últimos registros del sistema (laravel.log)</strong>
    </div>
    <div class="card-body" style="background:#222;color:#eee;font-family:monospace;font-size:13px;max-height:600px;overflow:auto;">
        @if($logContent)
            <pre style="white-space: pre-wrap; word-break: break-all;">{{ $logContent }}</pre>
        @else
            <span class="text-danger">No se encontró el archivo de log.</span>
        @endif
    </div>
</div>
@endsection
