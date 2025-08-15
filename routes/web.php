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

// Rutas del módulo de gestión del sistema
Route::prefix('system')->name('system.')->middleware(['auth'])->group(function () {
    Route::get('/', [App\Http\Controllers\SystemManagementController::class, 'index'])->name('index');
    Route::get('/user-roles', [App\Http\Controllers\SystemManagementController::class, 'userRoles'])->name('user-roles');
    Route::post('/assign-role', [App\Http\Controllers\SystemManagementController::class, 'assignRole'])->name('assign-role');
    Route::post('/assign-default-roles', [App\Http\Controllers\SystemManagementController::class, 'assignDefaultRoles'])->name('assign-default-roles');
    Route::get('/companies', [App\Http\Controllers\SystemManagementController::class, 'companies'])->name('companies');
    Route::post('/create-test-company', [App\Http\Controllers\SystemManagementController::class, 'createTestCompany'])->name('create-test-company');
    Route::post('/setup-roles', [App\Http\Controllers\SystemManagementController::class, 'setupRoles'])->name('setup-roles');

    // Rutas GET para el menú (redirigen a las páginas correspondientes)
    Route::get('/setup-roles-page', [App\Http\Controllers\SystemManagementController::class, 'setupRolesPage'])->name('setup-roles-page');
    Route::get('/create-test-company-page', [App\Http\Controllers\SystemManagementController::class, 'createTestCompanyPage'])->name('create-test-company-page');
});

// Módulo de testing
Route::middleware(['auth'])->group(function () {
    Route::get('testing', [App\Http\Controllers\TestRunnerController::class, 'index'])->name('testing.index');
    Route::post('testing/run', [App\Http\Controllers\TestRunnerController::class, 'run'])->name('testing.run');
    Route::post('testing/command', [App\Http\Controllers\TestRunnerController::class, 'runCommand'])->name('testing.command');
    Route::get('testing/system-info', [App\Http\Controllers\TestRunnerController::class, 'systemInfo'])->name('testing.system-info');
});

// Pruebas de Encuesta Pública (RUTAS PÚBLICAS - sin autenticación)
Route::get('testing/encuesta-publica', [App\Http\Controllers\TestingEncuestaPublicaController::class, 'index'])->name('testing.encuesta-publica');
Route::post('testing/encuesta-publica', [App\Http\Controllers\TestingEncuestaPublicaController::class, 'ejecutarPrueba'])->name('testing.encuesta-publica');
Route::get('testing/encuesta-publica/logs', [App\Http\Controllers\TestingEncuestaPublicaController::class, 'obtenerLogs'])->name('testing.encuesta-publica-logs');
Route::get('testing/encuesta-publica/vista/{encuesta_id}', [App\Http\Controllers\TestingEncuestaPublicaController::class, 'mostrarVistaPublica'])->name('testing.encuesta-publica-vista');

// Información del sistema
Route::get('system/info', [App\Http\Controllers\SystemController::class, 'info'])->name('system.info');

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
    Route::get('empresa/departamentos/{pais_id}', [App\Http\Controllers\EmpresaController::class, 'getDepartamentos'])->name('empresa.departamentos');
    Route::get('empresa/municipios/{departamento_id}', [App\Http\Controllers\EmpresaController::class, 'getMunicipios'])->name('empresa.municipios');
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

// ============================================================================
// RUTAS DEL MÓDULO DE ENCUESTAS
// ============================================================================

// Rutas públicas de encuestas (sin autenticación, sin sesiones, sin cookies)
Route::post('{id}', [EncuestaPublicaController::class, 'responder'])
        ->name('responder')->middleware('no.session');
