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

Route::middleware(['auth'])->group(function () {
    Route::get('/settings/images', [App\Http\Controllers\ImageSettingsController::class, 'index'])->name('settings.images');
    Route::post('/settings/images', [App\Http\Controllers\ImageSettingsController::class, 'update'])->name('settings.images.update');
    // Logs del sistema
    Route::get('/logs', [App\Http\Controllers\LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/module', [App\Http\Controllers\LogController::class, 'module'])->name('logs.module');
});
