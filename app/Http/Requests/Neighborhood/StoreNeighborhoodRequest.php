<?php

namespace App\Http\Requests\Neighborhood;

use App\Models\Neighborhood;
use Illuminate\Foundation\Http\FormRequest;

class StoreNeighborhoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Neighborhood::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:120'],
            'city'      => ['nullable', 'string', 'max:120'],
            'district'  => ['nullable', 'string', 'max:120'],
            'geom_ref'  => ['nullable', 'string', 'max:255'],
        ];
    }

    public function prepareForValidation(): void
    {
        // Trim fields and normalize empty strings to null
        $this->merge([
            'city'     => $this->normalize($this->city),
            'district' => $this->normalize($this->district),
            'geom_ref' => $this->normalize($this->geom_ref),
        ]);
    }

    private function normalize($value)
    {
        return ($value === '' || $value === null) ? null : trim($value);
    }
}
