<?php

namespace App\Modules\SystemOptimizer\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SystemOptimizerController extends Controller
{
    /**
     * Muestra la página principal de optimización
     */
    public function index()
    {
        return view('system_optimizer.index');
    }

    /**
     * Limpia todas las cachés del sistema
     */
    public function clearCaches()
    {
        $results = [];

        try {
            // Limpiar caché de configuración
            Artisan::call('config:clear');
            $results['config'] = [
                'success' => true,
                'message' => 'Caché de configuración limpiada correctamente',
                'output' => Artisan::output()
            ];
        } catch (\Exception $e) {
            $results['config'] = [
                'success' => false,
                'message' => 'Error al limpiar caché de configuración: ' . $e->getMessage()
            ];
            Log::error('Error clearing config cache: ' . $e->getMessage());
        }

        try {
            // Limpiar caché de rutas
            Artisan::call('route:clear');
            $results['route'] = [
                'success' => true,
                'message' => 'Caché de rutas limpiada correctamente',
                'output' => Artisan::output()
            ];
        } catch (\Exception $e) {
            $results['route'] = [
                'success' => false,
                'message' => 'Error al limpiar caché de rutas: ' . $e->getMessage()
            ];
            Log::error('Error clearing route cache: ' . $e->getMessage());
        }

        try {
            // Limpiar caché de vistas
            Artisan::call('view:clear');
            $results['view'] = [
                'success' => true,
                'message' => 'Caché de vistas limpiada correctamente',
                'output' => Artisan::output()
            ];
        } catch (\Exception $e) {
            $results['view'] = [
                'success' => false,
                'message' => 'Error al limpiar caché de vistas: ' . $e->getMessage()
            ];
            Log::error('Error clearing view cache: ' . $e->getMessage());
        }

        try {
            // Limpiar caché de aplicación
            Artisan::call('cache:clear');
            $results['cache'] = [
                'success' => true,
                'message' => 'Caché de aplicación limpiada correctamente',
                'output' => Artisan::output()
            ];
        } catch (\Exception $e) {
            $results['cache'] = [
                'success' => false,
                'message' => 'Error al limpiar caché de aplicación: ' . $e->getMessage()
            ];
            Log::error('Error clearing application cache: ' . $e->getMessage());
        }

        return response()->json($results);
    }

    /**
     * Regenera el autoloader de Composer
     */
    public function dumpAutoload()
    {
        try {
            $command = 'composer dump-autoload';
            $output = [];
            $returnCode = 0;

            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode === 0) {
                return response()->json([
                   'success' => true,
                   'message' => 'Autoloader de Composer regenerado correctamente',
                  'output' => implode("\n", $output)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                'message' => 'Error al regenerar autoloader de Composer',
                  'output' => implode("\n", $output)
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error dumping composer autoload: ' . $e->getMessage());
            return response()->json([
                'success' => false,
            'message' => 'Error al regenerar autoloader: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Optimiza las rutas del sistema
     */
    public function optimizeRoutes()
    {
        try {
            Artisan::call('route:cache');
            return response()->json([
               'success' => true,
             'message' => 'Rutas optimizadas correctamente',
             'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            Log::error('Error optimizing routes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
            'message' => 'Error al optimizar rutas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Limpia archivos temporales
     */
    public function clearTempFiles()
    {
        $results = [];
        $tempPaths = [
            storage_path('app/temp'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($tempPaths as $path) {
            if (is_dir($path)) {
                try {
                    $files = glob($path . '/*');
                    $deletedCount = 0;

                    foreach ($files as $file) {
                        if (is_file($file) && unlink($file)) {
                            $deletedCount++;
                        }
                    }

                    $results[basename($path)] = [
                       'success' => true,
                        'message' => 'Limpieza completada en ' . $path,
                        'deleted_files' => $deletedCount
                    ];
                } catch (\Exception $e) {
                    $results[basename($path)] = [
                        'success' => false,
                    'message' => 'Error limpiando ' . $path . ': ' . $e->getMessage()
                    ];
                }
            }
        }

        return response()->json($results);
    }

    /**
     * Ejecuta todas las optimizaciones
     */
    public function optimizeAll()
    {
        $results = [];

        // Limpiar cachés
        $cacheResults = $this->clearCaches();
        $results['caches'] = json_decode($cacheResults->getContent(), true);

        // Regenerar autoloader
        $autoloadResult = $this->dumpAutoload();
        $results['autoload'] = json_decode($autoloadResult->getContent(), true);

        // Optimizar rutas
        $routesResult = $this->optimizeRoutes();
        $results['routes'] = json_decode($routesResult->getContent(), true);

        // Limpiar archivos temporales
        $tempResult = $this->clearTempFiles();
        $results['temp_files'] = json_decode($tempResult->getContent(), true);

        return response()->json($results);
    }
}
