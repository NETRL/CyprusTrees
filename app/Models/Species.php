<?php

namespace App\Models;

use App\Enums\CanopyClass;
use App\Enums\DroughtTolerance;
use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Species extends Model
{
    /** @use HasFactory<\Database\Factories\SpeciesFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $fillable = [
        'latin_name',
        'common_name',
        'family',
        'opals_score',
        'drought_tolerance',
        'canopy_class',
        'notes',
    ];

    protected array  $tableColumns = [
        'id',
        'latin_name',
        'common_name',
        'family',
        'opals_score',
        'drought_tolerance',
        'canopy_class',
        'notes',
        'trees_count',
    ];

    protected array  $formColumns = [
        'latin_name',
        'common_name',
        'family',
        'opals_score',
        'drought_tolerance',
        'canopy_class',
        'notes',
    ];


    protected array  $searchable = [
        'id',
        'latin_name',
        'common_name',
        'family',
        'opals_score',
        'drought_tolerance',
        'canopy_class',
        'notes',

    ];

    protected array $sortable = [
        'id',
        'latin_name',
        'common_name',
        'family',
        'opals_score',
        'drought_tolerance',
        'canopy_class',
        'notes',
        'trees_count',
    ];

    public static function relationships(): array
    {
        return [
            'trees',
        ];
    }

    public function trees()
    {
        return $this->hasMany(Tree::class);
    }

    public static function getDroughtToleranceOptions(): array
    {
        return DroughtTolerance::options();
    }

    public static function getCanopyClassOptions(): array
    {
        return CanopyClass::options();
    }
}
