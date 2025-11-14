<?php

namespace App\Http\Requests\Neighborhood;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNeighborhoodRequest extends FormRequest
{
    public function authorize(): bool
    {
        $neighborhood = $this->route('neighborhood');
        return $this->user()?->can('update', $neighborhood) ?? false;
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
