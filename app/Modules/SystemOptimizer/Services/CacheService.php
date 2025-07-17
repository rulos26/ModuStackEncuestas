<?php

namespace App\Modules\SystemOptimizer\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Limpia todas las cachés del sistema
     */
    public function clearAllCaches()
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

        return $results;
    }

    /**
     * Optimiza las rutas del sistema
     */
    public function optimizeRoutes()
    {
        try {
            Artisan::call('route:cache');
            return [
                'success' => true,
                'message' => 'Rutas optimizadas correctamente',
                'output' => Artisan::output()
            ];
        } catch (\Exception $e) {
            Log::error('Error optimizing routes: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al optimizar rutas: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Limpia archivos temporales del sistema
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

        return $results;
    }
}
