<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EmpleadoPlantillaController extends Controller
{
    public function plantillas()
    {
        return view('empleados.plantillas');
    }

    public function descargarExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['Nombre', 'Cargo', 'Teléfono', 'Correo']
        ], null, 'A1');

        $writer = new Xlsx($spreadsheet);
        $filename = 'plantilla_empleados.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function descargarCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plantilla_empleados.csv"',
        ];
        $callback = function() {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nombre', 'Cargo', 'Teléfono', 'Correo']);
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
