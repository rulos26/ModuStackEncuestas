<?php

namespace App\Imports;

use App\Models\Empleado;

class EmpleadosImport
{
    public function importarDesdeCsv($file)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $campos = array_map('strtolower', $header);
        $registros = [];
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($campos, $row);
            // Validaciones básicas
            if (
                isset($data['nombre'], $data['cargo'], $data['telefono'], $data['correo']) &&
                filter_var($data['correo'], FILTER_VALIDATE_EMAIL) &&
                preg_match('/^[0-9]{10}$/', $data['telefono']) &&
                str_word_count($data['nombre']) <= 10 &&
                str_word_count($data['cargo']) <= 10
            ) {
                // Evitar duplicados por correo
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
        fclose($handle);
    }
}
