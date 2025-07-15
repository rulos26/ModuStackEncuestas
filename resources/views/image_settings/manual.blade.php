@php($settings = \App\Models\Setting::current())
@extends('adminlte::page')

@section('title', 'Manual de Usuario - Módulo de Imágenes')

@section('content_header')
    <h1><i class="fas fa-images"></i> Manual de Usuario: Módulo de Imágenes</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="alert alert-primary mb-4">
            <i class="fas fa-info-circle"></i> <b>¿Qué es el Módulo de Imágenes?</b><br>
            Personaliza los elementos visuales clave del sistema para adaptarlos a tu organización. Solo los administradores pueden modificar estas imágenes.
        </div>
        <div class="accordion" id="accordionManual">
            <div class="card mb-2">
                <div class="card-header p-2" id="headingTipos">
                    <h2 class="mb-0">
                        <button class="btn btn-link text-dark" type="button" data-toggle="collapse" data-target="#collapseTipos" aria-expanded="true" aria-controls="collapseTipos">
                            <i class="fas fa-list"></i> ¿Qué puedes personalizar?
                        </button>
                    </h2>
                </div>
                <div id="collapseTipos" class="collapse show" aria-labelledby="headingTipos" data-parent="#accordionManual">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <span class="badge badge-info"><i class="fas fa-image"></i> Logo Principal</span><br>
                                <small>Cabecera y menú lateral.<br>Recomendado: PNG, 120x40px.</small><br>
                                <img src="{{ $settings->getLogoUrl() ?? 'https://dummyimage.com/120x40/007bff/fff&text=Logo' }}" alt="Ejemplo Logo" class="img-thumbnail mt-1">
                            </div>
                            <div class="col-md-6 mb-2">
                                <span class="badge badge-info"><i class="fas fa-sign-in-alt"></i> Logo Login</span><br>
                                <small>Pantalla de inicio de sesión.<br>Recomendado: PNG, 120x40px.</small><br>
                                <img src="{{ $settings->getLoginLogoUrl() ?? 'https://dummyimage.com/120x40/6c757d/fff&text=Login' }}" alt="Ejemplo Login" class="img-thumbnail mt-1">
                            </div>
                            <div class="col-md-6 mb-2">
                                <span class="badge badge-info"><i class="fas fa-tachometer-alt"></i> Logo Dashboard</span><br>
                                <small>Página principal del dashboard.<br>Recomendado: PNG, 200x60px.</small><br>
                                <img src="{{ $settings->getDashboardLogoUrl() ?? 'https://dummyimage.com/200x60/28a745/fff&text=Dashboard' }}" alt="Ejemplo Dashboard" class="img-thumbnail mt-1">
                            </div>
                            <div class="col-md-6 mb-2">
                                <span class="badge badge-info"><i class="fas fa-sync-alt"></i> Spinner (Preloader)</span><br>
                                <small>Animación de carga.<br>Recomendado: GIF/PNG, 60x60px.</small><br>
                                <img src="{{ $settings->getSpinnerUrl() ?? 'https://dummyimage.com/60x60/ffc107/fff&text=Spin' }}" alt="Ejemplo Spinner" class="img-thumbnail mt-1">
                            </div>
                            <div class="col-md-6 mb-2">
                                <span class="badge badge-info"><i class="fas fa-star"></i> Favicon</span><br>
                                <small>Pestaña del navegador.<br>Recomendado: ICO/PNG, 32x32px.</small><br>
                                <img src="{{ $settings->getFaviconUrl() ?? 'https://dummyimage.com/32x32/343a40/fff&text=F' }}" alt="Ejemplo Favicon" class="img-thumbnail mt-1">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-2">
                <div class="card-header p-2" id="headingCambiar">
                    <h2 class="mb-0">
                        <button class="btn btn-link text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseCambiar" aria-expanded="false" aria-controls="collapseCambiar">
                            <i class="fas fa-edit"></i> ¿Cómo cambiar una imagen?
                        </button>
                    </h2>
                </div>
                <div id="collapseCambiar" class="collapse" aria-labelledby="headingCambiar" data-parent="#accordionManual">
                    <div class="card-body">
                        <ol>
                            <li>Accede al menú <b>Configuración &gt; Imágenes del Sistema</b>.</li>
                            <li>Selecciona el tipo de imagen que deseas cambiar.</li>
                            <li>Haz clic en <b>"Seleccionar archivo"</b> y elige la imagen desde tu computadora.</li>
                            <li>Haz clic en <b>"Guardar Cambios"</b>.</li>
                            <li>Verifica la previsualización para asegurarte de que la imagen se ve correctamente.</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="card mb-2">
                <div class="card-header p-2" id="headingRecomendaciones">
                    <h2 class="mb-0">
                        <button class="btn btn-link text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapseRecomendaciones" aria-expanded="false" aria-controls="collapseRecomendaciones">
                            <i class="fas fa-lightbulb"></i> Recomendaciones
                        </button>
                    </h2>
                </div>
                <div id="collapseRecomendaciones" class="collapse" aria-labelledby="headingRecomendaciones" data-parent="#accordionManual">
                    <div class="card-body">
                        <ul>
                            <li>Utiliza imágenes optimizadas y de buena calidad para una mejor apariencia.</li>
                            <li>Respeta los tamaños recomendados para evitar distorsiones.</li>
                            <li>Si tienes dudas, consulta con el área de diseño de tu organización.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card mb-2">
                <div class="card-header p-2" id="headingPreguntas">
                    <h2 class="mb-0">
                        <button class="btn btn-link text-dark collapsed" type="button" data-toggle="collapse" data-target="#collapsePreguntas" aria-expanded="false" aria-controls="collapsePreguntas">
                            <i class="fas fa-question-circle"></i> Preguntas Frecuentes
                        </button>
                    </h2>
                </div>
                <div id="collapsePreguntas" class="collapse" aria-labelledby="headingPreguntas" data-parent="#accordionManual">
                    <div class="card-body">
                        <b>¿Qué pasa si no subo una imagen?</b><br>
                        El sistema mostrará la imagen por defecto de la plantilla AdminLTE.<br><br>
                        <b>¿Dónde se muestran estas imágenes?</b>
                        <ul>
                            <li><b>Logo Principal:</b> Arriba a la izquierda en todas las páginas.</li>
                            <li><b>Logo Login:</b> En la pantalla de acceso al sistema.</li>
                            <li><b>Logo Dashboard:</b> En la página principal después de iniciar sesión.</li>
                            <li><b>Spinner:</b> Al cargar o recargar el sistema.</li>
                            <li><b>Favicon:</b> En la pestaña del navegador.</li>
                        </ul>
                        <b>¿Quién puede cambiar las imágenes?</b><br>
                        Solo los usuarios con perfil de administrador pueden acceder y modificar las imágenes del sistema.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
