<?php

namespace App\Imports;

use App\Models\Empleado;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EmpleadosImport
{
    public $errores = [];
    public $exitosas = 0;
    public $fallidas = 0;

    public function importar($file)
    {
        $this->errores = [];
        $this->exitosas = 0;
        $this->fallidas = 0;
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension === 'csv') {
            $this->importarDesdeCsv($file);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $this->importarDesdeExcel($file);
        } else {
            $this->errores[] = 'Formato de archivo no soportado.';
        }
        return [
            'errores' => $this->errores,
            'exitosas' => $this->exitosas,
            'fallidas' => $this->fallidas
        ];
    }

    public function importarDesdeCsv($file)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $campos = array_map('strtolower', $header);
        $fila = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $fila++;
            $data = array_combine($campos, $row);
            $this->guardarEmpleado($data, $fila);
        }
        fclose($handle);
    }

    public function importarDesdeExcel($file)
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        $header = array_map('strtolower', $rows[0]);
        foreach (array_slice($rows, 1) as $i => $row) {
            $fila = $i + 2;
            $data = array_combine($header, $row);
            $this->guardarEmpleado($data, $fila);
        }
    }

    private function guardarEmpleado($data, $fila)
    {
        if (!isset($data['nombre'], $data['telefono'], $data['correo'])) {
            $this->errores[] = "Fila $fila: Faltan campos obligatorios.";
            $this->fallidas++;
            return;
        }
        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $this->errores[] = "Fila $fila: El correo no es válido.";
            $this->fallidas++;
            return;
        }
        if (!preg_match('/^[0-9]{10}$/', $data['telefono'])) {
            $this->errores[] = "Fila $fila: El teléfono debe tener 10 dígitos numéricos.";
            $this->fallidas++;
            return;
        }
        if (str_word_count($data['nombre']) > 10) {
            $this->errores[] = "Fila $fila: El nombre tiene más de 10 palabras.";
            $this->fallidas++;
            return;
        }
        if (Empleado::where('correo_electronico', $data['correo'])->exists()) {
            $this->errores[] = "Fila $fila: El correo ya existe en la base de datos.";
            $this->fallidas++;
            return;
        }
        try {
            Empleado::create([
                'nombre' => $data['nombre'],
                'telefono' => $data['telefono'],
                'correo_electronico' => $data['correo'],
            ]);
            $this->exitosas++;
        } catch (\Exception $e) {
            $this->errores[] = "Fila $fila: Error al guardar en la base de datos.";
            $this->fallidas++;
        }
    }
}