Route::prefix('publica')
    ->name('encuestas.')
    ->withoutMiddleware([
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \App\Http\Middleware\VerifyCsrfToken::class,
    ])
    ->group(function () {
    Route::get('{slug}', [EncuestaPublicaController::class, 'mostrar'])
        ->name('publica')
        ->middleware(['no.session', 'verificar.token.encuesta']);
    Route::get('{slug}/sin-token', [EncuestaPublicaController::class, 'mostrarSinToken'])
        ->name('publica.sin-token')
        ->middleware(['no.session']);
        Route::get('encuesta/{id}', [EncuestaPublicaController::class, 'mostrarVistaPublica'])
        ->name('publica.por-id')
        ->middleware(['no.session']);
    /* Route::post('{id}', [EncuestaPublicaController::class, 'responder'])
        ->name('responder')
        ->middleware('no.session');*/
    Route::get('{slug}/fin', [EncuestaPublicaController::class, 'finEncuesta'])
        ->name('fin')
        ->middleware('no.session');

    // Rutas para renovación de enlaces
    Route::get('{slug}/renovar', [App\Http\Controllers\EncuestaRenovarController::class, 'mostrarFormularioRenovacion'])
        ->name('renovar.formulario')
        ->middleware('public.page');
    Route::post('{slug}/renovar', [App\Http\Controllers\EncuestaRenovarController::class, 'renovarEnlace'])
        ->name('renovar.enlace')
        ->middleware('public.page');
    Route::post('verificar-token', [App\Http\Controllers\EncuestaRenovarController::class, 'verificarToken'])
        ->name('verificar.token')
        ->middleware('public.page');
});
Route::get('eliminacion-masiva', [App\Http\Controllers\EncuestaController::class, 'eliminacionMasiva'])->name('eliminacion-masiva');
// Rutas protegidas de encuestas (con autenticación)
Route::middleware(['auth'])->prefix('encuestas')->name('encuestas.')->group(function () {
    // CRUD principal de encuestas
    Route::resource('/', App\Http\Controllers\EncuestaController::class)->parameters(['' => 'encuesta']);
    Route::post('{encuesta}/clonar', [App\Http\Controllers\EncuestaController::class, 'clonar'])->name('clone');

    // Confirmación de eliminación
    Route::get('{encuesta}/confirmar-eliminacion', [App\Http\Controllers\EncuestaController::class, 'confirmarEliminacion'])->name('confirmar-eliminacion');

    // Eliminación masiva
    //Route::get('eliminacion-masiva', [App\Http\Controllers\EncuestaController::class, 'eliminacionMasiva'])->name('eliminacion-masiva');
    Route::post('confirmar-eliminacion-masiva', [App\Http\Controllers\EncuestaController::class, 'confirmarEliminacionMasiva'])->name('confirmar-eliminacion-masiva');
    Route::post('ejecutar-eliminacion-masiva', [App\Http\Controllers\EncuestaController::class, 'ejecutarEliminacionMasiva'])->name('ejecutar-eliminacion-masiva');

    // Aplicar middleware de validación de fechas a las rutas de creación y edición
    Route::post('/', [App\Http\Controllers\EncuestaController::class, 'store'])->name('store')->middleware('validar.fechas');
    Route::put('{encuesta}', [App\Http\Controllers\EncuestaController::class, 'update'])->name('update')->middleware('validar.fechas');

    // Gestión de preguntas
    Route::get('{encuesta}/preguntas', [PreguntaController::class, 'create'])
        ->name('preguntas.create')
        ->middleware('validar.flujo.encuesta:preguntas');
    Route::post('{encuesta}/preguntas', [PreguntaController::class, 'store'])
        ->name('preguntas.store')
        ->middleware('validar.flujo.encuesta:preguntas');
    Route::get('{encuesta}/preguntas/{pregunta}/edit', [PreguntaController::class, 'edit'])->name('preguntas.edit');
    Route::put('{encuesta}/preguntas/{pregunta}', [PreguntaController::class, 'update'])->name('preguntas.update');
    Route::delete('{encuesta}/preguntas/{pregunta}', [PreguntaController::class, 'destroy'])->name('preguntas.destroy');
    Route::delete('{encuesta}/preguntas', [PreguntaController::class, 'destroyAll'])->name('preguntas.destroyAll');

    // Gestión de respuestas
    Route::get('{encuesta}/respuestas', [EncuestaRespuestaController::class, 'create'])
        ->name('respuestas.create')
        ->middleware('validar.flujo.encuesta:respuestas');
    Route::post('{encuesta}/respuestas', [EncuestaRespuestaController::class, 'store'])
        ->name('respuestas.store')
        ->middleware('validar.flujo.encuesta:respuestas');

    // Configuración de lógica
    Route::get('{encuesta}/logica', [EncuestaLogicaController::class, 'create'])
        ->name('logica.create')
        ->middleware('validar.flujo.encuesta:logica');
    Route::post('{encuesta}/logica', [EncuestaLogicaController::class, 'store'])
        ->name('logica.store')
        ->middleware('validar.flujo.encuesta:logica');
    Route::get('{encuesta}/logica/resumen', [EncuestaLogicaController::class, 'resumen'])->name('logica.resumen');

    // Vista previa
    Route::get('{encuesta}/preview', [EncuestaPreviewController::class, 'preview'])
        ->name('preview')
        ->middleware('validar.flujo.encuesta:preview');
    Route::get('{encuesta}/preview/preguntas/{pregunta}/editar', [EncuestaPreviewController::class, 'editarPregunta'])->name('preview.editar-pregunta');
    Route::delete('{encuesta}/preview/preguntas/{pregunta}/eliminar', [EncuestaPreviewController::class, 'eliminarPregunta'])->name('preview.eliminar-pregunta');
    Route::get('{encuesta}/preview/estadisticas', [EncuestaPreviewController::class, 'estadisticas'])->name('preview.estadisticas');

    // Configuración de envío
    Route::get('{encuesta}/envio', [App\Http\Controllers\EncuestaEnvioController::class, 'create'])
        ->name('envio.create')
        ->middleware('validar.flujo.encuesta:envio');
    Route::post('{encuesta}/envio', [App\Http\Controllers\EncuestaEnvioController::class, 'store'])
        ->name('envio.store')
        ->middleware('validar.flujo.encuesta:envio');
    Route::post('{encuesta}/envio/agregar-usuario', [App\Http\Controllers\EncuestaEnvioController::class, 'agregarUsuario'])->name('envio.agregar-usuario');

    // Dashboard de seguimiento
    Route::get('{encuesta}/seguimiento', [App\Http\Controllers\EncuestaSeguimientoController::class, 'dashboard'])->name('seguimiento.dashboard');
    Route::get('{encuesta}/seguimiento/actualizar', [App\Http\Controllers\EncuestaSeguimientoController::class, 'actualizarDatos'])->name('seguimiento.actualizar');
    Route::get('{encuesta}/seguimiento/actualizar-correos-pendientes', [App\Http\Controllers\EncuestaSeguimientoController::class, 'actualizarCorreosPendientes'])->name('seguimiento.actualizar-correos-pendientes');
    Route::post('{encuesta}/seguimiento/pausar', [App\Http\Controllers\EncuestaSeguimientoController::class, 'pausarEnvio'])->name('seguimiento.pausar');
    Route::post('{encuesta}/seguimiento/reanudar', [App\Http\Controllers\EncuestaSeguimientoController::class, 'reanudarEnvio'])->name('seguimiento.reanudar');
    Route::post('{encuesta}/seguimiento/cancelar', [App\Http\Controllers\EncuestaSeguimientoController::class, 'cancelarEnvio'])->name('seguimiento.cancelar');

    // Nuevas rutas para envío de correos
    Route::post('{encuesta}/seguimiento/enviar-masivo', [App\Http\Controllers\EncuestaSeguimientoController::class, 'enviarCorreosMasivos'])->name('seguimiento.enviar-masivo');
    Route::post('{encuesta}/seguimiento/enviar-seleccionados', [App\Http\Controllers\EncuestaSeguimientoController::class, 'enviarCorreosSeleccionados'])->name('seguimiento.enviar-seleccionados');
    Route::post('{encuesta}/seguimiento/enviar-individual', [App\Http\Controllers\EncuestaSeguimientoController::class, 'enviarCorreoIndividualEndpoint'])->name('seguimiento.enviar-individual');
    Route::get('{encuesta}/seguimiento/detalles-correo', [App\Http\Controllers\EncuestaSeguimientoController::class, 'detallesCorreo'])->name('seguimiento.detalles-correo');
    Route::post('{encuesta}/seguimiento/exportar-lista', [App\Http\Controllers\EncuestaSeguimientoController::class, 'exportarLista'])->name('seguimiento.exportar-lista');
});

