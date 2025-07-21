<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EncuestaRequest extends FormRequest
{
    public function authorize()
    {
        return Auth::check();
    }

    public function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'empresa_id' => 'required|exists:empresas,id',
            'numero_encuestas' => 'nullable|integer|min:0',
            'tiempo_disponible' => 'nullable|date',
            'enviar_por_correo' => 'boolean',
            'estado' => 'required|in:borrador,enviada,publicada',
        ];
    }

    public function messages()
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'empresa_id.required' => 'La empresa es obligatoria.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ];
    }
}
