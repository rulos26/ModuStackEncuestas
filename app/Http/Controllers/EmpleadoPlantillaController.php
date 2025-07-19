<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Response;

class EmpleadoPlantillaController extends Controller
{
    public function plantillas()
    {
        return view('empleados.plantillas');
    }

    public function descargarExcel()
    {
        $headers = ['Nombre', 'Cargo', 'Teléfono', 'Correo'];
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };
        // Usar Excel::download para xlsx
        return Excel::download(new \App\Exports\EmpleadoPlantillaExport, 'plantilla_empleados.xlsx');
    }

    public function descargarCsv()
    {
        $headers = ['Nombre', 'Cargo', 'Teléfono', 'Correo'];
        $callback = function() use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };
        return Response::stream($callback, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=plantilla_empleados.csv"
        ]);
    }
}
