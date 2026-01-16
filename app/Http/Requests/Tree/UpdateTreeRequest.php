<?php

namespace App\Http\Requests\Tree;

use App\Enums\TreeSex;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateTreeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $trees = $this->route('tree');
        return $this->user()?->can('update', $trees) ?? false;
    }

    public function rules(): array
    {
        return [
            'species_id'        => ['required', 'exists:species,id'],
            'neighborhood_id'   => ['nullable', 'exists:neighborhoods,id'],
            'tag_ids'   => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'lat'               => ['nullable', 'numeric', 'between:-90,90'],
            'lon'               => ['nullable', 'numeric', 'between:-180,180'],
            'address'           => ['nullable', 'string', 'max:255'],
            'planted_at'        => ['nullable', 'date', 'before_or_equal:now'],
            'last_inspected_at' => ['nullable', 'date', 'before_or_equal:now'],
            'status'            => ['nullable', 'string', 'max:20'],
            'health_status'     => ['nullable', 'string', 'max:20'],
            'sex'               => ['nullable', new Enum(TreeSex::class)],
            'height_m'          => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'dbh_cm'            => ['nullable', 'numeric', 'min:0', 'max:999.9'],
            'canopy_diameter_m' => ['nullable', 'numeric', 'min:0', 'max:999.99'],
            'owner_type'        => ['nullable', 'string', 'max:20'],
            'source'            => ['nullable', 'string', 'max:60'],
        ];
    }
}
