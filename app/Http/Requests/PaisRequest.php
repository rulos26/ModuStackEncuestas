<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaisRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $paisId = $this->route('pais')?->id;
        return [
            'name' => 'required|string|max:255|unique:paises,name' . ($paisId ? ",{$paisId}" : ''),
            'iso_name' => 'nullable|string|max:100',
            'alfa2' => 'nullable|string|max:2',
            'alfa3' => 'nullable|string|max:3',
            'numerico' => 'nullable|string|max:3',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'El nombre del país es obligatorio.',
            'name.unique' => 'El nombre del país ya existe.',
        ];
    }
}
