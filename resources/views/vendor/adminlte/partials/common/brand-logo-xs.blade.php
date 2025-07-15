@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php( $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home') )

@if (config('adminlte.use_route_url', false))
    @php( $dashboard_url = $dashboard_url ? route($dashboard_url) : '' )
@else
    @php( $dashboard_url = $dashboard_url ? url($dashboard_url) : '' )
@endif

<a href="{{ $dashboard_url }}"
    @if($layoutHelper->isLayoutTopnavEnabled())
        class="navbar-brand {{ config('adminlte.classes_brand') }}"
    @else
        class="brand-link {{ config('adminlte.classes_brand') }}"
    @endif>

    {{--
        INTEGRACIÓN CON MÓDULO DE CONFIGURACIÓN DE IMÁGENES:
        Esta vista personalizada prioriza las imágenes de la base de datos sobre config/adminlte.php.
        Si existe una imagen personalizada en la tabla 'settings', se usa esa.
        Si no existe, se usa el CDN de AdminLTE como fallback.
        Los valores de config/adminlte.php solo se usan para clases CSS y atributos alt.
    --}}
    @php($logo = \App\Models\Setting::current()->logo)
    <img src="{{ $logo ? asset('storage/images/logo/logo.png') : 'https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png' }}"
         alt="{{ config('adminlte.logo_img_alt', 'AdminLTE') }}"
         class="{{ config('adminlte.logo_img_class', 'brand-image img-circle elevation-3') }}"
         style="opacity:.8">

    {{-- Brand text --}}
    <span class="brand-text font-weight-light {{ config('adminlte.classes_brand_text') }}">
        {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
    </span>

</a>
