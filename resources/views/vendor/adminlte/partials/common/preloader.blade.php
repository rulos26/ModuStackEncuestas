@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

<div class="{{ $preloaderHelper->makePreloaderClasses() }}" style="{{ $preloaderHelper->makePreloaderStyle() }}">

    @hasSection('preloader')

        {{-- Use a custom preloader content --}}
        @yield('preloader')

    @else

        {{--
            INTEGRACIÓN CON MÓDULO DE CONFIGURACIÓN DE IMÁGENES:
            Esta vista personalizada prioriza las imágenes de la base de datos sobre config/adminlte.php.
            Si existe una imagen personalizada en la tabla 'settings', se usa esa.
            Si no existe, se usa el CDN de AdminLTE como fallback.
            Los valores de config/adminlte.php solo se usan para clases CSS y atributos alt.
        --}}
        @php($spinner = \App\Models\Setting::current()->spinner)
        <img src="{{ $spinner ? asset('storage/images/spinner/' . basename($spinner)) : 'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png' }}"
             alt="AdminLTE Preloader"
             height="60"
             width="60"
             class="animation__shake">

    @endif

</div>
