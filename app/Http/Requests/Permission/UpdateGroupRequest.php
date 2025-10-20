<?php

namespace App\Http\Requests\Permission;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => [
                'required',
                'string',
                Rule::unique('permissions')->ignore($this->id)
            ],
            'guard_name' => [
                'required',
                'string'
            ]
        ];
    }
}
