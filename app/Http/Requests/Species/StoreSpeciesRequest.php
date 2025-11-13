<?php

namespace App\Http\Requests\Species;

use App\Models\Species;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpeciesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Species::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'latin_name'        => ['nullable', 'string', 'max:120'],
            'common_name'       => ['nullable', 'string', 'max:120'],
            'family'            => ['nullable', 'string', 'max:120'],
            'drought_tolerance' => ['nullable', 'in:Low,Moderate,High'],
            'canopy_class'      => ['nullable', 'in:S,M,L'],
            'notes'             => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'drought_tolerance.in' => 'Drought tolerance must be one of: Low, Moderate, or High.',
            'canopy_class.in'      => 'Canopy class must be one of: S, M, or L.',
        ];
    }
}
