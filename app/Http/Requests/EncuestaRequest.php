<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Helpers\FechaHelper;

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
            'fecha_inicio' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    // Si no hay valor, es válido (opcional)
                    if (!$value) {
                        return;
                    }

                    try {
                        // Convertir a Carbon y validar
                        $fechaInicio = \Carbon\Carbon::parse($value);
                        $hoy = \Carbon\Carbon::now()->startOfDay();

                        if ($fechaInicio->lt($hoy)) {
                            $fail('La fecha de inicio debe ser igual o posterior a hoy (' . $hoy->format('d/m/Y') . ').');
                        }
                    } catch (\Exception $e) {
                        $fail('La fecha de inicio no tiene un formato válido.');
                    }
                }
            ],
            'fecha_fin' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) {
                    // Si no hay valor, es válido (opcional)
                    if (!$value) {
                        return;
                    }

                    try {
                        $fechaFin = \Carbon\Carbon::parse($value);
                        $fechaInicio = $this->input('fecha_inicio') ? \Carbon\Carbon::parse($this->input('fecha_inicio')) : null;

                        // Si hay fecha de inicio, validar que la fecha de fin sea posterior
                        if ($fechaInicio && $fechaFin->lt($fechaInicio)) {
                            $fail('La fecha de fin debe ser igual o posterior a la fecha de inicio.');
                        }
                    } catch (\Exception $e) {
                        $fail('La fecha de fin no tiene un formato válido.');
                    }
                }
            ],
            'enviar_por_correo' => 'boolean',
            'plantilla_correo' => 'nullable|string|max:5000',
            'asunto_correo' => 'nullable|string|max:255',
            'envio_masivo_activado' => 'boolean',
            // 'estado' => 'required|in:borrador,enviada,publicada', // Removido: se maneja automáticamente
            'habilitada' => 'boolean',
        ];

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
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_inicio.after_or_equal' => 'La fecha de inicio debe ser igual o posterior a hoy.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            // 'estado.required' => 'Debe seleccionar un estado.', // Removido
            // 'estado.in' => 'El estado seleccionado no es válido.', // Removido
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
            'enviar_por_correo' => 'enviar por correo',
            // 'estado' => 'estado', // Removido
            'habilitada' => 'habilitada',
        ];
    }
}
