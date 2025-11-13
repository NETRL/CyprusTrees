<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /** @use HasFactory<\Database\Factories\TagFactory> */
    use HasFactory, BaseModelTrait, Paginatable;


    protected $fillable = ['name'];

    protected array  $tableColumns = [
        'id',
        'name',
        'trees_count',
        'created_at',
        'updated_at',

    ];

    protected array  $searchable = [
        'id',
        'name',
        'created_at',
        'updated_at',
    ];

    protected array $sortable = [
        'id',
        'name',
        'trees_count',
        'created_at',
        'updated_at',
    ];


    public static function relationships(): array
    {
        return [
            'trees',
        ];
    }

    public function trees()
    {
        return $this->belongsToMany(Tree::class, 'tree_tags', 'tag_id', 'tree_id')
            ->withTimestamps();
    }
}
