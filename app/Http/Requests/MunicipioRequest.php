<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MunicipioRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $municipioId = $this->route('municipio')?->id;
        return [
            'nombre' => 'required|string|max:255|unique:municipios,nombre' . ($municipioId ? ",{$municipioId}" : ''),
            'estado' => 'required|boolean',
            'departamento_id' => 'required|exists:departamentos,id',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del municipio es obligatorio.',
            'nombre.unique' => 'El nombre del municipio ya existe.',
            'estado.required' => 'El estado es obligatorio.',
            'departamento_id.required' => 'El departamento es obligatorio.',
            'departamento_id.exists' => 'El departamento seleccionado no existe.',
        ];
    }
}
