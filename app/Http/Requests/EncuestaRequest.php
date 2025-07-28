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
            'numero_encuestas' => 'nullable|integer|min:1|max:10000',
            'tiempo_disponible' => 'nullable|date|after:now',
            'fecha_inicio' => 'nullable|date|after_or_equal:now',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'enviar_por_correo' => 'boolean',
            'plantilla_correo' => 'nullable|string|max:5000',
            'asunto_correo' => 'nullable|string|max:255',
            'envio_masivo_activado' => 'boolean',
            'estado' => 'required|in:borrador,enviada,publicada',
            'habilitada' => 'boolean',
        ];

        // Validaciones adicionales según el estado
        if ($this->input('estado') === 'publicada') {
            $rules['titulo'] .= '|unique:encuestas,titulo,' . $this->route('encuesta');
        }

        // Validar fechas si ambas están presentes
        if ($this->input('fecha_inicio') && $this->input('fecha_fin')) {
            $rules['fecha_fin'] = 'required|date|after:fecha_inicio';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'titulo.required' => 'El título de la encuesta es obligatorio.',
            'titulo.min' => 'El título debe tener al menos 3 caracteres.',
            'titulo.max' => 'El título no puede exceder 255 caracteres.',
            'empresa_id.required' => 'Debe seleccionar una empresa.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
            'numero_encuestas.integer' => 'El número de encuestas debe ser un número entero.',
            'numero_encuestas.min' => 'El número de encuestas debe ser al menos 1.',
            'numero_encuestas.max' => 'El número de encuestas no puede exceder 10,000.',
            'tiempo_disponible.date' => 'El tiempo de disponibilidad debe ser una fecha válida.',
            'tiempo_disponible.after' => 'El tiempo de disponibilidad debe ser posterior a la fecha actual.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio debe ser igual o posterior a la fecha actual.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
            'estado.required' => 'Debe seleccionar un estado.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'plantilla_correo.max' => 'La plantilla de correo no puede exceder 5000 caracteres.',
            'asunto_correo.max' => 'El asunto del correo no puede exceder 255 caracteres.',
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