// Rutas para edición de respuestas
Route::middleware(['auth'])->prefix('encuestas')->name('encuestas.')->group(function () {
    Route::get('{pregunta}/respuestas/obtener', [App\Http\Controllers\EncuestaRespuestaController::class, 'obtenerRespuestas'])->name('respuestas.obtener');
    Route::post('{pregunta}/respuestas/editar', [App\Http\Controllers\EncuestaRespuestaController::class, 'editarRespuestas'])->name('respuestas.editar');
});

// ============================================================================
// RUTAS PARA HERRAMIENTAS DEL SISTEMA
// ============================================================================

// Módulo de respuestas con análisis de IA
Route::middleware(['auth'])->prefix('respuestas')->name('respuestas.')->group(function () {
    Route::get('/', [App\Http\Controllers\RespuestasController::class, 'index'])->name('index');
    Route::post('generar-analisis', [App\Http\Controllers\RespuestasController::class, 'generarAnalisis'])->name('generar-analisis');
    Route::get('ver/{encuestaId}', [App\Http\Controllers\RespuestasController::class, 'ver'])->name('ver');
});

// Wizard de respuestas (rutas independientes)
Route::middleware(['auth', 'respuesta.wizard.session'])->group(function () {
    Route::get('respuestas/wizard', [App\Http\Controllers\RespuestaWizardController::class, 'index'])->name('respuestas.wizard.index');
    Route::get('respuestas/wizard/responder', [App\Http\Controllers\RespuestaWizardController::class, 'responder'])->name('respuestas.wizard.responder');
    Route::post('respuestas/wizard/store', [App\Http\Controllers\RespuestaWizardController::class, 'store'])->name('respuestas.wizard.store');
    Route::get('respuestas/wizard/resumen', [App\Http\Controllers\RespuestaWizardController::class, 'resumen'])->name('respuestas.wizard.resumen');
    Route::post('respuestas/wizard/confirmar', [App\Http\Controllers\RespuestaWizardController::class, 'confirmar'])->name('respuestas.wizard.confirmar');
    Route::post('respuestas/wizard/configurar-pregunta', [App\Http\Controllers\RespuestaWizardController::class, 'configurarPregunta'])->name('respuestas.wizard.configurar.pregunta');
    Route::get('respuestas/wizard/cancel', [App\Http\Controllers\RespuestaWizardController::class, 'cancel'])->name('respuestas.wizard.cancel');
});

