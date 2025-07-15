@extends('adminlte::page')

@section('title', 'Manual de Usuario - Módulo de Imágenes')

@section('content_header')
    <h1>Manual de Usuario: Módulo de Imágenes</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <h4>¿Qué es el Módulo de Imágenes?</h4>
        <p>El módulo de imágenes te permite personalizar los elementos visuales clave del sistema para que se adapten a la identidad de tu organización. Solo los administradores pueden acceder y modificar estas imágenes.</p>
        <hr>
        <h5>¿Qué puedes personalizar?</h5>
        <ul>
            <li><b>Logo Principal:</b> Se muestra en la cabecera y menú lateral. <br><small>Recomendado: PNG, fondo transparente, 120x40px.</small></li>
            <li><b>Logo Login:</b> Aparece en la pantalla de inicio de sesión. <br><small>Recomendado: PNG, fondo transparente, 120x40px.</small></li>
            <li><b>Logo Dashboard:</b> Imagen destacada en la página principal del dashboard. <br><small>Recomendado: PNG, 200x60px.</small></li>
            <li><b>Spinner (Preloader):</b> Imagen animada que aparece mientras carga el sistema. <br><small>Recomendado: GIF o PNG, 60x60px.</small></li>
            <li><b>Favicon:</b> Icono pequeño que aparece en la pestaña del navegador. <br><small>Recomendado: ICO o PNG, 32x32px o 64x64px.</small></li>
        </ul>
        <hr>
        <h5>¿Cómo cambiar una imagen?</h5>
        <ol>
            <li>Accede al menú <b>Configuración &gt; Imágenes del Sistema</b>.</li>
            <li>Selecciona el tipo de imagen que deseas cambiar.</li>
            <li>Haz clic en <b>"Seleccionar archivo"</b> y elige la imagen desde tu computadora.</li>
            <li>Haz clic en <b>"Guardar Cambios"</b>.</li>
            <li>Verifica la previsualización para asegurarte de que la imagen se ve correctamente.</li>
        </ol>
        <hr>
        <h5>Recomendaciones</h5>
        <ul>
            <li>Utiliza imágenes optimizadas y de buena calidad para una mejor apariencia.</li>
            <li>Respeta los tamaños recomendados para evitar distorsiones.</li>
            <li>Si tienes dudas, consulta con el área de diseño de tu organización.</li>
        </ul>
        <hr>
        <h5>¿Qué pasa si no subo una imagen?</h5>
        <p>Si no personalizas una imagen, el sistema mostrará la imagen por defecto de la plantilla AdminLTE.</p>
        <hr>
        <h5>¿Dónde se muestran estas imágenes?</h5>
        <ul>
            <li><b>Logo Principal:</b> Arriba a la izquierda en todas las páginas.</li>
            <li><b>Logo Login:</b> En la pantalla de acceso al sistema.</li>
            <li><b>Logo Dashboard:</b> En la página principal después de iniciar sesión.</li>
            <li><b>Spinner:</b> Al cargar o recargar el sistema.</li>
            <li><b>Favicon:</b> En la pestaña del navegador.</li>
        </ul>
        <hr>
        <h5>¿Quién puede cambiar las imágenes?</h5>
        <p>Solo los usuarios con perfil de administrador pueden acceder y modificar las imágenes del sistema.</p>
    </div>
</div>
@endsection
