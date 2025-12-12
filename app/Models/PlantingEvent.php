<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantingEvent extends Model
{
    /** @use HasFactory<\Database\Factories\PlantingEventFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $primaryKey = 'planting_id';

    protected $fillable = [
        'tree_id',
        'campaign_id',
        'planted_by',
        'planted_at',
        'method',
        'notes',
    ];

    protected array  $tableColumns = [
        'planting_id',
        'tree_id',
        'campaign_id',
        'planted_by',
        'planted_at',
        'method',
        'notes',
    ];


    protected array  $formColumns = [
        'planting_id',
        'tree_id',
        'campaign_id',
        'planted_by',
        'planted_at',
        'method',
        'notes',
    ];

    protected array  $searchable = [
        'planting_id',
        'tree_id',
        'campaign_id',
        'planted_by',
        'planted_at',
        'method',
        'notes',

        'campaign.name',
        'campaign.sponsor',
        'tree.address',
        'tree.species.common_name',
        'tree.species.latin_name',
        'tree.tags.name',
        'planter.first_name',
        'planter.last_name',
    ];

    protected array $sortable = [
        'planting_id',
        'tree_id',
        'campaign_id',
        'planted_by',
        'planted_at',
        'method',
        'notes',
    ];


    public static function relationships(): array
    {
        return [
            'tree',
            'campaign',
            'planter',
        ];
    }

    protected $casts = [
        'planted_at' => 'datetime',
    ];


    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function planter()
    {
        return $this->belongsTo(User::class, 'planted_by', 'id');
    }
}
