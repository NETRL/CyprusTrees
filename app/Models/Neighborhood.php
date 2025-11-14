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
}
