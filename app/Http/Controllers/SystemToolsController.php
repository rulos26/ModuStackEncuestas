<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Exception;

class SystemToolsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard principal de herramientas del sistema
     */
    public function dashboard()
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['system.tools'])) {
                return $this->redirectIfNoAccess('No tienes permisos para acceder a las herramientas del sistema.');
            }

            // Obtener información del sistema
            $systemInfo = $this->getSystemInfo();

            // Obtener estado de las migraciones
            $migrationStatus = $this->getMigrationStatus();

            // Obtener estadísticas de la base de datos
            $databaseStats = $this->getDatabaseStats();

            return view('system.tools.dashboard', compact('systemInfo', 'migrationStatus', 'databaseStats'));
        } catch (Exception $e) {
            Log::error('Error en dashboard de herramientas del sistema', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error al cargar el dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Vista de diagnóstico de encuestas
     */
    public function diagnosticarEncuestas(Request $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['system.tools'])) {
                return $this->redirectIfNoAccess('No tienes permisos para acceder a las herramientas del sistema.');
            }

            $encuestaId = $request->get('encuesta_id');
            $resultado = null;

            if ($request->has('ejecutar')) {
                $resultado = $this->ejecutarComando('encuestas:diagnosticar', [
                    '--encuesta_id' => $encuestaId
                ]);
            }

            return view('system.tools.diagnosticar_encuestas', compact('resultado', 'encuestaId'));
        } catch (Exception $e) {
            Log::error('Error en diagnóstico de encuestas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error en diagnóstico: ' . $e->getMessage());
        }
    }

    /**
     * Vista de diagnóstico de preguntas
     */
    public function diagnosticarPreguntas(Request $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['system.tools'])) {
                return $this->redirectIfNoAccess('No tienes permisos para acceder a las herramientas del sistema.');
            }

            $encuestaId = $request->get('encuesta_id');
            $crearPrueba = $request->has('crear_prueba');
            $resultado = null;

            if ($request->has('ejecutar')) {
                $opciones = [];
                if ($encuestaId) $opciones['--encuesta_id'] = $encuestaId;
                if ($crearPrueba) $opciones['--crear_prueba'] = true;

                $resultado = $this->ejecutarComando('preguntas:diagnosticar', $opciones);
            }

            return view('system.tools.diagnosticar_preguntas', compact('resultado', 'encuestaId', 'crearPrueba'));
        } catch (Exception $e) {
            Log::error('Error en diagnóstico de preguntas', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error en diagnóstico: ' . $e->getMessage());
        }
    }

    /**
     * Vista de migraciones
     */
    public function migraciones(Request $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['system.tools'])) {
                return $this->redirectIfNoAccess('No tienes permisos para acceder a las herramientas del sistema.');
            }

            $resultado = null;
            $migrationStatus = $this->getMigrationStatus();

            if ($request->has('ejecutar')) {
                $tipo = $request->get('tipo', 'all');

                switch ($tipo) {
                    case 'status':
                        $resultado = $this->ejecutarComando('migrate:status');
                        break;
                    case 'run':
                        $resultado = $this->ejecutarComando('migrate');
                        break;
                    case 'rollback':
                        $resultado = $this->ejecutarComando('migrate:rollback');
                        break;
                    case 'refresh':
                        $resultado = $this->ejecutarComando('migrate:refresh');
                        break;
                    case 'preguntas':
                        $resultado = $this->ejecutarComando('preguntas:verificar-migraciones', ['--ejecutar' => true]);
                        break;
                    case 'fechas_encuestas':
                        $resultado = $this->ejecutarComando('migrate', ['--path' => 'database/migrations/2025_07_13_091000_agregar_fechas_encuestas.php']);
                        break;
                    case 'limpiar_encuestas':
                        $resultado = $this->ejecutarComando('migraciones:limpiar-encuestas', ['--ejecutar' => true]);
                        break;
                }
            }

            return view('system.tools.migraciones', compact('resultado', 'migrationStatus'));
        } catch (Exception $e) {
            Log::error('Error en migraciones', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error en migraciones: ' . $e->getMessage());
        }
    }

    /**
     * Vista de seeders
     */
    public function seeders(Request $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['system.tools'])) {
                return $this->redirectIfNoAccess('No tienes permisos para acceder a las herramientas del sistema.');
            }

            $resultado = null;
            $seeders = $this->getAvailableSeeders();

            if ($request->has('ejecutar')) {
                $seeder = $request->get('seeder', 'all');

                if ($seeder === 'all') {
                    $resultado = $this->ejecutarComando('db:seed');
                } else {
                    $resultado = $this->ejecutarComando('db:seed', ['--class' => $seeder]);
                }
            }

            return view('system.tools.seeders', compact('resultado', 'seeders'));
        } catch (Exception $e) {
            Log::error('Error en seeders', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error en seeders: ' . $e->getMessage());
        }
    }

    /**
     * Vista de pruebas del sistema
     */
    public function pruebas(Request $request)
    {
        try {
            // Verificar permisos
            if (!$this->checkUserAccess(['system.tools'])) {
                return $this->redirectIfNoAccess('No tienes permisos para acceder a las herramientas del sistema.');
            }

            $resultado = null;
            $tipo = $request->get('tipo');

            if ($request->has('ejecutar')) {
                switch ($tipo) {
                    case 'encuestas':
                        $resultado = $this->ejecutarComando('encuestas:probar-creacion');
                        break;
                    case 'preguntas':
                        $encuestaId = $request->get('encuesta_id');
                        $opciones = [];
                        if ($encuestaId) $opciones['--encuesta_id'] = $encuestaId;
                        $resultado = $this->ejecutarComando('preguntas:probar-creacion', $opciones);
                        break;
                    case 'sistema':
                        $resultado = $this->ejecutarComando('encuestas:probar-sistema');
                        break;
                    case 'fechas':
                        $resultado = $this->ejecutarComando('encuestas:diagnosticar-fechas');
                        break;
                    case 'limpiar':
                        $resultado = $this->ejecutarComando('migraciones:limpiar-encuestas');
                        break;
                    case 'creacion_preguntas':
                        $encuestaId = $request->get('encuesta_id');
                        $opciones = [];
                        if ($encuestaId) $opciones['--encuesta_id'] = $encuestaId;
                        if ($request->get('crear_prueba')) $opciones['--crear_prueba'] = true;
                        $resultado = $this->ejecutarComando('preguntas:diagnosticar-creacion', $opciones);
                        break;
                    case 'simular_pregunta':
                        $encuestaId = $request->get('encuesta_id');
                        $opciones = [];
                        if ($encuestaId) $opciones['--encuesta_id'] = $encuestaId;
                        $resultado = $this->ejecutarComando('preguntas:simular-creacion', $opciones);
                        break;
                    case 'verificar_bd':
                        $opciones = [];
                        if ($request->get('corregir')) $opciones['--corregir'] = true;
                        $resultado = $this->ejecutarComando('bd:verificar-configuracion', $opciones);
                        break;
                    case 'estado_encuesta':
                        $encuestaId = $request->get('encuesta_id');
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $resultado = $this->ejecutarComando('encuesta:diagnosticar-estado', ['encuesta_id' => $encuestaId]);
                        }
                        break;
                    case 'probar_envio':
                        $encuestaId = $request->get('encuesta_id');
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $resultado = $this->ejecutarComando('envio:probar-configuracion', ['encuesta_id' => $encuestaId]);
                        }
                        break;
                    case 'diagnosticar_tipos':
                        $encuestaId = $request->get('encuesta_id');
                        $debug = $request->get('debug', false);
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $params = ['--encuesta_id' => $encuestaId];
                            if ($debug) {
                                $params['--debug'] = true;
                            }
                            $resultado = $this->ejecutarComando('preguntas:diagnosticar-tipos', $params);
                        }
                        break;
                    case 'diagnosticar_progreso':
                        $encuestaId = $request->get('encuesta_id');
                        $debug = $request->get('debug', false);
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $params = ['encuesta_id' => $encuestaId];
                            if ($debug) {
                                $params['--debug'] = true;
                            }
                            $resultado = $this->ejecutarComando('encuesta:diagnosticar-progreso', $params);
                        }
                        break;
                    case 'forzar_validaciones':
                        $encuestaId = $request->get('encuesta_id');
                        $debug = $request->get('debug', false);
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $params = ['encuesta_id' => $encuestaId];
                            if ($debug) {
                                $params['--debug'] = true;
                            }
                            $resultado = $this->ejecutarComando('encuesta:forzar-validaciones', $params);
                        }
                        break;
                    case 'diagnosticar_envio_correos':
                        $encuestaId = $request->get('encuesta_id');
                        if (!$encuestaId) {
                            $resultado = $this->ejecutarComando('diagnosticar:envio-correos');
                        } else {
                            $resultado = $this->ejecutarComando('diagnosticar:envio-correos', ['encuesta_id' => $encuestaId]);
                        }
                        break;
                    case 'probar_cron_job':
                        $debug = $request->get('debug', false);
                        $opciones = [];
                        if ($debug) {
                            $opciones['--debug'] = true;
                        }
                        $resultado = $this->ejecutarComando('probar:cron-job', $opciones);
                        break;
                    case 'verificar_sistema_colas':
                        $fix = $request->get('fix', false);
                        $opciones = [];
                        if ($fix) {
                            $opciones['--fix'] = true;
                        }
                        $resultado = $this->ejecutarComando('verificar:sistema-colas', $opciones);
                        break;
                    case 'probar_envio_correos':
                        $configuracionId = $request->get('configuracion_id');
                        $test = $request->get('test', false);
                        $force = $request->get('force', false);
                        $opciones = [];
                        if ($test) {
                            $opciones['--test'] = true;
                        }
                        if ($force) {
                            $opciones['--force'] = true;
                        }
                        if ($configuracionId) {
                            $opciones['--configuracion-id'] = $configuracionId;
                        }
                        $resultado = $this->ejecutarComando('probar:envio-correos', $opciones);
                        break;
                    case 'ejecutar_cron_job':
                        $force = $request->get('force', false);
                        $opciones = [];
                        if ($force) {
                            $opciones['--force'] = true;
                        }
                        $resultado = $this->ejecutarComando('ejecutar:cron-job', $opciones);
                        break;
                    case 'diagnosticar_configuracion_envio':
                        $empresaId = $request->get('empresa_id');
                        $encuestaId = $request->get('encuesta_id');
                        $opciones = [];
                        if ($empresaId) {
                            $opciones['--empresa-id'] = $empresaId;
                        }
                        if ($encuestaId) {
                            $opciones['--encuesta-id'] = $encuestaId;
                        }
                        $resultado = $this->ejecutarComando('diagnosticar:configuracion-envio', $opciones);
                        break;
                    case 'corregir_configuraciones_envio':
                        $configuracionId = $request->get('configuracion_id');
                        $tipoDestinatario = $request->get('tipo_destinatario', 'empleados');
                        $dryRun = $request->get('dry_run', false);
                        $opciones = [];
                        if ($configuracionId) {
                            $opciones['--configuracion-id'] = $configuracionId;
                        }
                        if ($tipoDestinatario) {
                            $opciones['--tipo-destinatario'] = $tipoDestinatario;
                        }
                        if ($dryRun) {
                            $opciones['--dry-run'] = true;
                        }
                        $resultado = $this->ejecutarComando('corregir:configuraciones-envio', $opciones);
                        break;
                    case 'probar_dashboard':
                        $encuestaId = $request->get('encuesta_id');
                        $debug = $request->get('debug', false);
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $params = ['encuesta_id' => $encuestaId];
                            if ($debug) {
                                $params['--debug'] = true;
                            }
                            $resultado = $this->ejecutarComando('dashboard:probar-seguimiento', $params);
                        }
                        break;
                    case 'diagnosticar_dashboard':
                        $encuestaId = $request->get('encuesta_id');
                        $debug = $request->get('debug', false);
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $params = ['encuesta_id' => $encuestaId];
                            if ($debug) {
                                $params['--debug'] = true;
                            }
                            $resultado = $this->ejecutarComando('dashboard:diagnosticar', $params);
                        }
                        break;
                    case 'migracion_sent_mails':
                        $debug = $request->get('debug', false);
                        $params = [];
                        if ($debug) {
                            $params['--debug'] = true;
                        }
                        $resultado = $this->ejecutarComando('migracion:sent-mails', $params);
                        break;
                    case 'migracion_configuracion_envio':
                        $resultado = $this->ejecutarComando('probar:migracion-configuracion-envio');
                        break;
                    case 'diagnosticar_tablas_migracion':
                        $resultado = $this->ejecutarComando('diagnosticar:tablas-migracion');
                        break;
                    case 'probar_migracion_simple':
                        $resultado = $this->ejecutarComando('probar:migracion-simple');
                        break;
                    case 'verificar_tabla_empresas':
                        $resultado = $this->ejecutarComando('verificar:tabla-empresas');
                        break;
                    case 'diagnosticar_paso3_configuracion':
                        $empresaId = $request->get('empresa_id');
                        $encuestaIds = $request->get('encuesta_ids');
                        if ($empresaId && $encuestaIds) {
                            $resultado = $this->ejecutarComando('diagnosticar:paso3-configuracion', [
                                'empresa_id' => $empresaId,
                                'encuesta_ids' => $encuestaIds
                            ]);
                        } else {
                            $resultado = $this->ejecutarComando('diagnosticar:paso3-configuracion');
                        }
                        break;
                    case 'corregir_user_id':
                        $encuestaId = $request->get('encuesta_id');
                        $userId = $request->get('user_id');
                        $debug = $request->get('debug', false);
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $params = ['encuesta_id' => $encuestaId];
                            if ($userId) {
                                $params['--user_id'] = $userId;
                            }
                            if ($debug) {
                                $params['--debug'] = true;
                            }
                            $resultado = $this->ejecutarComando('encuesta:corregir-user-id', $params);
                        }
                        break;
                    case 'debug_dashboard':
                        $encuestaId = $request->get('encuesta_id');
                        $userId = $request->get('user_id');
                        $debug = $request->get('debug', false);
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $params = ['encuesta_id' => $encuestaId];
                            if ($userId) {
                                $params['--user_id'] = $userId;
                            }
                            if ($debug) {
                                $params['--debug'] = true;
                            }
                            $resultado = $this->ejecutarComando('debug:dashboard-encuesta', $params);
                        }
                        break;
                    case 'verificar_enum':
                        $resultado = $this->ejecutarComando('verificar:enum-estado');
                        break;
                    case 'tester_flujo_completo':
                        $email = $request->get('email');
                        $cantidad = $request->get('cantidad');
                        $params = [];
                        if ($email) $params['--email'] = $email;
                        if ($cantidad) $params['--cantidad'] = $cantidad;
                        $resultado = $this->ejecutarComando('tester:flujo-completo', $params);
                        break;
                    case 'publicar_encuesta':
                        $encuestaId = $request->get('encuesta_id');
                        $email = $request->get('email');
                        $horas = $request->get('horas');
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $params = ['encuesta_id' => $encuestaId];
                            if ($email) $params['--email'] = $email;
                            if ($horas) $params['--horas'] = $horas;
                            $resultado = $this->ejecutarComando('encuesta:publicar-y-generar-enlace', $params);
                        }
                        break;
                    case 'verificar_respuestas':
                        $encuestaId = $request->get('encuesta_id');
                        if (!$encuestaId) {
                            $resultado = "❌ Error: Debes proporcionar el ID de la encuesta";
                        } else {
                            $resultado = $this->ejecutarComando('encuesta:verificar-respuestas', ['encuesta_id' => $encuestaId]);
                        }
                        break;
                    case 'configurar_sesiones':
                        $resultado = $this->ejecutarComando('configurar:sesiones-hosting');
                        break;
                    case 'verificar_escala':
                        $encuestaId = $request->get('encuesta_id');
                        $params = [];
                        if ($encuestaId) $params['--encuesta_id'] = $encuestaId;
                        $resultado = $this->ejecutarComando('verificar:escala-preguntas', $params);
                        break;
                    case 'diagnosticar_error_publica':
                        $encuestaId = $request->get('encuesta_id');
                        $params = [];
                        if ($encuestaId) $params['--encuesta_id'] = $encuestaId;
                        $resultado = $this->ejecutarComando('diagnosticar:error-publica', $params);
                        break;
                    case 'solucionar_hosting_completa':
                        $resultado = $this->ejecutarComando('solucionar:hosting-completa');
                        break;
                    case 'solucionar_csrf_hosting':
                        $resultado = $this->ejecutarComando('solucionar:csrf-hosting');
                        break;
                    case 'solucion_definitiva_hosting':
                        $resultado = $this->ejecutarComando('solucion:definitiva-hosting');
                        break;
                    case 'emergency_hosting_fix':
                        $resultado = $this->ejecutarComando('emergency:hosting-fix');
                        break;
                    case 'diagnosticar_flujo_publica':
                        $encuestaId = $request->get('encuesta_id');
                        $params = [];
                        if ($encuestaId) $params['--encuesta_id'] = $encuestaId;
                        $resultado = $this->ejecutarComando('diagnosticar:flujo-encuesta-publica', $params);
                        break;
                    case 'revisar_logs_prueba':
                        $resultado = $this->ejecutarComando('revisar:logs-prueba');
                        break;
                    case 'fix_session_419':
                        $resultado = $this->ejecutarComando('fix:session-419');
                        break;
                    case 'diagnosticar_redireccion_fin':
                        $resultado = $this->ejecutarComando('diagnosticar:redireccion-fin');
                        break;
                    case 'probar_numero_encuestas':
                        $encuestaId = $request->get('encuesta_id');
                        $params = [];
                        if ($encuestaId) $params['--encuesta_id'] = $encuestaId;
                        $resultado = $this->ejecutarComando('probar:numero-encuestas', $params);
                        break;
                    case 'probar_contadores_encuesta':
                        $encuestaId = $request->get('encuesta_id');
                        $params = [];
                        if ($encuestaId) $params['--encuesta_id'] = $encuestaId;
                        $resultado = $this->ejecutarComando('probar:contadores-encuesta', $params);
                        break;
                }
            }

            return view('system.tools.pruebas', compact('resultado', 'tipo'));
        } catch (Exception $e) {
            Log::error('Error en pruebas del sistema', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Error en pruebas: ' . $e->getMessage());
        }
    }

    /**
     * Ejecutar comando Artisan
     */
    private function ejecutarComando($comando, $opciones = [])
    {
        try {
            $output = new \Symfony\Component\Console\Output\BufferedOutput;
            $exitCode = Artisan::call($comando, $opciones, $output);

            return [
                'comando' => $comando,
                'opciones' => $opciones,
                'exit_code' => $exitCode,
                'output' => $output->fetch(),
                'success' => $exitCode === 0
            ];
        } catch (Exception $e) {
            return [
                'comando' => $comando,
                'opciones' => $opciones,
                'exit_code' => 1,
                'output' => 'Error: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }

    /**
     * Obtener información del sistema
     */
    private function getSystemInfo()
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
            'database_name' => config('database.connections.' . config('database.default') . '.database'),
            'app_environment' => config('app.env'),
            'app_debug' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale')
        ];
    }

    /**
     * Obtener estado de las migraciones
     */
    private function getMigrationStatus()
    {
        try {
            $output = new \Symfony\Component\Console\Output\BufferedOutput;
            Artisan::call('migrate:status', [], $output);

            return [
                'output' => $output->fetch(),
                'success' => true
            ];
        } catch (Exception $e) {
            return [
                'output' => 'Error: ' . $e->getMessage(),
                'success' => false
            ];
        }
    }

    /**
     * Obtener estadísticas de la base de datos
     */
    private function getDatabaseStats()
    {
        try {
            $tables = [
                'users' => 'Usuarios',
                'empresa' => 'Empresas',
                'empresas_clientes' => 'Empresas Clientes',
                'encuestas' => 'Encuestas',
                'preguntas' => 'Preguntas',
                'respuestas' => 'Respuestas',
                'sent_mails' => 'Correos Enviados',
                'bloques_envio' => 'Bloques de Envío',
                'tokens_encuesta' => 'Tokens de Encuesta'
            ];

            $stats = [];
            foreach ($tables as $table => $nombre) {
                if (Schema::hasTable($table)) {
                    $stats[$nombre] = DB::table($table)->count();
                } else {
                    $stats[$nombre] = 'Tabla no existe';
                }
            }

            return $stats;
        } catch (Exception $e) {
            return ['Error' => $e->getMessage()];
        }
    }

    /**
     * Obtener seeders disponibles
     */
    private function getAvailableSeeders()
    {
        $seeders = [
            'DatabaseSeeder' => 'Seeder Principal',
            'UserSeeder' => 'Seeder de Usuarios',
            'roleSeeder' => 'Seeder de Roles',
            'EmpresaSeeder' => 'Seeder de Empresas',
            'TokenSeeder' => 'Seeder de Tokens'
        ];

        return $seeders;
    }

    /**
     * Verificar acceso del usuario
     */
    private function checkUserAccess(array $requiredPermissions = []): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        // Superadmin tiene acceso total
        if ($this->userHasRole('Superadmin')) {
            return true;
        }

        // Verificar permisos específicos
        if (!empty($requiredPermissions)) {
            foreach ($requiredPermissions as $permission) {
                if ($this->userHasPermission($permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    private function userHasRole(string $role): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        try {
            return $user->hasRole($role);
        } catch (\Exception $e) {
            Log::error('Error verificando rol del usuario', [
                'user_id' => $user->id,
                'role' => $role,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    private function userHasPermission(string $permission): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        try {
            return $user->hasPermissionTo($permission);
        } catch (\Exception $e) {
            Log::error('Error verificando permiso del usuario', [
                'user_id' => $user->id,
                'permission' => $permission,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Redirigir si no tiene acceso
     */
    private function redirectIfNoAccess(string $message): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('home')->with('error', $message);
    }
}
