<?php

namespace App\Http\Requests\Permission;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
                Rule::unique('permissions')
            ],
            'guard_name' => [
                'required',
                'string'
            ],
            'is_crud'    => [
                'required',
                'boolean'
            ],
        ];
    }
}
