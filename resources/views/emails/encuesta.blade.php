<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Encuesta Disponible</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin: 20px 0;
        }
        .warning {
            background-color: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Nueva Encuesta Disponible</h1>
            <p>Hola {{ $nombre }}, tienes una nueva encuesta pendiente de completar.</p>
        </div>

        <div class="content">
            <h2>Encuesta: {{ $encuesta }}</h2>

            <div class="info-box">
                <h3>📝 Detalles de la Encuesta</h3>
                <p><strong>Nombre:</strong> {{ $encuesta }}</p>
                <p><strong>Fecha límite:</strong> {{ $fecha_limite }}</p>
                <p><strong>Estado:</strong> Pendiente de completar</p>
            </div>

            <div class="warning">
                <h4>⚠️ Importante</h4>
                <p>Esta encuesta tiene una fecha límite establecida. Por favor, complétala antes de la fecha indicada para que tu respuesta sea considerada válida.</p>
            </div>

            <p>Para acceder a la encuesta y completarla, haz clic en el siguiente botón:</p>

            <div style="text-align: center;">
                <a href="{{ $enlace }}" class="btn">
                    🚀 Comenzar Encuesta
                </a>
            </div>

            <p style="margin-top: 20px;">
                <strong>Enlace directo:</strong><br>
                <a href="{{ $enlace }}" style="color: #007bff; word-break: break-all;">{{ $enlace }}</a>
            </p>
        </div>

        <div class="footer">
            <p>
                <strong>ModuStackEncuestas</strong><br>
                Sistema de Gestión de Encuestas<br>
                Este correo fue enviado automáticamente. No respondas a este mensaje.
            </p>
            <p>
                Si tienes problemas para acceder a la encuesta, contacta al administrador del sistema.
            </p>
        </div>
    </div>
</body>
</html>
