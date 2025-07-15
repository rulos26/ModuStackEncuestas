{{--
MÓDULO DE CONFIGURACIÓN DE IMÁGENES
-----------------------------------

Este módulo permite gestionar y personalizar las imágenes principales del sistema desde el panel de administración.

**Propósito:**
- Permitir al administrador subir y cambiar imágenes como: logo, login, dashboard, spinner y favicon.
- Las imágenes personalizadas reemplazan las de AdminLTE por defecto en todo el sistema.

**Rutas principales:**
- GET  /settings/images   → Panel de configuración de imágenes
- POST /settings/images   → Guardar cambios de imágenes

**Estructura de carpetas:**
- Las imágenes se guardan en:
  - public/storage/images/logo/logo.png
  - public/storage/images/login/login.png
  - public/storage/images/dashboard/dashboard.png
  - public/storage/images/spinner/spinner.png
  - public/storage/images/favicon/favicon.png

**Personalización:**
- Si existe una imagen personalizada, el sistema la muestra automáticamente.
- Si no existe, se usa la imagen por defecto de AdminLTE (CDN).
- El favicon también es personalizable y se muestra en la pestaña del navegador.

**Acceso:**
- Solo usuarios autenticados pueden modificar las imágenes.
- El acceso es desde el menú lateral: Configuración > Imágenes del Sistema.

--}}
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
