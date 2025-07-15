<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function index()
    {
        // Solo usuarios autenticados pueden ver logs (ya está protegido por middleware)
        $logPath = storage_path('logs/laravel.log');
        $logContent = '';
        if (file_exists($logPath)) {
            // Leer solo los últimos 5000 caracteres para no saturar la vista
            $logContent = File::get($logPath);
            $logContent = substr($logContent, -5000);
        }
        return view('logs.index', compact('logContent'));
    }

    public function module()
    {
        $logPath = storage_path('logs/module_error.log');
        $logContent = '';
        if (file_exists($logPath)) {
            $logContent = \Illuminate\Support\Facades\File::get($logPath);
            $logContent = substr($logContent, -5000);
        }
        return view('logs.module', compact('logContent'));
    }

    /**
     * Registrar un error personalizado en el log del módulo
     * Uso: LogController::logModuleError('Mensaje de error');
     */
    public static function logModuleError($message)
    {
        $logPath = storage_path('logs/module_error.log');
        $date = date('Y-m-d H:i:s');
        $entry = "[$date] $message\n";
        file_put_contents($logPath, $entry, FILE_APPEND);
    }
}