// Rutas del Wizard de Lógica de Preguntas
Route::middleware(['auth', 'logica.wizard.session'])->group(function () {
    Route::get('logica/wizard', [App\Http\Controllers\LogicaWizardController::class, 'index'])->name('logica.wizard.index');
    Route::get('logica/wizard/configurar', [App\Http\Controllers\LogicaWizardController::class, 'configurar'])->name('logica.wizard.configurar');
    Route::post('logica/wizard/store', [App\Http\Controllers\LogicaWizardController::class, 'store'])->name('logica.wizard.store');
    Route::get('logica/wizard/resumen', [App\Http\Controllers\LogicaWizardController::class, 'resumen'])->name('logica.wizard.resumen');
    Route::post('logica/wizard/confirmar', [App\Http\Controllers\LogicaWizardController::class, 'confirmar'])->name('logica.wizard.confirmar');
    Route::get('logica/wizard/cancel', [App\Http\Controllers\LogicaWizardController::class, 'cancel'])->name('logica.wizard.cancel');
});

Route::middleware(['auth'])->prefix('system/tools')->name('system.tools.')->group(function () {
    Route::get('/', [App\Http\Controllers\SystemToolsController::class, 'dashboard'])->name('dashboard');
    Route::get('diagnosticar-encuestas', [App\Http\Controllers\SystemToolsController::class, 'diagnosticarEncuestas'])->name('diagnosticar-encuestas');
    Route::get('diagnosticar-preguntas', [App\Http\Controllers\SystemToolsController::class, 'diagnosticarPreguntas'])->name('diagnosticar-preguntas');
    Route::match(['GET', 'POST'], 'migraciones', [App\Http\Controllers\SystemToolsController::class, 'migraciones'])->name('migraciones');
    Route::match(['GET', 'POST'], 'seeders', [App\Http\Controllers\SystemToolsController::class, 'seeders'])->name('seeders');
    Route::match(['GET', 'POST'], 'pruebas', [App\Http\Controllers\SystemToolsController::class, 'pruebas'])->name('pruebas');
});

    // Rutas para carga masiva de encuestas
    Route::middleware(['auth'])->prefix('carga-masiva')->name('carga-masiva.')->group(function () {
        Route::get('/', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'index'])->name('index');
        Route::post('procesar-preguntas', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'procesarPreguntas'])->name('procesar-preguntas');
        Route::get('wizard-preguntas', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'wizardPreguntas'])->name('wizard-preguntas');
        Route::post('guardar-tipo-pregunta', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'guardarTipoPregunta'])->name('guardar-tipo-pregunta');
        Route::get('confirmar-preguntas', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'confirmarPreguntas'])->name('confirmar-preguntas');
        Route::post('guardar-preguntas', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'guardarPreguntas'])->name('guardar-preguntas');
        Route::get('cargar-respuestas', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'cargarRespuestas'])->name('cargar-respuestas');
        Route::post('procesar-respuestas', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'procesarRespuestas'])->name('procesar-respuestas');
        Route::get('corregir-incompatibilidades', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'corregirIncompatibilidades'])->name('corregir-incompatibilidades');
        Route::post('guardar-correccion-incompatibilidad', [App\Http\Controllers\CargaMasivaEncuestasController::class, 'guardarCorreccionIncompatibilidad'])->name('guardar-correccion-incompatibilidad');
    });

