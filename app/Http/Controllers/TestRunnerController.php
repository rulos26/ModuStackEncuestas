<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class TestRunnerController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|superadmin']);
    }

    public function index()
    {
        return view('testing.index');
    }

    public function run(Request $request)
    {
        $file = $request->input('file');
        $command = 'test';
        if ($file) {
            $command .= ' --filter=' . escapeshellarg($file);
        }
        // Ejecutar el comando y capturar la salida
        Artisan::call($command);
        $output = Artisan::output();
        return view('testing.index', [
            'output' => $output,
            'file' => $file,
        ]);
    }
}
