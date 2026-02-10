<?php

namespace App\Http\Requests\Gis;

use App\Models\GisLayer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertGisLayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) return false;

        if ($this->isMethod('post')) {
            return $user->can('create', GisLayer::class);
        }

        $layer = $this->route('layer');
        return $layer && $user->can('update', $layer);
    }

    public function rules(): array
    {
        $layerId = $this->route('layer')?->id;

        return [
            'key' => [
                'required',
                'string',
                'max:120',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('gis_layers', 'key')->ignore($layerId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'display_name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:48'],
            'source' => ['nullable', 'string', 'max:255'],

            'default_import_mode' => ['required', Rule::in(['replace', 'append'])],

            'is_active' => ['required', 'boolean'],
            'is_editable' => ['required', 'boolean'],

            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.regex' => 'Key must contain only lowercase letters, numbers, and underscores.',
        ];
    }
}
