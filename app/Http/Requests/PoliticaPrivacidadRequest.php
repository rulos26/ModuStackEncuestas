<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PoliticaPrivacidadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'estado' => 'required|boolean',
            'version' => 'required|string|max:50',
            'fecha_publicacion' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'contenido.required' => 'El contenido es obligatorio.',
            'estado.required' => 'El estado es obligatorio.',
            'version.required' => 'La versión es obligatoria.',
            'fecha_publicacion.required' => 'La fecha de publicación es obligatoria.',
        ];
    }
}
