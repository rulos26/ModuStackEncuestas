<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    {{-- Base Meta Tags --}}
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')

    {{-- Title --}}
    <title>
        @yield('title_prefix', config('adminlte.title_prefix', ''))
        @yield('title', config('adminlte.title', 'AdminLTE 3'))
        @yield('title_postfix', config('adminlte.title_postfix', ''))
    </title>

    {{-- Custom stylesheets (pre AdminLTE) --}}
    @yield('adminlte_css_pre')

    {{-- Base Stylesheets (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_mix_css_path', 'css/app.css')) }}">
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <link rel="stylesheet" href="{{ mix(config('adminlte.laravel_css_path', 'css/app.css')) }}">
            @break

            @case('vite')
                @vite([config('adminlte.laravel_css_path', 'resources/css/app.css'), config('adminlte.laravel_js_path', 'resources/js/app.js')])
            @break

            @case('vite_js_only')
                @vite(config('adminlte.laravel_js_path', 'resources/js/app.js'))
            @break

            @default
                <!-- ====== AdminLTE y dependencias por CDN ====== -->
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/icheck-bootstrap@3.0.1/icheck-bootstrap.min.css">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars/css/OverlayScrollbars.min.css">

                @if(config('adminlte.google_fonts.allowed', true))
                    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
                @endif
        @endswitch
    @endif

    {{-- Extra Configured Plugins Stylesheets --}}
    @include('adminlte::plugins', ['type' => 'css'])

    {{-- Livewire Styles --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireStyles
        @else
            <livewire:styles />
        @endif
    @endif

    {{-- Custom Stylesheets (post AdminLTE) --}}
    @yield('adminlte_css')

    {{-- CSS Global para Autofill en Formularios --}}
    <style>
        /* ====== CSS GLOBAL PARA AUTOFILL ====== */

        /* Estilos base para inputs */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="tel"],
        input[type="url"],
        input[type="search"],
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        input[type="month"],
        input[type="week"],
        textarea,
        select {
            color: #222 !important;
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }

        /* Estilos para modo oscuro */
        body.dark-mode input[type="text"],
        body.dark-mode input[type="email"],
        body.dark-mode input[type="password"],
        body.dark-mode input[type="number"],
        body.dark-mode input[type="tel"],
        body.dark-mode input[type="url"],
        body.dark-mode input[type="search"],
        body.dark-mode input[type="date"],
        body.dark-mode input[type="time"],
        body.dark-mode input[type="datetime-local"],
        body.dark-mode input[type="month"],
        body.dark-mode input[type="week"],
        body.dark-mode textarea,
        body.dark-mode select {
            color: #fff !important;
            background-color: #343a40 !important;
            border-color: #495057 !important;
        }

        /* Estilos para focus */
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="tel"]:focus,
        input[type="url"]:focus,
        input[type="search"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus,
        input[type="datetime-local"]:focus,
        input[type="month"]:focus,
        input[type="week"]:focus,
        textarea:focus,
        select:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
            outline: 0 !important;
        }

        body.dark-mode input[type="text"]:focus,
        body.dark-mode input[type="email"]:focus,
        body.dark-mode input[type="password"]:focus,
        body.dark-mode input[type="number"]:focus,
        body.dark-mode input[type="tel"]:focus,
        body.dark-mode input[type="url"]:focus,
        body.dark-mode input[type="search"]:focus,
        body.dark-mode input[type="date"]:focus,
        body.dark-mode input[type="time"]:focus,
        body.dark-mode input[type="datetime-local"]:focus,
        body.dark-mode input[type="month"]:focus,
        body.dark-mode input[type="week"]:focus,
        body.dark-mode textarea:focus,
        body.dark-mode select:focus {
            color: #fff !important;
            background-color: #343a40 !important;
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25) !important;
        }

        /* ====== AUTOFILL - Forzar colores en autocompletado ====== */

        /* Modo claro */
        input:-webkit-autofill,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 100px #f8f9fa inset !important;
            box-shadow: 0 0 0 100px #f8f9fa inset !important;
            -webkit-text-fill-color: #222 !important;
            color: #222 !important;
            border-color: #ced4da !important;
        }

        /* Modo oscuro */
        body.dark-mode input:-webkit-autofill,
        body.dark-mode input:-webkit-autofill:focus,
        body.dark-mode input:-webkit-autofill:hover,
        body.dark-mode input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 100px #343a40 inset !important;
            box-shadow: 0 0 0 100px #343a40 inset !important;
            -webkit-text-fill-color: #fff !important;
            color: #fff !important;
            border-color: #007bff !important;
        }

        /* ====== PLACEHOLDER ====== */
        ::placeholder {
            color: #888 !important;
            opacity: 1 !important;
        }

        body.dark-mode ::placeholder {
            color: #ccc !important;
        }

        /* ====== SELECT ESPECÍFICO ====== */
        select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.5rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.5em 1.5em !important;
            padding-right: 2.5rem !important;
        }

        body.dark-mode select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e") !important;
        }

        /* ====== TEXTAREA ====== */
        textarea {
            resize: vertical !important;
            min-height: 100px !important;
        }

        /* ====== INPUT GROUPS ====== */
        .input-group-text {
            background-color: #e9ecef !important;
            border-color: #ced4da !important;
            color: #495057 !important;
        }

        body.dark-mode .input-group-text {
            background-color: #495057 !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }

        /* ====== FORM VALIDATION ====== */
        .is-valid {
            border-color: #28a745 !important;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        body.dark-mode .is-valid {
            border-color: #28a745 !important;
        }

        body.dark-mode .is-invalid {
            border-color: #dc3545 !important;
        }

        /* ====== DISABLED INPUTS ====== */
        input:disabled,
        textarea:disabled,
        select:disabled {
            background-color: #e9ecef !important;
            opacity: 0.65 !important;
        }

        body.dark-mode input:disabled,
        body.dark-mode textarea:disabled,
        body.dark-mode select:disabled {
            background-color: #495057 !important;
            opacity: 0.65 !important;
        }

                /* ====== CARDS ARMÓNICAS ====== */

        /* Cards básicas - SOLO las que no tienen color específico */
        .card:not(.bg-primary):not(.bg-success):not(.bg-info):not(.bg-warning):not(.bg-danger):not(.bg-secondary):not(.bg-dark):not(.bg-purple):not(.bg-teal):not(.bg-orange):not(.bg-indigo):not(.bg-maroon):not(.bg-pink):not(.bg-cyan):not(.bg-light):not(.bg-gradient-primary):not(.bg-gradient-success):not(.bg-gradient-info) {
            background-color: #f8f9fa !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        /* Modo oscuro para cards básicas */
        body.dark-mode .card:not(.bg-primary):not(.bg-success):not(.bg-info):not(.bg-warning):not(.bg-danger):not(.bg-secondary):not(.bg-dark):not(.bg-purple):not(.bg-teal):not(.bg-orange):not(.bg-indigo):not(.bg-maroon):not(.bg-pink):not(.bg-cyan):not(.bg-light):not(.bg-gradient-primary):not(.bg-gradient-success):not(.bg-gradient-info) {
            background-color: #343a40 !important;
            border-color: #495057 !important;
            color: #fff !important;
        }

        /* Card headers - SOLO para cards básicas */
        .card:not(.bg-primary):not(.bg-success):not(.bg-info):not(.bg-warning):not(.bg-danger):not(.bg-secondary):not(.bg-dark):not(.bg-purple):not(.bg-teal):not(.bg-orange):not(.bg-indigo):not(.bg-maroon):not(.bg-pink):not(.bg-cyan):not(.bg-light):not(.bg-gradient-primary):not(.bg-gradient-success):not(.bg-gradient-info) .card-header {
            background-color: #e9ecef !important;
            border-bottom: 1px solid #dee2e6 !important;
            color: #495057 !important;
        }

        body.dark-mode .card:not(.bg-primary):not(.bg-success):not(.bg-info):not(.bg-warning):not(.bg-danger):not(.bg-secondary):not(.bg-dark):not(.bg-purple):not(.bg-teal):not(.bg-orange):not(.bg-indigo):not(.bg-maroon):not(.bg-pink):not(.bg-cyan):not(.bg-light):not(.bg-gradient-primary):not(.bg-gradient-success):not(.bg-gradient-info) .card-header {
            background-color: #495057 !important;
            border-bottom-color: #6c757d !important;
            color: #fff !important;
        }

        /* Card body - SOLO para cards básicas */
        .card:not(.bg-primary):not(.bg-success):not(.bg-info):not(.bg-warning):not(.bg-danger):not(.bg-secondary):not(.bg-dark):not(.bg-purple):not(.bg-teal):not(.bg-orange):not(.bg-indigo):not(.bg-maroon):not(.bg-pink):not(.bg-cyan):not(.bg-light):not(.bg-gradient-primary):not(.bg-gradient-success):not(.bg-gradient-info) .card-body {
            color: #212529 !important;
        }

        body.dark-mode .card:not(.bg-primary):not(.bg-success):not(.bg-info):not(.bg-warning):not(.bg-danger):not(.bg-secondary):not(.bg-dark):not(.bg-purple):not(.bg-teal):not(.bg-orange):not(.bg-indigo):not(.bg-maroon):not(.bg-pink):not(.bg-cyan):not(.bg-light):not(.bg-gradient-primary):not(.bg-gradient-success):not(.bg-gradient-info) .card-body {
            color: #fff !important;
        }

        /* Card titles - SOLO para cards básicas */
        .card:not(.bg-primary):not(.bg-success):not(.bg-info):not(.bg-warning):not(.bg-danger):not(.bg-secondary):not(.bg-dark):not(.bg-purple):not(.bg-teal):not(.bg-orange):not(.bg-indigo):not(.bg-maroon):not(.bg-pink):not(.bg-cyan):not(.bg-light):not(.bg-gradient-primary):not(.bg-gradient-success):not(.bg-gradient-info) .card-title {
            color: #495057 !important;
        }

        body.dark-mode .card:not(.bg-primary):not(.bg-success):not(.bg-info):not(.bg-warning):not(.bg-danger):not(.bg-secondary):not(.bg-dark):not(.bg-purple):not(.bg-teal):not(.bg-orange):not(.bg-indigo):not(.bg-maroon):not(.bg-pink):not(.bg-cyan):not(.bg-light):not(.bg-gradient-primary):not(.bg-gradient-success):not(.bg-gradient-info) .card-title {
            color: #fff !important;
        }

        /* Cards con colores específicos */
        .card.bg-primary {
            background-color: #007bff !important;
            color: #fff !important;
        }

        .card.bg-success {
            background-color: #28a745 !important;
            color: #fff !important;
        }

        .card.bg-info {
            background-color: #17a2b8 !important;
            color: #fff !important;
        }

        .card.bg-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .card.bg-danger {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .card.bg-secondary {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        .card.bg-dark {
            background-color: #343a40 !important;
            color: #fff !important;
        }

        /* Colores adicionales para cards */
        .card.bg-purple {
            background-color: #6f42c1 !important;
            color: #fff !important;
        }

        .card.bg-teal {
            background-color: #20c997 !important;
            color: #fff !important;
        }

        .card.bg-orange {
            background-color: #fd7e14 !important;
            color: #fff !important;
        }

        .card.bg-indigo {
            background-color: #6610f2 !important;
            color: #fff !important;
        }

        .card.bg-maroon {
            background-color: #d63384 !important;
            color: #fff !important;
        }

        .card.bg-pink {
            background-color: #e83e8c !important;
            color: #fff !important;
        }

        .card.bg-cyan {
            background-color: #0dcaf0 !important;
            color: #fff !important;
        }

        .card.bg-light {
            background-color: #f8f9fa !important;
            color: #212529 !important;
        }

        .card.bg-light .card-body {
            color: #212529 !important;
        }

        .card.bg-light .text-dark {
            color: #212529 !important;
        }

        .card.bg-light ul li {
            color: #212529 !important;
        }

        .card.bg-light strong {
            color: #212529 !important;
        }

        /* Cards con gradientes */
        .card.bg-gradient-primary {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
            color: #fff !important;
        }

        .card.bg-gradient-success {
            background: linear-gradient(45deg, #28a745, #1e7e34) !important;
            color: #fff !important;
        }

        .card.bg-gradient-info {
            background: linear-gradient(45deg, #17a2b8, #117a8b) !important;
            color: #fff !important;
        }

        /* Texto en cards */
        .card .text-white {
            color: #fff !important;
        }

        .card .text-dark {
            color: #212529 !important;
        }

        /* Botones en cards */
        .card .btn {
            border-radius: 0.25rem !important;
            font-weight: 500 !important;
        }

        /* Formularios dentro de cards */
        .card .form-control {
            border-color: #ced4da !important;
        }

        body.dark-mode .card .form-control {
            background-color: #495057 !important;
            border-color: #6c757d !important;
            color: #fff !important;
        }

        /* Tablas en cards */
        .card .table {
            color: inherit !important;
        }

        .card .table th {
            border-top-color: #dee2e6 !important;
            color: inherit !important;
        }

        .card .table td {
            border-top-color: #dee2e6 !important;
            color: inherit !important;
        }

        body.dark-mode .card .table th,
        body.dark-mode .card .table td {
            border-top-color: #495057 !important;
        }

        /* Alertas en cards */
        .card .alert {
            border: 1px solid transparent !important;
            border-radius: 0.25rem !important;
        }

        /* Listas en cards */
        .card ul,
        .card ol {
            color: inherit !important;
        }

        /* Enlaces en cards */
        .card a {
            color: #007bff !important;
        }

        body.dark-mode .card a {
            color: #66b0ff !important;
        }

        .card a:hover {
            color: #0056b3 !important;
        }

        body.dark-mode .card a:hover {
            color: #99ccff !important;
        }

        /* Badges en cards */
        .card .badge {
            font-size: 0.75em !important;
            font-weight: 600 !important;
        }

        /* Progress bars en cards */
        .card .progress {
            background-color: #e9ecef !important;
        }

        body.dark-mode .card .progress {
            background-color: #495057 !important;
        }

        /* Modales y popovers en cards */
        .card .modal-content {
            background-color: #fff !important;
            color: #212529 !important;
        }

        body.dark-mode .card .modal-content {
            background-color: #343a40 !important;
            color: #fff !important;
        }

        /* Tooltips en cards */
        .card .tooltip-inner {
            background-color: #000 !important;
            color: #fff !important;
        }

        /* Dropdowns en cards */
        .card .dropdown-menu {
            background-color: #fff !important;
            border-color: #dee2e6 !important;
        }

        body.dark-mode .card .dropdown-menu {
            background-color: #343a40 !important;
            border-color: #495057 !important;
        }

        .card .dropdown-item {
            color: #212529 !important;
        }

        body.dark-mode .card .dropdown-item {
            color: #fff !important;
        }

        .card .dropdown-item:hover {
            background-color: #f8f9fa !important;
        }

        body.dark-mode .card .dropdown-item:hover {
            background-color: #495057 !important;
        }
    </style>

    {{--
        INTEGRACIÓN CON MÓDULO DE CONFIGURACIÓN DE IMÁGENES:
        Esta vista personalizada prioriza las imágenes de la base de datos sobre config/adminlte.php.
        Si existe una imagen personalizada en la tabla 'settings', se usa esa como favicon.
        Si no existe, se usan los valores de config/adminlte.php para favicon.
    --}}
    @php($logo = \App\Models\Setting::current()->logo)
    @php($favicon = \App\Models\Setting::current()->favicon)
    @if($favicon)
        <link rel="shortcut icon" href="{{ asset('public/storage/images/favicon/favicon.png') }}" />
    @elseif($logo)
        <link rel="shortcut icon" href="{{ asset('public/storage/images/logo/logo.png') }}" />
    @elseif(config('adminlte.use_ico_only'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
    @elseif(config('adminlte.use_full_favicon'))
        <link rel="shortcut icon" href="{{ asset('favicons/favicon.ico') }}" />
        <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('favicons/apple-icon-57x57.png') }}">
        <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('favicons/apple-icon-60x60.png') }}">
        <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('favicons/apple-icon-72x72.png') }}">
        <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('favicons/apple-icon-76x76.png') }}">
        <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('favicons/apple-icon-114x114.png') }}">
        <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('favicons/apple-icon-120x120.png') }}">
        <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('favicons/apple-icon-144x144.png') }}">
        <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('favicons/apple-icon-152x152.png') }}">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicons/apple-icon-180x180.png') }}">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicons/favicon-16x16.png') }}">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicons/favicon-32x32.png') }}">
        <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicons/favicon-96x96.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicons/android-icon-192x192.png') }}">
        <link rel="manifest" crossorigin="use-credentials" href="{{ asset('favicons/manifest.json') }}">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ asset('favicon/ms-icon-144x144.png') }}">
    @endif

</head>

<body class="@yield('classes_body')" @yield('body_data')>

    {{-- Body Content --}}
    @yield('body')

    {{-- Base Scripts (depends on Laravel asset bundling tool) --}}
    @if(config('adminlte.enabled_laravel_mix', false))
        <script src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>
    @else
        @switch(config('adminlte.laravel_asset_bundling', false))
            @case('mix')
                <script src="{{ mix(config('adminlte.laravel_js_path', 'js/app.js')) }}"></script>
            @break

            @case('vite')
            @case('vite_js_only')
            @break

            @default
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars/js/OverlayScrollbars.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
        @endswitch
    @endif

    {{-- Extra Configured Plugins Scripts --}}
    @include('adminlte::plugins', ['type' => 'js'])

    {{-- Livewire Script --}}
    @if(config('adminlte.livewire'))
        @if(intval(app()->version()) >= 7)
            @livewireScripts
        @else
            <livewire:scripts />
        @endif
    @endif

    {{-- Custom Scripts --}}
    @yield('adminlte_js')

</body>

</html>