// Rutas para configuración de envío de correos
Route::middleware(['auth'])->prefix('configuracion-envio')->name('configuracion-envio.')->group(function () {
    Route::get('/', [App\Http\Controllers\ConfiguracionEnvioController::class, 'index'])->name('index');
    Route::post('/get-encuestas', [App\Http\Controllers\ConfiguracionEnvioController::class, 'getEncuestasPorEmpresa'])->name('get-encuestas');
    Route::post('/get-configuracion', [App\Http\Controllers\ConfiguracionEnvioController::class, 'getConfiguracion'])->name('get-configuracion');
    Route::post('/store', [App\Http\Controllers\ConfiguracionEnvioController::class, 'store'])->name('store');
    Route::put('/update/{id}', [App\Http\Controllers\ConfiguracionEnvioController::class, 'update'])->name('update');
    Route::get('/configurar', [App\Http\Controllers\ConfiguracionEnvioController::class, 'configurar'])->name('configurar');
    Route::get('/editar/{id}', [App\Http\Controllers\ConfiguracionEnvioController::class, 'editar'])->name('editar');
    Route::get('/resumen', [App\Http\Controllers\ConfiguracionEnvioController::class, 'resumen'])->name('resumen');
    Route::post('/enviar-prueba', [App\Http\Controllers\ConfiguracionEnvioController::class, 'enviarPrueba'])->name('enviar-prueba');

    // Rutas para configuración de destinatarios
    Route::get('/obtener-empleados/{id}', [App\Http\Controllers\ConfiguracionEnvioController::class, 'obtenerEmpleados'])->name('obtener-empleados');
    Route::post('/guardar-destinatarios', [App\Http\Controllers\ConfiguracionEnvioController::class, 'guardarDestinatarios'])->name('guardar-destinatarios');
    });

// Rutas para Envío Masivo de Encuestas
Route::middleware(['auth'])->prefix('envio-masivo')->name('envio-masivo.')->group(function () {
    Route::get('/', [App\Http\Controllers\EnvioMasivoEncuestasController::class, 'index'])->name('index');
    Route::post('/enviar', [App\Http\Controllers\EnvioMasivoEncuestasController::class, 'enviar'])->name('enviar');
    Route::get('/estadisticas', [App\Http\Controllers\EnvioMasivoEncuestasController::class, 'estadisticas'])->name('estadisticas');
    Route::get('/vista-previa', [App\Http\Controllers\EnvioMasivoEncuestasController::class, 'vistaPrevia'])->name('vista-previa');
    Route::post('/obtener-empleados', [App\Http\Controllers\EnvioMasivoEncuestasController::class, 'obtenerEmpleados'])->name('obtener-empleados');
    Route::get('/validar-configuracion', [App\Http\Controllers\EnvioMasivoEncuestasController::class, 'validarConfiguracion'])->name('validar-configuracion');
});

// Wizard de preguntas (rutas independientes)
Route::middleware(['auth', 'wizard.session'])->group(function () {
    Route::get('preguntas/wizard', [App\Http\Controllers\PreguntaWizardController::class, 'index'])->name('preguntas.wizard.index');
    Route::get('preguntas/wizard/create', [App\Http\Controllers\PreguntaWizardController::class, 'create'])->name('preguntas.wizard.create');
    Route::post('preguntas/wizard/store', [App\Http\Controllers\PreguntaWizardController::class, 'store'])->name('preguntas.wizard.store');
    Route::post('preguntas/wizard/confirm', [App\Http\Controllers\PreguntaWizardController::class, 'confirm'])->name('preguntas.wizard.confirm');
    Route::get('preguntas/wizard/cancel', [App\Http\Controllers\PreguntaWizardController::class, 'cancel'])->name('preguntas.wizard.cancel');
});

