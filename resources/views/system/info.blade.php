@extends('adminlte::page')

@section('title', 'Información del Sistema')

@section('content_header')
    <h1>Información del Sistema</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Configuración de Fechas y Zona Horaria</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>📅 Información de Fechas</h4>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Zona Horaria</strong></td>
                                        <td>{{ $info['fechas']['zona_horaria'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Fecha Actual</strong></td>
                                        <td>{{ $info['fechas']['fecha_actual'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Timestamp</strong></td>
                                        <td>{{ $info['fechas']['timestamp'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>UTC</strong></td>
                                        <td>{{ $info['fechas']['utc'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Local</strong></td>
                                        <td>{{ $info['fechas']['local'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h4>⚙️ Configuración del Sistema</h4>
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Timezone</strong></td>
                                        <td>{{ $info['configuracion']['timezone'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Locale</strong></td>
                                        <td>{{ $info['configuracion']['locale'] }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Debug</strong></td>
                                        <td>{{ $info['configuracion']['debug'] ? 'Activado' : 'Desactivado' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Environment</strong></td>
                                        <td>{{ $info['configuracion']['env'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h4>✅ Pruebas de Validación</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Valor</th>
                                        <th>Estado</th>
                                        <th>Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Hoy</strong></td>
                                        <td>{{ $info['validaciones']['hoy']['fecha'] }}</td>
                                        <td>{!! $info['validaciones']['hoy']['mensaje'] !!}</td>
                                        <td>Fecha actual del sistema</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ayer</strong></td>
                                        <td>{{ $info['validaciones']['ayer']['fecha'] }}</td>
                                        <td>{!! $info['validaciones']['ayer']['mensaje'] !!}</td>
                                        <td>Fecha anterior (debería ser inválida)</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Mañana</strong></td>
                                        <td>{{ $info['validaciones']['manana']['fecha'] }}</td>
                                        <td>{!! $info['validaciones']['manana']['mensaje'] !!}</td>
                                        <td>Fecha futura (debería ser válida)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle"></i> Información Importante</h5>
                                <p><strong>Zona Horaria:</strong> {{ $info['fechas']['zona_horaria'] }}</p>
                                <p><strong>Fecha Actual:</strong> {{ $info['fechas']['fecha_actual'] }}</p>
                                <p><strong>Estado:</strong>
                                    @if($info['validaciones']['hoy']['valida'])
                                        <span class="badge badge-success">✅ Configuración Correcta</span>
                                    @else
                                        <span class="badge badge-danger">❌ Problema de Configuración</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .table td {
        vertical-align: middle;
    }
    .badge {
        font-size: 0.9em;
    }
</style>
@stop
