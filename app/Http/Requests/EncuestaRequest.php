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
        $rules = [
            'titulo' => 'required|string|max:255|min:3',
            'empresa_id' => 'required|exists:empresa,id',
            'numero_encuestas' => 'nullable|integer|min:0|max:10000',
            'tiempo_disponible' => 'nullable|date|after:now',
            'enviar_por_correo' => 'boolean',
            'estado' => 'required|in:borrador,enviada,publicada',
            'habilitada' => 'boolean',
        ];

        // Validaciones adicionales según el estado
        if ($this->input('estado') === 'publicada') {
            $rules['titulo'] .= '|unique:encuestas,titulo,' . $this->route('encuesta');
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.string' => 'El título debe ser texto.',
            'titulo.max' => 'El título no puede tener más de 255 caracteres.',
            'titulo.min' => 'El título debe tener al menos 3 caracteres.',
            'titulo.unique' => 'Ya existe una encuesta con este título.',
            'empresa_id.required' => 'La empresa es obligatoria.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
            'numero_encuestas.integer' => 'El número de encuestas debe ser un número entero.',
            'numero_encuestas.min' => 'El número de encuestas no puede ser negativo.',
            'numero_encuestas.max' => 'El número de encuestas no puede exceder 10,000.',
            'tiempo_disponible.date' => 'El tiempo disponible debe ser una fecha válida.',
            'tiempo_disponible.after' => 'El tiempo disponible debe ser posterior a la fecha actual.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'habilitada.boolean' => 'El campo habilitada debe ser verdadero o falso.',
        ];
    }

    public function attributes()
    {
        return [
            'titulo' => 'título',
            'empresa_id' => 'empresa',
            'numero_encuestas' => 'número de encuestas',
            'tiempo_disponible' => 'tiempo disponible',
            'enviar_por_correo' => 'enviar por correo',
            'estado' => 'estado',
            'habilitada' => 'habilitada',
        ];
    }
}
