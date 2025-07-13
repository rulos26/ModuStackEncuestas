@inject('preloaderHelper', 'JeroenNoten\LaravelAdminLte\Helpers\PreloaderHelper')

<div class="{{ $preloaderHelper->makePreloaderClasses() }}" style="{{ $preloaderHelper->makePreloaderStyle() }}">

    @hasSection('preloader')

        {{-- Use a custom preloader content --}}
        @yield('preloader')

    @else

        {{-- Use the default preloader content --}}
        <img src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/img/AdminLTELogo.png"
             alt="AdminLTE Preloader"
             height="60"
             width="60"
             class="animation__shake">

    @endif

</div>
