<?php

namespace App\Http\Requests\Species;

use App\Enums\CanopyClass;
use App\Enums\DroughtTolerance;
use App\Enums\SpeciesOrigin;
use App\Models\Species;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSpeciesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Species::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'latin_name'            => ['nullable', 'string', 'max:120'],
            'common_name'           => ['nullable', 'string', 'max:120'],
            'family'                => ['nullable', 'string', 'max:120'],
            'origin'                => ['nullable', new Enum(SpeciesOrigin::class)],
            'opals_score'           => ['nullable', 'integer', 'min:0', 'max:10'],
            'drought_tolerance'     => ['nullable', new Enum(DroughtTolerance::class)],
            'canopy_class'          => ['nullable', new Enum(CanopyClass::class)],
            'notes'                 => ['nullable', 'string'],
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
