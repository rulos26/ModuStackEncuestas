<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresa Cliente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.4;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 18px;
        }
        .info-row {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .info-label {
            font-weight: bold;
            color: #333;
            display: inline-block;
            width: 150px;
        }
        .info-value {
            color: #666;
            display: inline-block;
        }
        .divider {
            border-bottom: 1px solid #ccc;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <h1>INFORMACIÓN DE EMPRESA CLIENTE</h1>

    <div class="info-row">
        <span class="info-label">Nombre del Cliente:</span>
        <span class="info-value">{{ $empresas_cliente->nombre ?? 'No especificado' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">NIT:</span>
        <span class="info-value">{{ $empresas_cliente->nit ?? 'No especificado' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Teléfono de la Empresa:</span>
        <span class="info-value">{{ $empresas_cliente->telefono ?? 'No especificado' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Correo Institucional:</span>
        <span class="info-value">{{ $empresas_cliente->correo_electronico ?? 'No especificado' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Dirección Local:</span>
        <span class="info-value">{{ $empresas_cliente->direccion ?? 'No especificado' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Representante Legal:</span>
        <span class="info-value">{{ $empresas_cliente->contacto ?? 'No especificado' }}</span>
    </div>

    <div class="divider"></div>

    <div class="footer">
        <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
