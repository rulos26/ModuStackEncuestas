<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMailRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && ($this->user()->hasRole('Superadmin') || $this->user()->hasRole('Admin'));
    }

    public function rules()
    {
        return [
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'attachments.*' => 'nullable|file|max:5120', // 5MB por archivo
        ];
    }
}
