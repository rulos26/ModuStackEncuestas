<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresa Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; }
        h1 { text-align: center; }
        dl { width: 100%; overflow: hidden; }
        dt { float: left; width: 30%; font-weight: bold; }
        dd { float: left; width: 70%; margin: 0 0 10px 0; }
        .clearfix { clear: both; }
    </style>
</head>
<body>
    <h1>Empresa Cliente</h1>
    <dl>
        <dt>Nombre</dt>
        <dd>{{ $empresas_cliente->nombre }}</dd>
        <dt>NIT</dt>
        <dd>{{ $empresas_cliente->nit }}</dd>
        <dt>Teléfono</dt>
        <dd>{{ $empresas_cliente->telefono }}</dd>
        <dt>Correo electrónico</dt>
        <dd>{{ $empresas_cliente->correo_electronico }}</dd>
        <dt>Dirección</dt>
        <dd>{{ $empresas_cliente->direccion }}</dd>
        <dt>Nombre del Contacto</dt>
        <dd>{{ $empresas_cliente->contacto }}</dd>
        <dt>Cargo del Contacto</dt>
        <dd>{{ $empresas_cliente->cargo_contacto }}</dd>
    </dl>
    <div class="clearfix"></div>
</body>
</html>
