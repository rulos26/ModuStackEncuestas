@extends('adminlte::auth.auth-page', ['authType' => 'login'])

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('adminlte_css')
    @parent
    <style>
        .login-box input[type="email"],
        .login-box input[type="password"],
        .login-box input[type="text"] {
            color: #222 !important;
            background-color: #f8f9fa !important;
        }
        body.dark-mode .login-box input[type="email"],
        body.dark-mode .login-box input[type="password"],
        body.dark-mode .login-box input[type="text"] {
            color: #fff !important;
            background-color: #343a40 !important;
        }
        body.dark-mode .login-box input[type="email"]:focus,
        body.dark-mode .login-box input[type="password"]:focus,
        body.dark-mode .login-box input[type="text"]:focus {
            color: #fff !important;
            background-color: #343a40 !important;
            border-color: #007bff !important;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
        /* Forzar color en autocompletado/autofill */
        body.dark-mode .login-box input:-webkit-autofill,
        body.dark-mode .login-box input:-webkit-autofill:focus,
        body.dark-mode .login-box input:-webkit-autofill:hover,
        body.dark-mode .login-box input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 100px #343a40 inset !important;
            box-shadow: 0 0 0 100px #343a40 inset !important;
            -webkit-text-fill-color: #fff !important;
            color: #fff !important;
            border-color: #007bff !important;
        }
        .login-box ::placeholder {
            color: #888 !important;
            opacity: 1;
        }
        body.dark-mode .login-box ::placeholder {
            color: #ccc !important;
        }
    </style>
@endsection

@php
    $loginUrl = View::getSection('login_url') ?? config('adminlte.login_url', 'login');
    $registerUrl = View::getSection('register_url') ?? config('adminlte.register_url', 'register');
    $passResetUrl = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset');

    if (config('adminlte.use_route_url', false)) {
        $loginUrl = $loginUrl ? route($loginUrl) : '';
        $registerUrl = $registerUrl ? route($registerUrl) : '';
        $passResetUrl = $passResetUrl ? route($passResetUrl) : '';
    } else {
        $loginUrl = $loginUrl ? url($loginUrl) : '';
        $registerUrl = $registerUrl ? url($registerUrl) : '';
        $passResetUrl = $passResetUrl ? url($passResetUrl) : '';
    }
@endphp

@section('auth_header', 'Inicia sesión para comenzar')

@section('auth_body')
    <form action="{{ $loginUrl }}" method="post">
        @csrf
        {{-- Email field --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" autofocus required autocomplete="username">
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
            @error('email')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
        {{-- Password field con ojito --}}
        <div class="input-group mb-3">
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ __('adminlte::adminlte.password') }}" required autocomplete="current-password">
            <div class="input-group-append">
                <div class="input-group-text" style="cursor:pointer;" onclick="togglePassword()">
                    <span id="togglePasswordIcon" class="fas fa-eye"></span>
                </div>
            </div>
            @error('password')
                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>
        {{-- Remember me --}}
        <div class="row">
            <div class="col-8">
                <div class="icheck-primary">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">{{ __('adminlte::adminlte.remember_me') }}</label>
                </div>
            </div>
            <div class="col-4">
                <button type="submit" class="btn btn-primary btn-block">
                    <span class="fas fa-sign-in-alt"></span> {{ __('adminlte::adminlte.sign_in') }}
                </button>
            </div>
        </div>
    </form>
    <script>
        function togglePassword() {
            var input = document.getElementById('password');
            var icon = document.getElementById('togglePasswordIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endsection

@section('auth_footer')
    {{-- Enlaces eliminados para no mostrar 'I forgot my password' ni 'Register a new membership' --}}
@stop
