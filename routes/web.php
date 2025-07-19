<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\MailPanelController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EmpleadoPlantillaController;

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
Route::middleware(['auth'])->group(function () {
    Route::get('testing', [App\Http\Controllers\TestRunnerController::class, 'index'])->name('testing.index');
    Route::post('testing/run', [App\Http\Controllers\TestRunnerController::class, 'run'])->name('testing.run');
});

// Módulo de pruebas internas
Route::get('/test', [App\Http\Controllers\UsuarioTestController::class, 'index'])->name('test.index');
Route::post('/test/ejecutar', [App\Http\Controllers\UsuarioTestController::class, 'ejecutar'])->name('test.ejecutar');

// Documentación de usuarios y roles
Route::get('/ayuda/usuarios-roles', function() {
    return view('ayuda.usuarios_roles');
})->name('ayuda.usuarios_roles');

// Logs de errores de módulos
Route::get('/logs/module/user', [App\Http\Controllers\LogController::class, 'userModule'])->name('logs.module.user');
Route::get('/logs/module/role', [App\Http\Controllers\LogController::class, 'roleModule'])->name('logs.module.role');

// Login social Google
Route::get('login/google', [App\Http\Controllers\SocialAuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('login/google/callback', [App\Http\Controllers\SocialAuthController::class, 'handleGoogleCallback']);
// Login social Microsoft
Route::get('login/microsoft', [App\Http\Controllers\SocialAuthController::class, 'redirectToMicrosoft'])->name('login.microsoft');
Route::get('login/microsoft/callback', [App\Http\Controllers\SocialAuthController::class, 'handleMicrosoftCallback']);

/* Route::middleware(['auth', 'role:Superadmin|Admin'])->prefix('admin')->group(function () {
    Route::get('correos', [MailPanelController::class, 'index'])->name('admin.correos.index');
    Route::post('correos/enviar', [MailPanelController::class, 'send'])->name('admin.correos.send');
}); */

Route::get('correos', [MailPanelController::class, 'index'])->name('admin.correos.index');
    Route::post('correos/enviar', [MailPanelController::class, 'send'])->name('admin.correos.send');

Route::get('empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
Route::get('empleados/create', [EmpleadoController::class, 'create'])->name('empleados.create');
Route::post('empleados', [EmpleadoController::class, 'store'])->name('empleados.store');
Route::get('empleados/import', [EmpleadoController::class, 'importForm'])->name('empleados.import.form');
Route::post('empleados/import', [EmpleadoController::class, 'import'])->name('empleados.import');
Route::get('empleados/{empleado}', [EmpleadoController::class, 'show'])->name('empleados.show');
Route::get('empleados/{empleado}/edit', [EmpleadoController::class, 'edit'])->name('empleados.edit');
Route::put('empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');
Route::delete('empleados/{empleado}', [EmpleadoController::class, 'destroy'])->name('empleados.destroy');

Route::get('empleados/plantillas', [EmpleadoPlantillaController::class, 'plantillas'])->name('empleados.plantillas');
Route::get('empleados/plantillas/excel', [EmpleadoPlantillaController::class, 'descargarExcel'])->name('empleados.plantilla.excel');
Route::get('empleados/plantillas/csv', [EmpleadoPlantillaController::class, 'descargarCsv'])->name('empleados.plantilla.csv');
