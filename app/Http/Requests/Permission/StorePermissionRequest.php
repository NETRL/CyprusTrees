<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Permission\UniquePermissionType;

class StorePermissionRequest extends FormRequest
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
                new UniquePermissionType($this->group)
            ],
        ];
    }
}
