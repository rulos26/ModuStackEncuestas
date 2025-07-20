<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información de la Empresa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 13px; }
        .header { text-align: center; margin-bottom: 20px; }
        .title { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .section { margin-bottom: 15px; }
        .label { font-weight: bold; width: 180px; display: inline-block; }
        .value { display: inline-block; }
        .row { margin-bottom: 5px; }
        .divider { border-bottom: 1px solid #888; margin: 15px 0; }
        .subsection { margin-left: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Información Institucional de la Empresa</div>
        <div>{{ $empresa->nombre_legal }}</div>
    </div>
    <div class="section">
        <div class="row"><span class="label">NIT:</span> <span class="value">{{ $empresa->nit }}</span></div>
        <div class="row"><span class="label">Representante Legal:</span> <span class="value">{{ $empresa->representante_legal }}</span></div>
        <div class="row"><span class="label">Teléfono:</span> <span class="value">{{ $empresa->telefono }}</span></div>
        <div class="row"><span class="label">Correo Electrónico:</span> <span class="value">{{ $empresa->email }}</span></div>
        <div class="row"><span class="label">Dirección:</span> <span class="value">{{ $empresa->direccion }}</span></div>
        <div class="row"><span class="label">País:</span> <span class="value">{{ $empresa->pais->name ?? '-' }}</span></div>
        <div class="row"><span class="label">Departamento:</span> <span class="value">{{ $empresa->departamento->nombre ?? '-' }}</span></div>
        <div class="row"><span class="label">Municipio:</span> <span class="value">{{ $empresa->municipio->nombre ?? '-' }}</span></div>
        <div class="row"><span class="label">Fecha de Creación:</span> <span class="value">{{ $empresa->fecha_creacion ? \Carbon\Carbon::parse($empresa->fecha_creacion)->format('d/m/Y') : '-' }}</span></div>
    </div>
    <div class="divider"></div>
    <div class="section">
        <div class="label">Misión:</div>
        <div class="subsection">{{ $empresa->mision }}</div>
    </div>
    <div class="section">
        <div class="label">Visión:</div>
        <div class="subsection">{{ $empresa->vision }}</div>
    </div>
    <div class="section">
        <div class="label">Descripción:</div>
        <div class="subsection">{{ $empresa->descripcion }}</div>
    </div>
    <div class="divider"></div>
    <div style="text-align:right; font-size:11px; color:#888;">Generado por el sistema el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
