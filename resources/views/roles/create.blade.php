@extends('adminlte::page')
@section('title', 'Crear Rol')
@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Crear Rol</h1>
@stop
@section('content')
@include('roles.form', ['route' => route('roles.store'), 'method' => 'POST', 'role' => null, 'permissions' => $permissions, 'rolePermissions' => []])
@stop
