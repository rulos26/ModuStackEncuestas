{{--
    VISTA PERSONALIZADA PARA ERROR 419 (PAGE EXPIRED)
    ------------------------------------------------
    Este error ocurre cuando la sesión del usuario ha expirado por inactividad o el token CSRF ya no es válido.
    Suele suceder si el usuario deja la página abierta mucho tiempo y luego intenta realizar una acción protegida.
    Solución para el usuario: recargar la página o volver a iniciar sesión.
    Solución para desarrolladores: puedes aumentar el tiempo de expiración de la sesión en config/session.php (lifetime),
    pero por seguridad no se recomienda valores muy altos.
--}}
@extends('adminlte::page')

@section('title', 'Sesión Expirada')

@section('content')
<div class="container text-center" style="margin-top:100px;">
    <div class="display-4 text-danger mb-3"><i class="fas fa-clock"></i> 419</div>
    <h2 class="mb-3">¡Tu sesión ha expirado!</h2>
    <p class="lead">Por motivos de seguridad, tu sesión se cerró tras un periodo de inactividad.<br>
    Por favor, <a href="{{ url('login') }}">inicia sesión nuevamente</a> para continuar.</p>
    <a href="{{ url('login') }}" class="btn btn-primary mt-3"><i class="fas fa-sign-in-alt"></i> Volver a iniciar sesión</a>
</div>
@endsection
