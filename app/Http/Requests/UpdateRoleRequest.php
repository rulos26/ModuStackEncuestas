<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')->id ?? null;
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $roleId,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}
