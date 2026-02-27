<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantingEventTree extends Model
{
    use HasFactory, BaseModelTrait, Paginatable;

    protected $table = 'planting_events_trees';
    protected $appends = ['species_label'];
    protected $hidden = ['tree']; // avoid loading tree model also. only append the species_label

    protected $fillable = [
        'planting_id',
        'tree_id',
        'planted_by',
        'planted_at',
        'planting_method',
        'notes',
    ];

    protected array $tableColumns = [
        'id',
        'planting_id',
        'tree_id',
        'planted_by',
        'planted_at',
        'planting_method',
        'notes',
        'created_at',
        'updated_at',
    ];

    protected array $formColumns = [
        'planting_id',
        'tree_id',
        'planted_by',
        'planted_at',
        'planting_method',
        'notes',
    ];

    protected array $searchable = [
        'id',
        'planting_id',
        'tree_id',
        'planted_by',
        'planted_at',
        'planting_method',
        'notes',

        'plantingEvent.status',
        'tree.tree_id',
        'plantedBy.full_name',
    ];

    protected array $sortable = [
        'id',
        'planting_id',
        'tree_id',
        'planted_by',
        'planted_at',
        'created_at',
    ];

    public static function relationships(): array
    {
        return [
            'plantingEvent',
            'tree',
            'plantedBy',
        ];
    }

    protected $casts = [
        'planted_at' => 'datetime',
    ];

    public function plantingEvent()
    {
        return $this->belongsTo(PlantingEvent::class, 'planting_id', 'planting_id');
    }

    public function tree()
    {
        return $this->belongsTo(Tree::class, 'tree_id', 'id');
    }

    public function plantedBy()
    {
        return $this->belongsTo(User::class, 'planted_by', 'id');
    }

    public function getSpeciesLabelAttribute()
    {
        return $this->tree?->species_label ?? '-';
    }
}
