<?php

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\Paginatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tree extends Model
{
    /** @use HasFactory<\Database\Factories\TreeFactory> */
    use HasFactory, BaseModelTrait, Paginatable;

    protected $appends = ['species_label', 'tags_label'];

    protected $fillable = [
        'species_id',
        'neighborhood_id',
        'lat',
        'lon',
        'address',
        'planted_at',
        'status',
        'health_status',
        'height_m',
        'dbh_cm',
        'canopy_diameter_m',
        'last_inspected_at',
        'owner_type',
        'source',
    ];


    protected array $tableColumns = [
        'id',
        'species_label',
        'tags_label',
        'lat',
        'lon',
        'address',
        'planted_at',
        'status',
        'health_status',
        'height_m',
        'dbh_cm',
        'canopy_diameter_m',
        'last_inspected_at',
        'owner_type',
        'source',
    ];

    protected array $searchable = [
        'id',
        'species.common_name',
        'tags.name',
        'lat',
        'lon',
        'address',
        'planted_at',
        'status',
        'health_status',
        'height_m',
        'dbh_cm',
        'canopy_diameter_m',
        'last_inspected_at',
        'owner_type',
        'source',
    ];

    protected array $sortable = [
        'id',
        'species_label',
        'tags_label',
        'planted_at',
        'last_inspected_at',
        'height_m',
        'dbh_cm',
        'canopy_diameter_m',
    ];


    protected $casts = [
        'lat'               => 'float',
        'lon'               => 'float',
        'height_m'          => 'float',
        'dbh_cm'            => 'float',
        'canopy_diameter_m' => 'float',
        'planted_at'        => 'date',
        'last_inspected_at' => 'datetime',
    ];

    public static function relationships(): array
    {
        return [
            'species',
            'neighborhood',
            'plantingEvents',
            'maintenanceEvents',
            // 'healthAssessments',
            'citizenReports',
            'photos',
            'tags',
        ];
    }


    public function species()
    {
        return $this->belongsTo(Species::class);
    }

    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }

    public function plantingEvents()
    {
        return $this->hasMany(PlantingEvent::class);
    }

    public function maintenanceEvents()
    {
        return $this->hasMany(MaintenanceEvent::class);
    }

    public function healthAssessments()
    {
        return $this->hasMany(HealthAssesment::class);
    }

    public function citizenReports()
    {
        return $this->hasMany(CitizenReport::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tree_tags', 'tree_id', 'tag_id')->withTimestamps();
    }

    public function getSpeciesLabelAttribute()
    {
        return $this->species
            ? "{$this->species->common_name} ({$this->species->latin_name})"
            : '-';
    }

    public function getTagsLabelAttribute(): string
    {
        $tags = $this->tags;

        // If relation isn't loaded yet, lazy-load it
        if (!$tags || $tags->isEmpty()) {
            return '-';
        }

        return $tags->pluck('name')->join(', ');
    }
}
