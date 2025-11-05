<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tree extends Model
{
    /** @use HasFactory<\Database\Factories\TreeFactory> */
    use HasFactory;


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

    protected $casts = [
        'planted_at' => 'date',
        'last_inspected_at' => 'datetime',
        'height_m' => 'float',
        'dbh_cm' => 'float',
        'canopy_diameter_m' => 'float',
        'lat' => 'float',
        'lon' => 'float',
    ];

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
}
