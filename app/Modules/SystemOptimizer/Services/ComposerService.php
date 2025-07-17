<?php

namespace App\Modules\SystemOptimizer\Services;

use Illuminate\Support\Facades\Log;

class ComposerService
{
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
                return [
                    'success' => true,
                    'message' => 'Autoloader de Composer regenerado correctamente',
                    'output' => implode("\n", $output)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al regenerar autoloader de Composer',
                    'output' => implode("\n", $output)
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error dumping composer autoload: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al regenerar autoloader: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verifica las dependencias de Composer
     */
    public function checkDependencies()
    {
        try {
            $command = 'composer check-platform-reqs';
            $output = [];
            $returnCode = 0;

            exec($command . ' 2>&1', $output, $returnCode);

            return [
                'success' => $returnCode === 0,
                'message' => $returnCode === 0 ? 'Dependencias verificadas correctamente' : 'Problemas encontrados en las dependencias',
                'output' => implode("\n", $output)
            ];
        } catch (\Exception $e) {
            Log::error('Error checking composer dependencies: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al verificar dependencias: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza las dependencias de Composer
     */
    public function updateDependencies()
    {
        try {
            $command = 'composer update --no-dev --optimize-autoloader';
            $output = [];
            $returnCode = 0;

            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode === 0) {
                return [
                    'success' => true,
                    'message' => 'Dependencias actualizadas correctamente',
                    'output' => implode("\n", $output)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar dependencias',
                    'output' => implode("\n", $output)
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error updating composer dependencies: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar dependencias: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Instala las dependencias de Composer
     */
    public function installDependencies()
    {
        try {
            $command = 'composer install --no-dev --optimize-autoloader';
            $output = [];
            $returnCode = 0;

            exec($command . ' 2>&1', $output, $returnCode);

            if ($returnCode === 0) {
                return [
                    'success' => true,
                    'message' => 'Dependencias instaladas correctamente',
                    'output' => implode("\n", $output)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al instalar dependencias',
                    'output' => implode("\n", $output)
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error installing composer dependencies: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al instalar dependencias: ' . $e->getMessage()
            ];
        }
    }
}
