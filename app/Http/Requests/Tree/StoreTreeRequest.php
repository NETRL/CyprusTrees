<?php

namespace App\Http\Requests\Tree;

use App\Models\Tree;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Tree::class) ?? false;
    }

    public function rules(): array
    {
        return [
            // Foreign keys
            'species_id'        => ['required', 'exists:species,id'],
            'neighborhood_id'   => ['nullable', 'exists:neighborhoods,id'],
            'tag_ids'   => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],

            // Location
            'lat'               => ['nullable', 'numeric', 'between:-90,90'],
            'lon'               => ['nullable', 'numeric', 'between:-180,180'],
            'address'           => ['nullable', 'string', 'max:255'],

            // Dates
            'planted_at'        => ['nullable', 'date', 'before_or_equal:today'],
            'last_inspected_at' => ['nullable', 'date', 'before_or_equal:now'],

            // Descriptive attributes
            'status'            => ['nullable', 'string', 'max:20'],
            'health_status'     => ['nullable', 'string', 'max:20'],

            // Use realisctic measurements
            'height_m'          => ['nullable', 'numeric', 'between:0,999.99'],
            'dbh_cm'            => ['nullable', 'numeric', 'between:0,999.9'],
            'canopy_diameter_m' => ['nullable', 'numeric', 'between:0,999.99'],

            // Ownership / source
            'owner_type'        => ['nullable', 'string', 'max:20'],
            'source'            => ['nullable', 'string', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'species_id.required' => 'Please select a species for the tree.',
            'species_id.exists'   => 'Selected species does not exist.',

            'lat.numeric'         => 'Latitude must be a number.',
            'lat.between'         => 'Latitude must be between -90 and 90 degrees.',

            'lon.numeric'         => 'Longitude must be a number.',
            'lon.between'         => 'Longitude must be between -180 and 180 degrees.',

            'height_m.numeric'    => 'Height must be a number.',
            'height_m.between'    => 'Height must be between 0 and 999.99 meters.',

            'dbh_cm.numeric'      => 'DBH must be a number.',
            'dbh_cm.between'      => 'DBH must be between 0 and 999.9 cm.',

            'canopy_diameter_m.numeric' => 'Canopy diameter must be a number.',
            'canopy_diameter_m.between' => 'Canopy diameter must be between 0 and 999.99 meters.',
        ];
    }
}
