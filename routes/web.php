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
    Route::get('/settings/images/manual', function() {
        return view('image_settings.manual');
    })->name('settings.images.manual');
    // Logs del sistema
    Route::get('/logs', [App\Http\Controllers\LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/module', [App\Http\Controllers\LogController::class, 'module'])->name('logs.module');
    Route::resource('users', App\Http\Controllers\UserController::class);
    Route::get('users-export', [App\Http\Controllers\UserController::class, 'export'])->name('users.export');
    Route::resource('roles', App\Http\Controllers\RoleController::class);
});

// Rutas del módulo de optimización del sistema
Route::prefix('system/optimizer')->name('system.optimizer.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Modules\SystemOptimizer\Controllers\SystemOptimizerController::class, 'index'])->name('index');
    Route::post('/clear-caches', [App\Modules\SystemOptimizer\Controllers\SystemOptimizerController::class, 'clearCaches'])->name('clear-caches');
    Route::post('/dump-autoload', [App\Modules\SystemOptimizer\Controllers\SystemOptimizerController::class, 'dumpAutoload'])->name('dump-autoload');
    Route::post('/optimize-routes', [App\Modules\SystemOptimizer\Controllers\SystemOptimizerController::class, 'optimizeRoutes'])->name('optimize-routes');
    Route::post('/clear-temp-files', [App\Modules\SystemOptimizer\Controllers\SystemOptimizerController::class, 'clearTempFiles'])->name('clear-temp-files');
    Route::post('/optimize-all', [App\Modules\SystemOptimizer\Controllers\SystemOptimizerController::class, 'optimizeAll'])->name('optimize-all');
});

// Módulo de testing
Route::middleware(['auth', 'role:admin|superadmin'])->group(function () {
    Route::get('testing', [App\Http\Controllers\TestRunnerController::class, 'index'])->name('testing.index');
    Route::post('testing/run', [App\Http\Controllers\TestRunnerController::class, 'run'])->name('testing.run');
});
