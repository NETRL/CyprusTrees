<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Neighborhood extends Model
{
    /** @use HasFactory<\Database\Factories\NeighborhoodFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $appends = ['has_geojson'];

    protected $fillable = [
        'name',
        'city',
        'district',
        'geom_ref',
    ];

    protected array  $tableColumns = [
        'id',
        'name',
        'city',
        'district',
        'geom_ref',
        'trees_count',
    ];

    protected array  $formColumns = [
        'name',
        'city',
        'district',
        'geom_ref',
    ];


    protected array  $searchable = [
        'name',
        'city',
        'district',
        'geom_ref',


    ];

    protected array $sortable = [
        'name',
        'city',
        'district',
        'geom_ref',
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

    public function getHasGeojsonAttribute(): bool
    {
        $geomRef = $this->geom_ref;

        if (!$geomRef) {
            return false;
        }

        $path = base_path('geojson-data/' . $geomRef . '.json');

        return file_exists($path);
    }


    public function geojsonPath(string $ref): string
    {
        $ref ??= $this->geom_ref;
        return base_path('geojson-data/' . $ref . '.json');
    }
}
