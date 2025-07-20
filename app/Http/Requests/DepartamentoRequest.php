<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartamentoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $departamentoId = $this->route('departamento')?->id;
        return [
            'nombre' => 'required|string|max:255|unique:departamentos,nombre' . ($departamentoId ? ",{$departamentoId}" : ''),
            'pais_id' => 'required|exists:paises,id',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del departamento es obligatorio.',
            'nombre.unique' => 'El nombre del departamento ya existe.',
            'pais_id.required' => 'El país es obligatorio.',
            'pais_id.exists' => 'El país seleccionado no existe.',
        ];
    }
}
