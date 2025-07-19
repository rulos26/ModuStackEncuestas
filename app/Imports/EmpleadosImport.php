<?php

namespace App\Imports;

use App\Models\Empleado;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmpleadosImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Empleado([
            'nombre' => $row['nombre'],
            'cargo' => $row['cargo'],
            'telefono' => $row['telefono'],
            'correo_electronico' => $row['correo_electronico'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nombre' => 'required|string|max:255',
            '*.cargo' => 'required|string|max:255',
            '*.telefono' => 'required|string|max:20',
            '*.correo_electronico' => 'required|email|unique:empleados,correo_electronico',
        ];
    }
}
