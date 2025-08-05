<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class TestRunnerController extends Controller
{
    public function __construct()
    {
        //$this->middleware(['auth', 'role:admin|superadmin']);
    }

    public function index()
    {
        return view('testing.index');
    }

    public function run(Request $request)
    {
        try {
            $command = $request->input('command', 'test');
            $options = $request->input('options', []);
            $configuracionId = $request->input('configuracion_id');
            $file = $request->input('file');

            // Construir el comando completo
            $fullCommand = $command;

            // Agregar opciones
            if (!empty($options)) {
                $fullCommand .= ' ' . implode(' ', $options);
            }

            // Agregar configuración específica si se proporciona
            if ($configuracionId) {
                $fullCommand .= ' --configuracion-id=' . $configuracionId;
            }

            // Manejar comandos específicos
            if ($command === 'limpiar_hora_envio') {
                $fullCommand = 'limpiar:hora-envio';
                if (in_array('--dry-run', $options)) {
                    $fullCommand .= ' --dry-run';
                }
            } else if ($command === 'corregir_configuraciones_envio') {
                $fullCommand = 'corregir:configuraciones-envio';
                if ($configuracionId) {
                    $fullCommand .= ' --configuracion-id=' . $configuracionId;
                }
                if (in_array('--force', $options)) {
                    $fullCommand .= ' --force';
                }
            } else if ($command === 'verificar_empresas') {
                $fullCommand = 'verificar:empresas';
                if ($configuracionId) {
                    $fullCommand .= ' --empresa-id=' . $configuracionId;
                }
            } else if ($command === 'crear_empresa_prueba') {
                $fullCommand = 'crear:empresa-prueba';
                if ($configuracionId) {
                    $fullCommand .= ' --nombre=' . $configuracionId;
                }
                if (in_array('--force', $options)) {
                    $fullCommand .= ' --force';
                }
            }

            // Agregar filtro de archivo para comandos de test
            if ($file && $command === 'test') {
                $fullCommand .= ' --filter=' . escapeshellarg($file);
            }

            // Ejecutar el comando y capturar la salida
            Artisan::call($fullCommand);
            $output = Artisan::output();

            // Determinar si fue exitoso
            $isSuccess = !empty($output) && !str_contains($output, 'Error') && !str_contains($output, 'Exception');

            return view('testing.index', [
                'output' => $output,
                'command' => $fullCommand,
                'isSuccess' => $isSuccess,
                'configuracion_id' => $configuracionId,
                'file' => $file,
            ])->with($isSuccess ? 'success' : 'error',
                $isSuccess ? 'Comando ejecutado correctamente' : 'Error al ejecutar el comando');

        } catch (\Exception $e) {
            $errorMessage = 'Error ejecutando comando: ' . $e->getMessage();

            return view('testing.index', [
                'output' => $errorMessage,
                'command' => $command ?? 'unknown',
                'isSuccess' => false,
            ])->with('error', $errorMessage);
        }
    }

    /**
     * Ejecutar comando específico y devolver resultado JSON
     */
    public function runCommand(Request $request)
    {
        try {
            $command = $request->input('command');
            $options = $request->input('options', []);

            if (!$command) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comando requerido'
                ], 400);
            }

            // Construir comando
            $fullCommand = $command;
            if (!empty($options)) {
                $fullCommand .= ' ' . implode(' ', $options);
            }

            // Ejecutar comando
            Artisan::call($fullCommand);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'output' => $output,
                'command' => $fullCommand
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información del sistema
     */
    public function systemInfo()
    {
        try {
            $info = [
                'database' => [
                    'default' => config('database.default'),
                    'connections' => array_keys(config('database.connections')),
                ],
                'queue' => [
                    'default' => config('queue.default'),
                    'connections' => array_keys(config('queue.connections')),
                ],
                'app' => [
                    'timezone' => config('app.timezone'),
                    'locale' => config('app.locale'),
                    'debug' => config('app.debug'),
                ],
                'current_time' => now()->format('Y-m-d H:i:s'),
            ];

            return response()->json([
                'success' => true,
                'data' => $info
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo información: ' . $e->getMessage()
            ], 500);
        }
    }
}
