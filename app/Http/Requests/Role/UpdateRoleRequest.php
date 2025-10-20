<?php

namespace App\Http\Requests\Role;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => [
                'required',
                'string',
                Rule::unique('roles')->ignore($this->id)
            ],
            'permissions'   => [
                'array',
            ],
            'permissions.*' => [
                'numeric',
                'exists:permissions,id'
            ]
        ];
    }
}
