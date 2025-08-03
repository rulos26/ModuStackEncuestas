<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EmpresasClienteRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        $empresaId = $this->route('empresas_cliente')?->id;
        return [
            'nombre' => 'required|string|max:255',
            'nit' => 'required|string|max:255|unique:empresas_clientes,nit' . ($empresaId ? ",{$empresaId}" : ''),
            'telefono' => 'nullable|string|max:30',
            'correo_electronico' => 'nullable|email|max:255|unique:empresas_clientes,correo_electronico' . ($empresaId ? ",{$empresaId}" : ''),
            'direccion' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.unique' => 'El NIT ya está registrado.',
            'correo_electronico.email' => 'El correo electrónico no es válido.',
            'correo_electronico.unique' => 'El correo electrónico ya está registrado.',
        ];
    }
}
