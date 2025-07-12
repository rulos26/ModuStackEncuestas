<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación (login, registro, etc.)
Auth::routes();

// Ruta al home después de login
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
