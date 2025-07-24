<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\MailPanelController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EmpleadoPlantillaController;
use App\Models\PoliticaPrivacidad;
use App\Models\Empresa;
use App\Http\Controllers\EmpresasClienteController;
use App\Http\Controllers\EncuestaLogicaController;
use App\Http\Controllers\EncuestaPreviewController;
use App\Http\Controllers\EncuestaPublicaController;
use App\Http\Controllers\EncuestaRespuestaController;
use App\Http\Controllers\PreguntaController;

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

Route::get('empleados/plantillas', [EmpleadoPlantillaController::class, 'plantillas'])->name('empleados.plantillas');
Route::get('empleados/plantillas/excel', [EmpleadoPlantillaController::class, 'descargarExcel'])->name('empleados.plantilla.excel');
Route::get('empleados/plantillas/csv', [EmpleadoPlantillaController::class, 'descargarCsv'])->name('empleados.plantilla.csv');

Route::get('empleados', [EmpleadoController::class, 'index'])->name('empleados.index');
Route::get('empleados/create', [EmpleadoController::class, 'create'])->name('empleados.create');
Route::post('empleados', [EmpleadoController::class, 'store'])->name('empleados.store');
Route::get('empleados/import', [EmpleadoController::class, 'importForm'])->name('empleados.import.form');
Route::post('empleados/import', [EmpleadoController::class, 'import'])->name('empleados.import');
Route::get('empleados/{empleado}', [EmpleadoController::class, 'show'])->name('empleados.show');
Route::get('empleados/{empleado}/edit', [EmpleadoController::class, 'edit'])->name('empleados.edit');
Route::put('empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');
Route::delete('empleados/{empleado}', [EmpleadoController::class, 'destroy'])->name('empleados.destroy');

// Rutas del módulo de monitoreo de sesiones
Route::middleware(['auth'])->group(function () {
    Route::get('/session-monitor', [App\Http\Controllers\SessionMonitorController::class, 'index'])
        ->name('session.monitor.index');
    Route::get('/session-monitor/history', [App\Http\Controllers\SessionMonitorController::class, 'history'])
        ->name('session.monitor.history');
    Route::get('/session-monitor/active', [App\Http\Controllers\SessionMonitorController::class, 'getActiveSessions'])
        ->name('session.monitor.active');
    Route::post('/session-monitor/close/{sessionId}', [App\Http\Controllers\SessionMonitorController::class, 'closeSession'])
        ->name('session.monitor.close');
    Route::post('/session-monitor/close-user/{userId}', [App\Http\Controllers\SessionMonitorController::class, 'closeAllUserSessions'])
        ->name('session.monitor.close-user');
    Route::post('/session-monitor/close-expired', [App\Http\Controllers\SessionMonitorController::class, 'closeExpiredSessions'])
        ->name('session.monitor.close-expired');
    Route::get('/session-monitor/export', [App\Http\Controllers\SessionMonitorController::class, 'export'])
        ->name('session.monitor.export');
});

// Rutas del módulo de empresa (Gestión de Entidad Empresarial Única)
Route::middleware(['auth'])->group(function () {
    Route::get('empresa', [App\Http\Controllers\EmpresaController::class, 'show'])->name('empresa.show');
    Route::get('empresa/crear', [App\Http\Controllers\EmpresaController::class, 'create'])->name('empresa.create');
    Route::post('empresa', [App\Http\Controllers\EmpresaController::class, 'store'])->name('empresa.store');
    Route::get('empresa/editar', [App\Http\Controllers\EmpresaController::class, 'edit'])->name('empresa.edit');
    Route::put('empresa', [App\Http\Controllers\EmpresaController::class, 'update'])->name('empresa.update');
    Route::get('empresa/export/pdf', [App\Http\Controllers\EmpresaController::class, 'exportPdf'])->name('empresa.export.pdf');
    // AJAX para selects encadenados
    //Route::get('empresa/departamentos/{pais_id}', [App\Http\Controllers\EmpresaController::class, 'getDepartamentos'])->name('empresa.departamentos');
    //Route::get('empresa/municipios/{departamento_id}', [App\Http\Controllers\EmpresaController::class, 'getMunicipios'])->name('empresa.municipios');
});

// CRUD de Países
Route::middleware(['auth'])->group(function () {
    Route::resource('paises', App\Http\Controllers\PaisController::class);
});

// CRUD de Departamentos
Route::middleware(['auth'])->group(function () {
    Route::resource('departamentos', App\Http\Controllers\DepartamentoController::class);
});

// CRUD de Municipios
Route::middleware(['auth'])->group(function () {
    Route::resource('municipios', App\Http\Controllers\MunicipioController::class);
});
Route::get('empresa/departamentos/{pais_id}', [App\Http\Controllers\EmpresaController::class, 'getDepartamentos'])->name('empresa.departamentos');
Route::get('empresa/municipios/{departamento_id}', [App\Http\Controllers\EmpresaController::class, 'getMunicipios'])->name('empresa.municipios');

// CRUD de Políticas de Privacidad
Route::resource('politicas-privacidad', App\Http\Controllers\PoliticaPrivacidadController::class);

// Ruta pública para mostrar la última política de privacidad activa
Route::get('/politica-privacidad', function() {
    $politica = PoliticaPrivacidad::where('estado', true)->orderByDesc('fecha_publicacion')->first();
    return view('publico.politica', compact('politica'));
})->name('public.politica');

// Ruta pública para About Quantum Metric
Route::get('/about-quantum-metric', function() {
    $empresa = Empresa::with(['pais', 'departamento', 'municipio'])->first();
    return view('publico.about', compact('empresa'));
})->name('public.about');

Route::middleware(['auth'])->group(function () {
    Route::resource('empresas_clientes', EmpresasClienteController::class);
    Route::get('empresas_clientes/{empresas_cliente}/pdf', [EmpresasClienteController::class, 'exportPdf'])->name('empresas_clientes.exportPdf');
});

Route::get('encuestas/{encuesta}/preguntas', [PreguntaController::class, 'create'])->name('encuestas.preguntas.create');
Route::post('encuestas/{encuesta}/preguntas', [PreguntaController::class, 'store'])->name('encuestas.preguntas.store');
Route::get('encuestas/{encuesta}/respuestas', [EncuestaRespuestaController::class, 'create'])->name('encuestas.respuestas.create');
Route::post('encuestas/{encuesta}/respuestas', [EncuestaRespuestaController::class, 'store'])->name('encuestas.respuestas.store');
Route::get('encuestas/{encuesta}/logica', [EncuestaLogicaController::class, 'create'])->name('encuestas.logica.create');
Route::post('encuestas/{encuesta}/logica', [EncuestaLogicaController::class, 'store'])->name('encuestas.logica.store');

Route::get('encuestas/{encuesta}/preview', [EncuestaPreviewController::class, 'preview'])->name('encuestas.preview');

Route::get('publica/{slug}', [EncuestaPublicaController::class, 'mostrar'])->name('encuestas.publica');
Route::post('publica/{id}', [EncuestaPublicaController::class, 'responder'])->name('encuestas.responder');

Route::middleware(['auth'])->group(function () {
    Route::resource('encuestas', App\Http\Controllers\EncuestaController::class);
    Route::get('encuestas/create', [App\Http\Controllers\EncuestaController::class, 'create'])
        ->name('encuestas.create');
    Route::post('encuestas/{encuesta}/clonar', [App\Http\Controllers\EncuestaController::class, 'clonar'])->name('encuestas.clone');
});


