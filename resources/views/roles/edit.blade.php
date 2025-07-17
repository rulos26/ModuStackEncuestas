@extends('adminlte::page')
@section('title', 'Editar Rol')
@section('content_header')
    <h1><i class="fas fa-user-shield"></i> Editar Rol</h1>
@stop
@section('content')
@include('roles.form', ['route' => route('roles.update', $role), 'method' => 'PUT', 'role' => $role, 'permissions' => $permissions, 'rolePermissions' => $rolePermissions])
@stop
