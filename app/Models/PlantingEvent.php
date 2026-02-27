<?php

namespace App\Models;

use App\Enums\PlantingEventStatus;
use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantingEvent extends Model
{
    /** @use HasFactory<\Database\Factories\PlantingEventFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $primaryKey = 'planting_id';

    protected $appends = ['id'];

    protected $fillable = [
        'campaign_id',
        'neighborhood_id',
        'assigned_to',
        'created_by',
        'started_at',
        'completed_at',
        'lat',
        'lon',
        'target_tree_count',
        'status',
        'notes',
    ];

    protected array  $tableColumns = [
        'planting_id',
        'campaign_label',
        'neighborhood_label',
        'assigned_to_label',
        'status',
        'started_at',
        'completed_at',
        'target_tree_count',
        'trees_count',
        'location',
        'notes',
    ];


    protected array  $formColumns = [
        'planting_id',
        'campaign_id',
        'neighborhood_id',
        'assigned_to',
        'created_by',
        'started_at',
        'completed_at',
        'lat',
        'lon',
        'target_tree_count',
        'status',
        'notes',
    ];

    protected array  $searchable = [
        'planting_id',
        'campaign_id',
        'neighborhood_id',
        'assigned_to',
        'created_by',
        'lat',
        'lon',
        'target_tree_count',
        'status',
        'notes',

        'campaign.name',
        'campaign.sponsor',
        'createdBy.first_name',
        'createdBy.last_name',
        'assignedTo.first_name',
        'assignedTo.last_name',
    ];

    protected array $sortable = [
        'planting_id',
        'campaign_id',
        'neighborhood_id',
        'assigned_to',
        'created_by',
        'started_at',
        'completed_at',
        'lat',
        'lon',
        'target_tree_count',
        'status',
        'notes',
    ];

    protected array $dateFilterable = [
        'started_at',
        'completed_at',
    ];


    public static function relationships(): array
    {
        return [
            'campaign',
            'neighborhood',
            'createdBy',
            'assignedTo',
            'eventTrees',
        ];
    }

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function getIdAttribute()
    {
        return $this->attributes['planting_id'] ?? null;
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function eventTrees()
    {
        return $this->hasMany(PlantingEventTree::class, 'planting_id', 'planting_id');
    }

    public function trees()
    {
        return $this->belongsToMany(Tree::class, 'planting_events_trees', 'planting_id', 'tree_id', 'planting_id', 'id')
            ->withPivot(['id', 'planted_by', 'planted_at', 'planting_method', 'notes'])
            ->withTimestamps();
    }

    public function photos()
    {
        return $this->belongsToMany(Photo::class, 'planting_events_photos', 'planting_id', 'photo_id')->withTimestamps();
    }

    public static function getPlantingEventStatusOptions(): array
    {
        return PlantingEventStatus::options();
    }

    public function getLocationAttribute()
    {
        if (!isset($this->attributes['lat'], $this->attributes['lon'])) {
            return null;
        }

        return $this->attributes['lat'] . ',' . $this->attributes['lon'];
    }
}
