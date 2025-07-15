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
}
