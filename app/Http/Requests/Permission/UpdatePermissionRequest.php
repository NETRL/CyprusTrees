<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Permission\UniquePermissionTypeExceptSelf;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'string',
            ],
            'group'       => [
                'numeric',
                'exists:permissions,id'
            ],
            'type'        => [
                'required',
                'string',
                new UniquePermissionTypeExceptSelf($this->id)
            ],
        ];
    }
}
