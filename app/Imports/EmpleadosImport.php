<?php

namespace App\Imports;

use App\Models\Empleado;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmpleadosImport
{
    public function importar($file)
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension === 'csv') {
            $this->importarDesdeCsv($file);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $this->importarDesdeExcel($file);
        } else {
            throw new \Exception('Formato de archivo no soportado.');
        }
    }

    public function importarDesdeCsv($file)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $campos = array_map('strtolower', $header);
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($campos, $row);
            $this->guardarEmpleado($data);
        }
        fclose($handle);
    }

    public function importarDesdeExcel($file)
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $header = array_map('strtolower', $rows[0]);
        foreach (array_slice($rows, 1) as $row) {
            $data = array_combine($header, $row);
            $this->guardarEmpleado($data);
        }
    }

    private function guardarEmpleado($data)
    {
        if (
            isset($data['nombre'], $data['cargo'], $data['telefono'], $data['correo']) &&
            filter_var($data['correo'], FILTER_VALIDATE_EMAIL) &&
            preg_match('/^[0-9]{10}$/', $data['telefono']) &&
            str_word_count($data['nombre']) <= 10 &&
            str_word_count($data['cargo']) <= 10
        ) {
            if (!Empleado::where('correo_electronico', $data['correo'])->exists()) {
                Empleado::create([
                    'nombre' => $data['nombre'],
                    'cargo' => $data['cargo'],
                    'telefono' => $data['telefono'],
                    'correo_electronico' => $data['correo'],
                ]);
            }
        }
    }
}
