<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'SystemQM',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>System</b>QM',
    'logo_img' => 'vendor/systemqm/dist/img/SystemQMLogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'SystemQM Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/systemqm/dist/img/SystemQMLogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/systemqm/dist/img/SystemQMLogo.png',
            'alt' => 'SystemQM Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => 'auto',

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => true,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password.request',
    'password_email_url' => 'password.email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    | IMPLEMENTACIÓN DEL MENÚ:
    | Este menú está configurado para un sistema de encuestas con las siguientes secciones:
    | - Dashboard: Página principal del sistema
    | - Gestión de Encuestas: Crear, listar y gestionar encuestas
    | - Respuestas: Ver respuestas, reportes y exportar datos
    | - Administración: Gestión de usuarios y configuración del sistema
    | - Sistema: Logs y ayuda
    |
    | Las rutas marcadas con '#' están pendientes de implementación.
    | La ruta 'settings.images' está implementada y funcional.
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'Buscar',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Buscar',
        ],

        // Dashboard
        [
            'text' => 'Dashboard',
            'url' => 'home',
            'icon' => 'fas fa-fw fa-tachometer-alt',
        ],

        ['header' => 'GESTIÓN DEL SISTEMA'],

        // Panel de Gestión del Sistema
        [
            'text' => 'Panel de Gestión',
            'route' => 'system.index',
            'icon' => 'fas fa-fw fa-cogs',
            'can' => 'system.manage',
        ],

        ['header' => 'AJUSTES DE ENCUESTAS'],

        // Gestión de Encuestas
        [
            'text' => 'Gestión de Encuestas',
            'icon' => 'fas fa-poll',
            'submenu' => [
                [
                    'text' => 'Listar Encuestas',
                    'route' => 'encuestas.index',
                    'icon' => 'fas fa-list',
                ],
                [
                    'text' => 'Crear Encuesta',
                    'route' => 'encuestas.create',
                    'icon' => 'fas fa-plus',
                ],
                [
                    'text' => 'Wizard de Preguntas',
                    'route' => 'preguntas.wizard.index',
                    'icon' => 'fas fa-magic',
                ],
                            [
                'text' => 'Configurar Respuestas',
                'route' => 'respuestas.wizard.index',
                'icon' => 'fas fa-cogs',
            ],
                [
                    'text' => 'Carga Masiva',
                    'route' => 'carga-masiva.index',
                    'icon' => 'fas fa-upload',
                ],
                [
                    'text' => 'Análisis de Respuestas',
                    'route' => 'respuestas.index',
                    'icon' => 'fas fa-chart-bar',
                ],
                [
                    'text' => 'Configuración de Envío',
                    'route' => 'configuracion-envio.index',
                    'icon' => 'fas fa-envelope-open-text',
                ],
                [
                    'text' => 'Envío Masivo',
                    'route' => 'envio-masivo.index',
                    'icon' => 'fas fa-paper-plane',
                ],
                [
                    'text' => 'Vista Previa',
                    'url'  => '#', // Cambia por la ruta real si tienes una vista previa general
                    'icon' => 'fas fa-eye',
                ],
                [
                    'text' => 'Sistema de Pruebas',
                    'route' => 'testing.index',
                    'icon' => 'fas fa-vial',
                ],
                [
                    'text' => 'Pruebas Encuesta Pública',
                    'route' => 'testing.encuesta-publica',
                    'icon' => 'fas fa-vote-yea',
                ],
            ],
        ],

        ['header' => 'AJUSTES GENERALES DEL SISTEMA'],

        // Configuración
        [
            'text' => 'Configuración',
            'icon' => 'fas fa-fw fa-cogs',
            'submenu' => [
                [
                    'text' => 'Gestión de Accesos',
                    'icon' => 'fas fa-fw fa-key',
                    'submenu' => [
                        [
                            'text' => 'Usuarios',
                            'route' => 'users.index',
                            'icon' => 'fas fa-fw fa-users',
                        ],
                        [
                            'text' => 'Gestión de Roles',
                            'route' => 'system.user-roles',
                            'icon' => 'fas fa-fw fa-user-tag',
                            'can' => 'system.user-roles',
                        ],
                        [
                            'text' => 'Empleados',
                            'route' => 'empleados.index',
                            'icon' => 'fas fa-id-badge',
                        ],
                        [
                            'text' => 'Empresas Clientes',
                            'route' => 'empresas_clientes.index',
                            'icon' => 'fas fa-building',
                        ],
                        [
                            'text' => 'Roles y Permisos',
                            'route' => 'roles.index',
                            'icon' => 'fas fa-user-shield',
                        ],
                    ],
                ],
                [
                    'text' => 'Gestión de Empresas',
                    'icon' => 'fas fa-fw fa-building',
                    'submenu' => [
                        [
                            'text' => 'Panel de Empresas',
                            'route' => 'system.companies',
                            'icon' => 'fas fa-fw fa-list',
                            'can' => 'system.companies',
                        ],
                        [
                            'text' => 'Información de la Empresa',
                            'route' => 'empresa.show',
                            'icon' => 'fas fa-building',
                        ],
                        [
                            'text' => 'Crear Empresa de Prueba',
                            'route' => 'system.create-test-company-page',
                            'icon' => 'fas fa-fw fa-plus',
                            'can' => 'system.create-test-company',
                        ],
                    ],
                ],
                [
                    'text' => 'Identidad Corporativa',
                    'icon' => 'fas fa-fw fa-palette',
                    'submenu' => [
                        [
                            'text' => 'Recursos Visuales',
                            'route' => 'settings.images',
                            'icon' => 'fas fa-fw fa-images',
                        ],
                        [
                            'text' => 'Información de la Empresa',
                            'route' => 'empresa.show',
                            'icon' => 'fas fa-building',
                        ],
                        [
                            'text' => 'Privacidad de Datos',
                            'route' => 'politicas-privacidad.index',
                            'icon' => 'fas fa-user-shield',
                        ],
                        [
                            'text' => 'Gestión Geográfica',
                            'icon' => 'fas fa-fw fa-globe',
                            'submenu' => [
                                [
                                    'text' => 'Países',
                                    'route' => 'paises.index',
                                    'icon' => 'fas fa-flag',
                                ],
                                [
                                    'text' => 'Departamentos',
                                    'route' => 'departamentos.index',
                                    'icon' => 'fas fa-map',
                                ],
                                [
                                    'text' => 'Municipios',
                                    'route' => 'municipios.index',
                                    'icon' => 'fas fa-map-marker-alt',
                                ],
                            ],
                        ],
                    ],
                ],
                // Entradas públicas fuera de Ajustes Generales del Sistema
                [
                    'text' => 'Supervisión y Registros',
                    'icon' => 'fas fa-fw fa-clipboard-list',
                    'submenu' => [
                        [
                            'text' => 'Monitoreo de Sesiones',
                            'route' => 'session.monitor.index',
                            'icon' => 'fas fa-fw fa-users',
                        ],
                        [
                            'text' => 'Registros del Sistema',
                            'icon' => 'fas fa-fw fa-file-alt',
                            'submenu' => [
                                [
                                    'text' => 'Logs de Aplicación',
                                    'route' => 'logs.index',
                                    'icon' => 'fas fa-fw fa-file',
                                ],
                                [
                                    'text' => 'Errores de Usuario',
                                    'route' => 'logs.module.user',
                                    'icon' => 'fas fa-user-times',
                                ],
                                [
                                    'text' => 'Errores de Roles',
                                    'route' => 'logs.module.role',
                                    'icon' => 'fas fa-user-shield',
                                ],
                            ],
                        ],
                        [
                            'text' => 'Diagnósticos',
                            'icon' => 'fas fa-fw fa-stethoscope',
                            'submenu' => [
                                [
                                    'text' => 'Pruebas Internas',
                                    'route' => 'test.index',
                                    'icon' => 'fas fa-vials',
                                ],
                                [
                                    'text' => 'Herramientas del Sistema',
                                    'route' => 'system.tools.dashboard',
                                    'icon' => 'fas fa-tools',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'text' => 'Comunicaciones Institucionales',
                    'icon' => 'fas fa-fw fa-bullhorn',
                    'submenu' => [
                        [
                            'text' => 'Gestor de Correos',
                            'route' => 'admin.correos.index',
                            'icon' => 'fas fa-envelope',
                        ],
                    ],
                ],
                [
                    'text' => 'Rendimiento del Sistema',
                    'icon' => 'fas fa-fw fa-tachometer-alt',
                    'submenu' => [
                        [
                            'text' => 'Módulo de Optimización',
                            'route' => 'system.optimizer.index',
                            'icon' => 'fas fa-fw fa-tools',
                        ],
                        [
                            'text' => 'Configurar Sistema de Roles',
                            'route' => 'system.setup-roles-page',
                            'icon' => 'fas fa-fw fa-cog',
                            'can' => 'system.setup-roles',
                        ],
                    ],
                ],
                [
                    'text' => 'Soporte y Documentación',
                    'icon' => 'fas fa-fw fa-life-ring',
                    'submenu' => [
                        [
                            'text' => 'Manuales y Guías',
                            'icon' => 'fas fa-fw fa-book',
                            'submenu' => [
                                [
                                    'text' => 'Guía de Recursos Visuales',
                                    'route' => 'settings.images.manual',
                                    'icon' => 'fas fa-fw fa-image',
                                ],
                                [
                                    'text' => 'Manual de Usuarios y Permisos',
                                    'route' => 'ayuda.usuarios_roles',
                                    'icon' => 'fas fa-book',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        // Entradas públicas al final del menú principal
        [
            'text' => 'Política de Privacidad',
            'url' => '/politica-privacidad',
            'icon' => 'fas fa-user-shield',
        ],
        [
            'text' => 'About Quantum Metric',
            'url' => '/about-quantum-metric',
            'icon' => 'fas fa-building',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];

