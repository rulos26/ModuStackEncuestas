<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpresaRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $empresaId = $this->route('empresa')?->id;
        return [
            'nombre_legal' => 'required|string|max:255',
            'nit' => 'required|string|max:255|unique:empresa,nit' . ($empresaId ? ",{$empresaId}" : ''),
            'representante_legal' => 'required|string|max:255',
            'telefono' => 'required|string|max:30',
            'email' => 'required|email|max:255|unique:empresa,email' . ($empresaId ? ",{$empresaId}" : ''),
            'direccion' => 'required|string|max:255',
            'pais_id' => 'required|exists:paises,id',
            'departamento_id' => 'required|exists:departamentos,id',
            'municipio_id' => 'required|exists:municipios,id',
            'mision' => 'nullable|string',
            'vision' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'fecha_creacion' => 'nullable|date',
        ];
    }

    public function messages()
    {
        return [
            'nombre_legal.required' => 'El nombre legal es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.unique' => 'El NIT ya está registrado.',
            'representante_legal.required' => 'El representante legal es obligatorio.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'direccion.required' => 'La dirección es obligatoria.',
            'pais_id.required' => 'El país es obligatorio.',
            'pais_id.exists' => 'El país seleccionado no existe.',
            'departamento_id.required' => 'El departamento es obligatorio.',
            'departamento_id.exists' => 'El departamento seleccionado no existe.',
            'municipio_id.required' => 'El municipio es obligatorio.',
            'municipio_id.exists' => 'El municipio seleccionado no existe.',
        ];
    }
}
